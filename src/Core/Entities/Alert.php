<?php



namespace App\Core\Entities {

  use App\Core\Enums\ComparatorEnum;
  use App\Core\Enums\CriteriaEnum;
  use App\Core\Validator;
  use DateTimeImmutable;
  use Doctrine\Common\Collections\ArrayCollection;
  use Doctrine\Common\Collections\Collection;
  use Doctrine\ORM\Event\PrePersistEventArgs;
  use Doctrine\ORM\Event\PreUpdateEventArgs;
  use Doctrine\ORM\Mapping as ORM;
  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Type;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  /**
   * @ORM\Entity()
   * @ORM\Table(name="alerts", options={"collate"="utf8_czech_ci", "charset"="utf8"})
   * @ORM\HasLifecycleCallbacks()
   * @Type()
   */
  class Alert {
    /**
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     */
    private ?int $id = null;

    /** @ORM\Column(name="is_enabled", type="boolean") */
    private bool $isEnabled;

    /**
     * @var string|CriteriaEnum
     * @ORM\Column(type="string", columnDefinition="enum('TEMPERATURE', 'HUMIDITY', 'WIND_SPEED', 'WIND_GUST', 'WIND_DIRECTION', 'PRESSURE', 'CLOUDINESS')")
     */
    private $criteria;

    /** @ORM\Column(name="range_from", type="decimal", precision=6, scale=2) */
    private float $rangeFrom;

    /** @ORM\Column(name="range_to", type="decimal", precision=6, scale=2) */
    private float $rangeTo;

    /** @ORM\Column(name="update_frequency", type="integer", options={"unsigned": true}) */
    private int $updateFrequency;

    /** @ORM\Column(length=511) */
    private string $message;

    /** @ORM\Column(name="created_at", type="datetime_immutable") */
    private DateTimeImmutable $createdAt;

    /** @ORM\Column(name="updated_at", type="datetime_immutable") */
    private DateTimeImmutable $updatedAt;

    /** @ORM\ManyToOne(targetEntity="\App\Core\Entities\Location", inversedBy="alerts", cascade={"persist"}) */
    private Location $location;

    /** @ORM\OneToMany(targetEntity="Notification", mappedBy="alert", orphanRemoval=true, cascade={"persist"}) */
    private Collection $notifications;



    /**
     * @throws GraphQLException
     */
    public function __construct(bool $isEnabled, string $criteria, float $rangeFrom, float $rangeTo, int $updateFrequency, string $message) {
      $this->setIsEnabled($isEnabled);
      $this->setCriteria($criteria);
      $this->setRangeFrom($rangeFrom);
      $this->setRangeTo($rangeTo);
      $this->setUpdateFrequency($updateFrequency);
      $this->setMessage($message);
      $this->notifications = new ArrayCollection();
    }



    /** @Field() */
    public function getId(): ?int {
      return $this->id;
    }



    /** @Field() */
    public function getIsEnabled(): bool {
      return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): Alert {
      $this->isEnabled = $isEnabled;

      return $this;
    }



    /** @Field() */
    public function getCriteria(): string {
      return $this->criteria;
    }

    /**
     * @throws GraphQLException
     */
    public function setCriteria(string $criteria): Alert {
      $this->criteria = Validator::oneOf("criteria", $criteria, [ 'TEMPERATURE', 'HUMIDITY', 'WIND_SPEED', 'PRESSURE' ]);

      return $this;
    }



    /** @Field() */
    public function getRangeFrom(): float {
      return $this->rangeFrom;
    }

    public function setRangeFrom(float $rangeFrom): Alert {
      $this->rangeFrom = $rangeFrom;

      return $this;
    }



    /** @Field() */
    public function getRangeTo(): float {
      return $this->rangeTo;
    }

    public function setRangeTo(float $rangeTo): Alert {
      $this->rangeTo = $rangeTo;

      return $this;
    }



    /** @Field() */
    public function getUpdateFrequency(): int {
      return $this->updateFrequency;
    }

    /**
     * @throws GraphQLException
     */
    public function setUpdateFrequency(int $updateFrequency): Alert {
      $this->updateFrequency = Validator::multiple(
        Validator::greater("updateFrequency", $updateFrequency, 0),
        Validator::less("updateFrequency", $updateFrequency, 24 * 7)
      );

      return $this;
    }



    /** @Field() */
    public function getMessage(): string {
      return $this->message;
    }

    /**
     * @throws GraphQLException
     */
    public function setMessage(string $message): Alert {
      $this->message = Validator::maxLength("message", $message, 511);

      return $this;
    }



    /** @Field() */
    public function getCreatedAt(): DateTimeImmutable {
      return $this->createdAt;
    }



    /** @Field() */
    public function getUpdatedAt(): DateTimeImmutable {
      return $this->updatedAt;
    }



    /** @Field() */
    public function getLocation(): Location {
      return $this->location;
    }

    public function setLocation(Location $location): Alert {
      $this->location = $location;

      return $this;
    }


    /**
     * @Field()
     * @return Notification[]
     */
    public function getNotifications(): array {
      return $this->notifications->toArray();
    }

    public function addNotification(Notification $notification): Alert {
      $notification->setAlert($this);
      $this->notifications->add($notification);

      return $this;
    }



    /** @ORM\PrePersist() */
    public function on_pre_persist(PrePersistEventArgs $args) {
      $this->createdAt = new DateTimeImmutable();
      $this->updatedAt = new DateTimeImmutable();
    }

    /** @ORM\PreUpdate() */
    public function on_pre_update(PreUpdateEventArgs $args) {
      $this->updatedAt = new DateTimeImmutable();
    }
  }
}
