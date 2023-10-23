<?php



namespace App\Resources\Alert {

  use App\Resources\Alert\Enums\Criteria;
  use App\Resources\Common\Utilities\UnitsConverter;
  use App\Resources\Common\Utilities\Validator;
  use App\Resources\Location\LocationEntity;
  use App\Resources\Notification\NotificationEntity;
  use App\Resources\Weather\Enums\PressureUnits;
  use App\Resources\Weather\Enums\SpeedUnits;
  use App\Resources\Weather\Enums\TemperatureUnits;
  use DateTimeImmutable;
  use Doctrine\Common\Collections\ArrayCollection;
  use Doctrine\Common\Collections\Collection;
  use Doctrine\ORM\Event\PrePersistEventArgs;
  use Doctrine\ORM\Event\PreUpdateEventArgs;
  use Doctrine\ORM\Mapping as ORM;
  use Exception;
  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Type;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  #[ORM\Entity]
  #[ORM\Table(name: "alerts", options: ["collate" => "utf8_czech_ci", "charset" => "utf8"])]
  #[ORM\HasLifecycleCallbacks]
  #[Type(name: "Alert")]
  class AlertEntity {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", options: ["unsigned" => true])]
    private ?int $id = null;
    #[ORM\Column(name: "is_enabled", type: "boolean")]
    private bool $isEnabled;
    #[ORM\Column(type: Criteria::class)]
    private Criteria $criteria;
    #[ORM\Column(name: "range_from", type: "decimal", precision: 8, scale: 2)]
    private float $rangeFrom;
    #[ORM\Column(name: "range_to", type: "decimal", precision: 8, scale: 2)]
    private float $rangeTo;
    #[ORM\Column(name: "update_frequency", type: "integer", options: ["unsigned" => true])]
    private int $updateFrequency;
    #[ORM\Column(length: 511)]
    private string $message;
    #[ORM\Column(name: "created_at", type: "datetime_immutable")]
    private DateTimeImmutable $createdAt;
    #[ORM\Column(name: "updated_at", type: "datetime_immutable")]
    private DateTimeImmutable $updatedAt;
    #[ORM\ManyToOne(targetEntity: "\App\Resources\Location\LocationEntity", cascade: ["persist"], inversedBy: "alerts")]
    private LocationEntity $location;
    #[ORM\OneToMany(mappedBy: "alert", targetEntity: "\App\Resources\Notification\NotificationEntity", cascade: ["persist"], orphanRemoval: true)]
    private Collection $notifications;

    /**
     * @throws GraphQLException
     */
    public function __construct(bool $isEnabled, Criteria $criteria, float $rangeFrom, float $rangeTo, int $updateFrequency, string $message) {
      $this->setIsEnabled($isEnabled);
      $this->setCriteria($criteria);
      $this->setRangeFrom($rangeFrom);
      $this->setRangeTo($rangeTo);
      $this->setUpdateFrequency($updateFrequency);
      $this->setMessage($message);
      $this->notifications = new ArrayCollection();
    }

    /**
     * @throws Exception
     */
    public function convertRangeFrom(TemperatureUnits|SpeedUnits|PressureUnits $units): void {
      // TODO: Handle input conversion
      // TODO: make conversion service?

      //      if ($units instanceof TemperatureUnits) {
      //      } else if ($units instanceof SpeedUnits) {
      //      } else if ($units instanceof PressureUnits) {
      //      }

      $this->setRangeFrom(
        UnitsConverter::toMetric($this->getRangeFrom(), $units)
      );
    }

    /**
     * @throws Exception
     */
    public function convertRangeTo(TemperatureUnits|SpeedUnits|PressureUnits $units): void {
      $this->setRangeTo(
        UnitsConverter::toMetric($this->getRangeTo(), $units)
      );
    }

    /**
     * @throws Exception
     */
    public function convertRange(TemperatureUnits|SpeedUnits|PressureUnits $units): void {
      $this->convertRangeFrom($units);
      $this->convertRangeTo($units);
    }

    #[Field(outputType: "ID")]
    public function getId(): ?int {
      return $this->id;
    }

    #[Field]
    public function getIsEnabled(): bool {
      return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): AlertEntity {
      $this->isEnabled = $isEnabled;

      return $this;
    }

    #[Field]
    public function getCriteria(): Criteria {
      return $this->criteria;
    }

    public function setCriteria(Criteria $criteria): AlertEntity {
      $this->criteria = $criteria;

      return $this;
    }

    #[Field]
    public function getRangeFrom(): float {
      return $this->rangeFrom;
    }

    public function setRangeFrom(float $rangeFrom): AlertEntity {
      $this->rangeFrom = $rangeFrom;

      return $this;
    }

    #[Field]
    public function getRangeTo(): float {
      return $this->rangeTo;
    }

    public function setRangeTo(float $rangeTo): AlertEntity {
      $this->rangeTo = $rangeTo;

      return $this;
    }

    #[Field]
    public function getUpdateFrequency(): int {
      return $this->updateFrequency;
    }

    /**
     * @throws GraphQLException
     */
    public function setUpdateFrequency(int $updateFrequency): AlertEntity {
      $this->updateFrequency = Validator::multiple(
        Validator::greater("updateFrequency", $updateFrequency, 0),
        Validator::less("updateFrequency", $updateFrequency, 24 * 7)
      );

      return $this;
    }

    #[Field]
    public function getMessage(): string {
      return $this->message;
    }

    /**
     * @throws GraphQLException
     */
    public function setMessage(string $message): AlertEntity {
      $this->message = Validator::maxLength("message", $message, 511);

      return $this;
    }

    #[Field]
    public function getCreatedAt(): DateTimeImmutable {
      return $this->createdAt;
    }

    #[Field]
    public function getUpdatedAt(): DateTimeImmutable {
      return $this->updatedAt;
    }

    #[Field]
    public function getLocation(): LocationEntity {
      return $this->location;
    }

    public function setLocation(LocationEntity $location): AlertEntity {
      $this->location = $location;

      return $this;
    }

    /**
     * @return NotificationEntity[]
     */
    #[Field]
    public function getNotifications(): array {
      return $this->notifications->toArray();
    }

    public function addNotification(NotificationEntity $notification): AlertEntity {
      $notification->setAlert($this);
      $this->notifications->add($notification);

      return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist(PrePersistEventArgs $args): void {
      $this->createdAt = new DateTimeImmutable();
      $this->updatedAt = new DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(PreUpdateEventArgs $args): void {
      $this->updatedAt = new DateTimeImmutable();
    }
  }
}
