<?php



namespace App\GraphQL\Controllers {

  use App\Core\Entities\Account;
  use App\Core\Entities\Alert;
  use App\Core\Entities\Notification;
  use App\Core\Entities\PushSubscription;
  use App\Core\EntityManagerProxy;
  use App\Utilities\Email;
  use GuzzleHttp\RequestOptions;
  use Minishlink\WebPush\Subscription;
  use Minishlink\WebPush\WebPush;
  use TheCodingMachine\GraphQLite\Annotations\InjectUser;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Mutation;
  use TheCodingMachine\GraphQLite\Annotations\Query;
  use TheCodingMachine\GraphQLite\Annotations\Right;



  class NotificationController {
    /**
     * @Query()
     * @Logged()
     * @InjectUser(for="$currentAccount")
     * @return Notification[]
     */
    public static function allNotifications(Account $currentAccount): array {
      $alerts = AlertController::allAlerts($currentAccount);
      $allNotifications = [];

      foreach ($alerts as $alert) {
        array_push($allNotifications, ...$alert->getNotifications());
      }

      // sort by creation time
      usort($allNotifications, function(Notification $a, Notification $b) {
        $dtA = $a->getCreatedAt();
        $dtB = $b->getCreatedAt();

        if ($dtA === $dtB)
          return 0;

        return $dtA < $dtB ? -1 : +1;
      });

      return $allNotifications;
    }



    /**
     * @Query()
     * @Logged()
     * @InjectUser(for="$currentAccount")
     */
    public static function hasUnseen(Account $currentAccount): bool {
      $allNotifications = self::allNotifications($currentAccount);

      foreach ($allNotifications as $notification) {
        if (!$notification->getSeen())
          return true;
      }

      return false;
    }



    /**
     * @Mutation()
     * @Logged()
     * @InjectUser(for="$currentAccount")
     */
    public static function seenAll(Account $currentAccount): string {
      $allNotifications = self::allNotifications($currentAccount);

      foreach ($allNotifications as $notification) {
        if (!$notification->getSeen()) {
          $notification->setSeen(true);

          EntityManagerProxy::$entity_manager->persist($notification);
          EntityManagerProxy::$entity_manager->flush($notification);
        }
      }

      return "";
    }



    /**
     * @Mutation()
     * @Logged()
     * @Right("CAN_SEND_PUSH_NOTIFICATIONS")
     */
    public static function notify(int $accountId, int $alertId): Notification {
      $account = PrivateAccountController::account($accountId);
      $alert = AlertController::alert($account, $alertId);
      $notification = new Notification();
      $alert->addNotification($notification);

      EntityManagerProxy::$entity_manager->persist($notification);
      EntityManagerProxy::$entity_manager->flush($notification);



      // prepare notification data
      $locationName = $alert->getLocation()->getName();
      $alertMessage = $alert->getMessage();



      // send email
      Email::sendNotification(
        $account->getEmail(),
        $locationName,
        $alertMessage
      );



      // send push notification
      // initialize
      $auth = [
        "VAPID" => [
          "subject" => "https://home.spsostrov.cz/~dvorro2/dmp/frontend/",
          "publicKey" => $_ENV['PUSH_NOTIFICATIONS_PUBLIC_KEY'],
          "privateKey" => $_ENV['PUSH_NOTIFICATIONS_PRIVATE_KEY']
        ]
      ];

      $options = [
        "TTL" => 60 * 60 * 24 // 1 day
      ];

      $timeout = 120;

      $networkClientOptions = [
        RequestOptions::VERIFY => false // bypass school server's certificate issues
      ];

      $webPush = new WebPush($auth, $options, $timeout, $networkClientOptions);

      // prepare sending
      $title = "$locationName | MojeKlima";
      $data = [
        "notificationId" => $notification->getId()
      ];

      $body = json_encode([
        "data" => $data,
        "body" => $alertMessage,
        "title" => $title,
      ]);

      // send to each user agent associated with user
      $pushSubscriptions = $account->getPushSubscriptions();

      foreach ($pushSubscriptions as $pushSubscription) {
        // prepare sending
        $subscription = Subscription::create([
          "contentEncoding" => "aesgcm",
          "endpoint" => $pushSubscription->getEndpoint(),
          "authToken" => $pushSubscription->getAuth(),
          "keys" => [
            "auth" => $pushSubscription->getAuth(),
            "p256dh" => $pushSubscription->getP256dh()
          ]
        ]);

        // send
        $webPush->sendOneNotification($subscription, $body);
        // $result = $webPush->sendOneNotification($subscription, $body);
        //
        // // result
        // if ($result->isSuccess()) {
        // echo "ok";
        // } else {
        // echo "not ok";
        // echo_json($result->getReason());
        // echo_json($result->getResponse());
        // }
      }



      return $notification;
    }



    /**
     * @Mutation()
     * @Logged()
     * @Right("CAN_SEND_PUSH_NOTIFICATIONS")
     */
    public static function checkForNotifications(): string {
      $accounts = PrivateAccountController::accounts();

      foreach ($accounts as $account) {
        // handle account id null
        $accountId = $account->getId();

        if ($accountId === null)
          continue;

        // skip system accounts
        $role = $account->getRole();

        if ($role === "SYSTEM")
          continue;

        $locations = $account->getLocations();

        foreach ($locations as $location) {
          $locationId = $location->getId();

          // handle location id null
          if ($locationId === null)
            continue;

          $weather = OpenWeatherApiController::weather($account, $locationId);
          $alerts = $location->getAlerts();

          foreach ($alerts as $alert) {
            // next loop cycle if isEnabled === false
            if (!$alert->getIsEnabled())
              continue;

            $alertId = $alert->getId();

            // handle alert id null
            if ($alertId === null)
              continue;

            $criteria = $alert->getCriteria();

            // get weather value set in alert
            $currentValue = null;

            switch ($criteria) {
              case 'TEMPERATURE':
                $currentValue = $weather->getTemperature();
                break;

              case 'HUMIDITY':
                $currentValue = $weather->getHumidity();
                break;

              case 'WIND_SPEED':
                $currentValue = $weather->getWindSpeed();
                break;

              case 'PRESSURE':
                $currentValue = $weather->getPressure();
                break;
            }

            // PHP 7.4 limitation - no enums, string used as criteria type instead - need to handle all the possible values
            if ($currentValue === null)
              continue;

            // comparison
            $comparator = $alert->getComparator();
            $value = $alert->getValue();

            $shouldNotify = (
              $comparator === 'LESS_THAN' && $currentValue < $value
              || $comparator === 'LESS_THAN_OR_EQUAL_TO' && $currentValue <= $value
              || $comparator === 'EQUAL_TO' && $currentValue == $value
              || $comparator === 'GREATER_THAN_OR_EQUAL_TO' && $currentValue >= $value
              || $comparator === 'GREATER_THAN' && $currentValue > $value
            );

            if ($shouldNotify)
              self::notify($accountId, $alertId);
          }
        }
      }

      return "";
    }
  }
}
