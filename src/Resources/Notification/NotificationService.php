<?php



namespace App\Resources\Notification {

  use App\Resources\Account\AccountService;
  use App\Resources\Alert\AlertService;
  use App\Resources\Alert\DTO\AlertInput;
  use App\Resources\Alert\Enums\Criteria;
  use App\Resources\Common\CommonService;
  use App\Resources\Common\Exceptions\EntityNotFound;
  use App\Resources\Common\Utilities\ConfigManager;
  use App\Resources\Notification\DTO\DeleteNotificationInput;
  use App\Resources\Notification\DTO\NotificationInput;
  use App\Resources\Notification\DTO\NotifyInput;
  use App\Resources\Weather\WeatherService;
  use DateTimeImmutable;
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
      $alertInput = new AlertInput();
      $alertInput->id = $notify->alertId;
      $alert = $this->alertService->alert($alertInput);

      $account = $alert->getLocation()->getAccount();

      $notification = new NotificationEntity();

      $alert->addNotification($notification);
      $this->entityManager->persist($notification);
      $this->entityManager->flush($notification);

      // prepare notification data
      $location = $alert->getLocation();
      $cityName = $location->getCityName();
      $countryName = $location->getCountryName();
      $alertMessage = $alert->getMessage();

      // send push notification
      // initialize
      $subject = $this->config->get('app.origin');
      $publicKey = $this->config->get('keys.push.public');
      $privateKey = $this->config->get('keys.push.private');
      $appName = $this->config->get('keys.push.private');
      $auth = [
        'VAPID' => [
          'subject' => $subject,
          'publicKey' => $publicKey,
          'privateKey' => $privateKey
        ]
      ];
      $options = [
        'TTL' => 60 * 60 * 24 // 1 day
      ];
      $timeout = 120;
      $networkClientOptions = [
        RequestOptions::VERIFY => false // bypass server's certificate issues
      ];
      $webPush = new WebPush($auth, $options, $timeout, $networkClientOptions);

      // prepare sending
      $title = "$cityName, $countryName | $appName";
      $data = [
        'notificationId' => $notification->getId()
      ];
      $body = json_encode([
        'data' => $data,
        'body' => $alertMessage,
        'title' => $title,
      ]);

      // send to each user agent associated with user
      $pushSubscriptions = $account->getPushSubscriptions();

      foreach ($pushSubscriptions as $pushSubscription) {
        // prepare sending
        $subscription = Subscription::create([
          'contentEncoding' => "aesgcm",
          'endpoint' => $pushSubscription->getEndpoint(),
          'authToken' => $pushSubscription->getAuth(),
          'keys' => [
            'auth' => $pushSubscription->getAuth(),
            'p256dh' => $pushSubscription->getP256dh()
          ]
        ]);

        // send
        $webPush->sendOneNotification($subscription, $body);

        // TODO: Logger - Unable to send push notification for the following reason: <reason>
        // $result = $webPush->sendOneNotification($subscription, $body);
        //
        // // result
        // if ($result->isSuccess()) {
        // echo 'ok';
        // } else {
        // echo 'not ok';
        // echo_json($result->getReason());
        // echo_json($result->getResponse());
        // }
      }

      return $notification;
    }



    #private function notifyFromAlert(AlertEntity $alert): NotificationEntity {
    #  // TODO: Do the same as with the weather in a service?
    #  return null;
    #}



    /**
     * @return int
     * @throws EntityNotFound
     * @throws ErrorException
     * @throws GraphQLException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws Exception
     */
    public function checkForNotifications(): int {
      $now = new DateTimeImmutable();
      $now = $now->getTimestamp();
      $locationWeather = [/*
        "$latitude$longitude" => $weather,
        ...
      */];
      $alerts = $this->alertService->userEnabledAlerts();
      $notificationsDispatched = 0;

      foreach ($alerts as $alert) {
        // assure we have location's weather
        $location = $alert->getLocation();
        $latitude = $location->getLatitude();
        $longitude = $location->getLongitude();
        $key = "$latitude$longitude";

        if (!in_array($key, $locationWeather))
          $locationWeather[$key] = $location->getWeather();

        $weather = $locationWeather[$key];

        // compare current weather and alert's range to determine whether to dispatch a notification
        $criteria = $alert->getCriteria();
        $currentValue = match ($criteria) {
          Criteria::TEMPERATURE => $weather->temperature,
          Criteria::FEELS_LIKE => $weather->feelsLike,
          Criteria::HUMIDITY => $weather->humidity,
          Criteria::PRESSURE => $weather->pressure,
          Criteria::WIND_SPEED => $weather->windSpeed,
          Criteria::WIND_GUST => $weather->windGust,
          Criteria::WIND_DIRECTION => $weather->windDirection,
          Criteria::CLOUDINESS => $weather->cloudiness
        };
        $rangeFrom = $alert->getRangeFrom();
        $rangeTo = $alert->getRangeTo();
        $shouldNotify = $rangeFrom <= $currentValue && $currentValue <= $rangeTo;
        // respect update frequency
        $shouldNotify = $shouldNotify && round($now / 60 / 60) % $alert->getUpdateFrequency() === 0;

        // notify
        if ($shouldNotify) {
          $notificationsDispatched += 1;
          $notifyInput = new NotifyInput();
          $notifyInput->alertId = $alert->getId();

          $this->notify($notifyInput);
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
        /** @var NotificationEntity $notification */
        $notification = $this->repository->createQueryBuilder('n')
          ->select('n')
          ->join('n.alert', 'al')
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
