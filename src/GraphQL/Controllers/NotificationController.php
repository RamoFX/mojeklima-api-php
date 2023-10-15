<?php



namespace App\GraphQL\Controllers {

  use App\Core\Entities\Account;
  use App\Core\Entities\Notification;
  use App\Core\EntityManagerProxy;
  use App\GraphQL\Exceptions\EntityNotFound;
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
    public static function notification(Account $currentAccount, int $id): Notification {
      $allNotifications = self::allNotifications($currentAccount);

      foreach ($allNotifications as $notification) {
        if ($notification->getId() === $id)
          return $notification;
      }

      throw new EntityNotFound("Notification");
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
      $location_name = $alert->getLocation()->getName();
      $alert_message = $alert->getMessage();



      // send email
      /*
      $email_successful = Email::send_notification(
        $account->getEmail(),
        $location_name,
        $alert_message
      );
      */



      // send push notification
      // initialize
      $auth = [
        "VAPID" => [
          "subject" => "https://mojeklima.ramofx.dev/",
          "publicKey" => $_ENV['PUSH_NOTIFICATIONS_PUBLIC_KEY'],
          "privateKey" => $_ENV['PUSH_NOTIFICATIONS_PRIVATE_KEY']
        ]
      ];

      $options = [
        "TTL" => 60 * 60 * 24 // 1 day
      ];

      $timeout = 120;

      $network_client_options = [
        RequestOptions::VERIFY => false // bypass server's certificate issues
      ];

      $web_push = new WebPush($auth, $options, $timeout, $network_client_options);

      // prepare sending
      $title = "$location_name | MojeKlima";
      $data = [
        "notificationId" => $notification->getId()
      ];

      $body = json_encode([
        "data" => $data,
        "body" => $alert_message,
        "title" => $title,
      ]);

      // send to each user agent associated with user
      $push_subscriptions = $account->getPushSubscriptions();

      foreach ($push_subscriptions as $push_subscription) {
        // prepare sending
        $subscription = Subscription::create([
          "contentEncoding" => "aesgcm",
          "endpoint" => $push_subscription->getEndpoint(),
          "authToken" => $push_subscription->getAuth(),
          "keys" => [
            "auth" => $push_subscription->getAuth(),
            "p256dh" => $push_subscription->getP256dh()
          ]
        ]);

        // send
        $web_push->sendOneNotification($subscription, $body);
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
        $account_id = $account->getId();

        if ($account_id === null)
          continue;

        // skip system accounts
        $role = $account->getRole();

        if ($role === "SYSTEM")
          continue;

        $locations = $account->getLocations();

        foreach ($locations as $location) {
          $location_id = $location->getId();
          $location_units = $location->getUnits();

          // handle location id null
          if ($location_id === null)
            continue;

          $weather = OpenWeatherApiController::weather($account, $location_id, $location_units);
          $alerts = $location->getAlerts();

          foreach ($alerts as $alert) {
            // next loop cycle if isEnabled === false
            if (!$alert->getIsEnabled())
              continue;

            $alert_id = $alert->getId();

            // handle alert id null
            if ($alert_id === null)
              continue;

            $criteria = $alert->getCriteria();

            // get weather value set in alert
            $current_value = null;

            switch ($criteria) {
              case 'TEMPERATURE':
                $current_value = $weather->getTemperature();
                break;

              case 'HUMIDITY':
                $current_value = $weather->getHumidity();
                break;

              case 'WIND_SPEED':
                $current_value = $weather->getWindSpeed();
                break;

              case 'PRESSURE':
                $current_value = $weather->getPressure();
                break;
            }

            // PHP 7.4 limitation - no enums, string used as criteria type instead - need to handle all the possible values
            if ($current_value === null)
              continue;

            // comparison
            $rangeFrom = $alert->getRangeFrom();
            $rangeTo = $alert->getRangeTo();

            $should_notify = $rangeFrom <= $current_value && $current_value <= $rangeTo;

            if ($should_notify)
              self::notify($account_id, $alert_id);
          }
        }
      }

      return "";
    }



    /**
     * @Mutation()
     * @Logged()
     * @InjectUser(for="$currentAccount")
     */
    public static function deleteNotification(Account $currentAccount, int $id): Notification {
      $notification = self::notification($currentAccount, $id);

      EntityManagerProxy::$entity_manager->remove($notification);
      EntityManagerProxy::$entity_manager->flush($notification);

      return $notification;
    }
  }
}
