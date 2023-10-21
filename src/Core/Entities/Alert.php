<?php



namespace App\Core\Entities {

  use App\Core\Enums\Criteria;
  use App\Core\Validator;
  use App\Utilities\UnitsConverter;
  use DateTimeImmutable;
  use Doctrine\Common\Collections\ArrayCollection;
  use Doctrine\Common\Collections\Collection;
  use Doctrine\ORM\Event\PrePersistEventArgs;
  use Doctrine\ORM\Event\PreUpdateEventArgs;
  use Doctrine\ORM\Mapping as ORM;
  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Type;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  #[ORM\Entity]
  #[ORM\Table(name: "alerts", options: ["collate" => "utf8_czech_ci", "charset" => "utf8"])]
  #[ORM\HasLifecycleCallbacks]
  #[Type]
  class Alert {
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
    #[ORM\ManyToOne(targetEntity: "\App\Core\Entities\Location", cascade: ["persist"], inversedBy: "alerts")]
    private Location $location;
    #[ORM\OneToMany(mappedBy: "alert", targetEntity: "Notification", cascade: ["persist"], orphanRemoval: true)]
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



    private function validateRangeUnits(Criteria $criteria, string $units): void {
      switch ($criteria) {
        case Criteria::TEMPERATURE:
        case 'FEELS_LIKE':
          Validator::oneOf("units", $units, ["CELSIUS", "FAHRENHEIT", "KELVIN", "RANKINE"]);
          break;

        case Criteria::WIND_SPEED:
        case Criteria::WIND_GUST:
          Validator::oneOf("units", $units, ["METERS_PER_SECOND", "KILOMETERS_PER_HOUR", "MILES_PER_HOUR", "KNOTS"]);
          break;

        case Criteria::PRESSURE:
          Validator::oneOf("units", $units, ["HECTOPASCAL", "MILLIBAR", "INCHES_OF_MERCURY"]);
          break;
      }
    }

    public function convertRangeFrom(?Criteria $criteria, string $units): void {
      $this->validateRangeUnits($criteria ?? $this->getCriteria(), $units);

      $this->setRangeFrom(
        UnitsConverter::toMetric(
          $this->getRangeFrom(),
          $units
        )
      );
    }

    public function convertRangeTo(?Criteria $criteria, string $units): void {
      $this->validateRangeUnits($criteria ?? $this->getCriteria(), $units);

      $this->setRangeTo(
        UnitsConverter::toMetric(
          $this->getRangeTo(),
          $units
        )
      );
    }

    public function convertRange(Criteria $criteria, string $units): void {
      $this->convertRangeFrom($criteria, $units);
      $this->convertRangeTo($criteria, $units);
    }



    /** @Field() */
    public function getId(): ?int {
      return $this->id;
    }

    #[Field]
    public function getIsEnabled(): bool {
      return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): Alert {
      $this->isEnabled = $isEnabled;

      return $this;
    }

    #[Field]
    public function getCriteria(): Criteria {
      return $this->criteria;
    }

    public function setCriteria(Criteria $criteria): Alert {
      $this->criteria = $criteria;

      return $this;
    }

    #[Field]
    public function getRangeFrom(): float {
      return $this->rangeFrom;
    }

    public function setRangeFrom(float $rangeFrom): Alert {
      $this->rangeFrom = $rangeFrom;

      return $this;
    }

    #[Field]
    public function getRangeTo(): float {
      return $this->rangeTo;
    }

    public function setRangeTo(float $rangeTo): Alert {
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
    public function setUpdateFrequency(int $updateFrequency): Alert {
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
    public function setMessage(string $message): Alert {
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
    public function getLocation(): Location {
      return $this->location;
    }

    public function setLocation(Location $location): Alert {
      $this->location = $location;

      return $this;
    }

    /**
     * @return Notification[]
     */
    #[Field]
    public function getNotifications(): array {
      return $this->notifications->toArray();
    }

    public function addNotification(Notification $notification): Alert {
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
