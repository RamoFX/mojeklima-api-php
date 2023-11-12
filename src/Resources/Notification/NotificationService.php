<?php



namespace App\Resources\Notification {

  use App\Resources\Account\AccountService;
  use App\Resources\Account\InputTypes\AccountInput;
  use App\Resources\Alert\AlertService;
  use App\Resources\Alert\Enums\Criteria;
  use App\Resources\Alert\InputTypes\AlertInput;
  use App\Resources\Common\CommonService;
  use App\Resources\Common\Exceptions\EntityNotFound;
  use App\Resources\Common\Utilities\ConfigManager;
  use App\Resources\Notification\InputTypes\DeleteNotificationInput;
  use App\Resources\Notification\InputTypes\NotificationInput;
  use App\Resources\Notification\InputTypes\NotifyInput;
  use App\Resources\Weather\InputTypes\WeatherInput;
  use App\Resources\Weather\WeatherService;
  use Doctrine\ORM\EntityManager;
  use Doctrine\ORM\EntityRepository;
  use Doctrine\ORM\Exception\ORMException;
  use Doctrine\ORM\NoResultException;
  use Doctrine\ORM\OptimisticLockException;
  use ErrorException;
  use Exception;
  use GuzzleHttp\RequestOptions;
  use Minishlink\WebPush\Subscription;
  use Minishlink\WebPush\WebPush;
  use Psr\SimpleCache\InvalidArgumentException;
  use RestClientException;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class NotificationService extends CommonService {
    protected EntityRepository $repository;



    public function __construct(
      protected EntityManager $entityManager,
      protected ConfigManager $config,
      protected AccountService $accountService,
      protected WeatherService $weatherService,
      protected AlertService $alertService
    ) {
      parent::__construct();
      $this->repository = $entityManager->getRepository(NotificationEntity::class);
    }



    /**
     * @return NotificationEntity[]
     */
    public function notifications(): array {
      return $this->repository->createQueryBuilder('n')
        ->select('n')
        ->join('n.alert', 'al')
        ->join('al.location', 'l')
        ->join('l.account', 'ac')
        ->where('ac.id = :accountId')
        ->orderBy('n.createdAt', 'desc')
        ->setParameter('accountId', $this->currentAccount->getId())
        ->getQuery()
        ->getResult();
    }



    /**
     * @throws EntityNotFound
     * @throws Exception
     */
    public function notification(NotificationInput $notification): NotificationEntity {
      try {
        return $this->repository->createQueryBuilder('n')
          ->select('n')
          ->join('n.alert', 'al')
          ->join('al.location', 'l')
          ->join('l.account', 'ac')
          ->where('ac.id = :accountId')
          ->andWhere('n.id = :notificationId')
          ->orderBy('n.createdAt', 'desc')
          ->setParameter('accountId', $this->currentAccount->getId())
          ->setParameter('notificationId', $notification->id)
          ->getQuery()
          ->getSingleResult();
      } catch (Exception) {
        throw new EntityNotFound('Notification');
      }
    }



    /**
     * @throws Exception
     */
    public function hasUnseen(): bool {
      return 0 < $this->repository->createQueryBuilder('n')
          ->select('COUNT(n.id)')
          ->join('n.alert', 'al')
          ->join('al.location', 'l')
          ->join('l.account', 'ac')
          ->where('ac.id = :accountId')
          ->andWhere('n.seen = false')
          ->setParameter('accountId', $this->currentAccount->getId())
          ->getQuery()
          ->getSingleScalarResult();
    }



    /**
     * @throws Exception
     */
    public function seenAll(): int {
      return $this->repository->createQueryBuilder('n')
        ->update(NotificationEntity::class, 'n')
        ->set('n.seen', true)
        ->join('a.locations', 'l')
        ->join('l.alerts', 'al')
        ->join('al.notifications', 'n')
        ->where('n.seen = false')
        ->getQuery()
        ->execute();
    }



    /**
     * @throws EntityNotFound
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws ErrorException
     */
    public function notify(NotifyInput $notify): NotificationEntity {
      $accountInput = new AccountInput();
      $accountInput->id = $notify->accountId;
      $account = $this->accountService->account($accountInput);
      $alertInput = new AlertInput();
      $alertInput->id = $notify->alertId;
      $alert = $this->alertService->alert($alertInput);
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
      $networkClientOptions = [
        RequestOptions::VERIFY => false // bypass server's certificate issues
      ];
      $webPush = new WebPush($auth, $options, $timeout, $networkClientOptions);

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
        // TODO: Logger - Unable to send push notification for the following reason: <reason>
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
     * @throws GraphQLException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws RestClientException
     * @throws InvalidArgumentException
     */
    public function checkForNotifications(): int {
      $accounts = $this->accountService->userAccounts();
      $notificationsDispatched = 0;

      foreach ($accounts as $account) {
        // handle account id null
        $accountId = $account->getId();

        if ($accountId === null)
          continue;

        $locations = $account->getLocations();

        foreach ($locations as $location) {
          $locationId = $location->getId();

          // handle location id null
          if ($locationId === null)
            continue;

          $weatherInput = new WeatherInput();
          $weatherInput->locationId = $locationId;
          $weather = $this->weatherService->weather($weatherInput);
          $alerts = $location->getAlerts();

          foreach ($alerts as $alert) {
            // only enabled alerts are getting notifications
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
              $notifyInput = new NotifyInput();
              $notifyInput->accountId = $accountId;
              $notifyInput->alertId = $alertId;

              $this->notify($notifyInput);
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
    public function deleteNotification(DeleteNotificationInput $deleteNotification): NotificationEntity {
      try {
        /** @var $notification NotificationEntity */
        $notification = $this->repository->createQueryBuilder('n')
          ->select('n')
          ->join('n.alerts', 'al')
          ->join('al.location', 'l')
          ->join('l.account', 'ac')
          ->where('n.id = :notificationId')
          ->andWhere('ac.id = :accountId')
          ->setParameter('notificationId', $deleteNotification->id)
          ->setParameter('accountId', $this->currentAccount->getId())
          ->getQuery()
          ->getSingleResult();
      } catch (NoResultException) {
        throw new EntityNotFound('Notification');
      }

      $this->entityManager->remove($notification);
      $this->entityManager->flush($notification);

      return $notification;
    }
  }
}
