<?php



namespace App\Resources\Notification {

  use App\Resources\Account\AccountEntity;
  use App\Resources\Account\AccountService;
  use App\Resources\Account\Enums\AccountRole;
  use App\Resources\Alert\AlertService;
  use App\Resources\Alert\Enums\Criteria;
  use App\Resources\Common\Exceptions\EntityNotFound;
  use App\Resources\Common\Utilities\ConfigManager;
  use App\Resources\Weather\WeatherService;
  use Doctrine\ORM\EntityManager;
  use Doctrine\ORM\Exception\NotSupported;
  use Doctrine\ORM\Exception\ORMException;
  use Doctrine\ORM\OptimisticLockException;
  use ErrorException;
  use GuzzleHttp\RequestOptions;
  use Minishlink\WebPush\Subscription;
  use Minishlink\WebPush\WebPush;
  use RestClientException;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class NotificationService {
    public function __construct(
      protected EntityManager $entityManager,
      protected ConfigManager $config,
      protected AccountService $accountService,
      protected WeatherService $weatherService,
      protected AlertService $alertService
    ) {}



    /**
     * @param AccountEntity $currentAccount
     * @return NotificationEntity[]
     */
    public function notifications(AccountEntity $currentAccount): array {
      $alerts = $this->alertService->allAlerts($currentAccount);
      $allNotifications = [];

      foreach ($alerts as $alert) {
        array_push($allNotifications, ...$alert->getNotifications());
      }

      // sort by creation time
      usort($allNotifications, function(NotificationEntity $a, NotificationEntity $b) {
        $dtA = $a->getCreatedAt();
        $dtB = $b->getCreatedAt();

        if ($dtA === $dtB)
          return 0;

        return $dtA < $dtB ? -1 : +1;
      });

      return $allNotifications;
    }



    /**
     * @throws EntityNotFound
     */
    public function notification(AccountEntity $currentAccount, int $id): NotificationEntity {
      $allNotifications = $this->notifications($currentAccount);

      foreach ($allNotifications as $notification) {
        if ($notification->getId() === $id)
          return $notification;
      }

      throw new EntityNotFound("Notification");
    }



    public function hasUnseen(AccountEntity $currentAccount): bool {
      $allNotifications = $this->notifications($currentAccount);

      foreach ($allNotifications as $notification) {
        if (!$notification->getSeen())
          return true;
      }

      return false;
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function seenAll(AccountEntity $currentAccount): int {
      $allNotifications = $this->notifications($currentAccount);
      $seenCount = 0;

      foreach ($allNotifications as $notification) {
        if (!$notification->getSeen()) {
          $notification->setSeen(true);
          $seenCount += 1;

          $this->entityManager->persist($notification);
          $this->entityManager->flush($notification);
        }
      }

      return $seenCount;
    }



    /**
     * @throws EntityNotFound
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws ErrorException
     */
    public function notify(int $accountId, int $alertId): NotificationEntity {
      $account = $this->accountService->account($accountId);
      $alert = $this->alertService->alert($account, $alertId);
      $notification = new NotificationEntity();
      $alert->addNotification($notification);

      $this->entityManager->persist($notification);
      $this->entityManager->flush($notification);

      // prepare notification data
      $location = $alert->getLocation();
      $cityName = $location->getCityName();
      $countryName = $location->getCountryName();
      $alert_message = $alert->getMessage();

      // send push notification
      // initialize
      $subject = $this->config->get('app.origin');
      $publicKey = $this->config->get('keys.push.public');
      $privateKey = $this->config->get('keys.push.private');
      $appName = $this->config->get('keys.push.private');

      $auth = [
        "VAPID" => [
          "subject" => $subject,
          "publicKey" => $publicKey,
          "privateKey" => $privateKey
        ]
      ];

      $options = [
        "TTL" => 60 * 60 * 24 // 1 day
      ];

      $timeout = 120;

      $networkClientCptions = [
        RequestOptions::VERIFY => false // bypass server's certificate issues
      ];

      $webPush = new WebPush($auth, $options, $timeout, $networkClientCptions);

      // prepare sending
      $title = "$cityName, $countryName | $appName";
      $data = [
        "notificationId" => $notification->getId()
      ];

      $body = json_encode([
        "data" => $data,
        "body" => $alert_message,
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
     * @return int
     * @throws EntityNotFound
     * @throws ErrorException
     * @throws NotSupported
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws RestClientException
     * @throws GraphQLException
     */
    public function checkForNotifications(): int {
      $accounts = $this->accountService->accounts();
      $notificationsDispatched = 0;

      foreach ($accounts as $account) {
        // handle account id null
        $accountId = $account->getId();

        if ($accountId === null)
          continue;

        // skip system accounts
        $role = $account->getRole();

        if ($role === AccountRole::ADMIN)
          continue;

        $locations = $account->getLocations();

        foreach ($locations as $location) {
          $locationId = $location->getId();

          // handle location id null
          if ($locationId === null)
            continue;

          $weather = $this->weatherService->weather($account, $locationId, null, null, null);
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
            $currentValue = match ($criteria) {
              Criteria::TEMPERATURE => $weather->getTemperature(),
              Criteria::FEELS_LIKE => $weather->getFeelsLike(),
              Criteria::HUMIDITY => $weather->getHumidity(),
              Criteria::WIND_SPEED => $weather->getWindSpeed(),
              Criteria::WIND_GUST => $weather->getWindGust(),
              Criteria::WIND_DIRECTION => $weather->getWindDirection(),
              Criteria::PRESSURE => $weather->getPressure(),
              Criteria::CLOUDINESS => $weather->getCloudiness(),
            };

            if ($currentValue === null)
              continue;

            // comparison
            $rangeFrom = $alert->getRangeFrom();
            $rangeTo = $alert->getRangeTo();

            $shouldNotify = $rangeFrom <= $currentValue && $currentValue <= $rangeTo;

            if ($shouldNotify) {
              self::notify($accountId, $alertId);
              $notificationsDispatched += 1;
            }
          }
        }
      }

      return $notificationsDispatched;
    }



    /**
     * @throws OptimisticLockException
     * @throws EntityNotFound
     * @throws ORMException
     */
    public function deleteNotification(AccountEntity $currentAccount, int $id): NotificationEntity {
      $notification = self::notification($currentAccount, $id);

      $this->entityManager->remove($notification);
      $this->entityManager->flush($notification);

      return $notification;
    }
  }
}
