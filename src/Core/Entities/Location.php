<?php



namespace App\Core\Entities {

  use App\Core\Enums\WeatherUnitsEnum;
  use App\Core\Validator;
  use DateTimeImmutable;
  use Doctrine\Common\Collections\ArrayCollection;
  use Doctrine\Common\Collections\Collection;
  use Doctrine\ORM\Event\PrePersistEventArgs;
  use Doctrine\ORM\Event\PreUpdateEventArgs;
  use Doctrine\ORM\Mapping as ORM;
  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Type;



  #[ORM\Entity]
  #[ORM\Table(name: "locations", options: ["collate" => "utf8_czech_ci", "charset" => "utf8"])]
  #[ORM\HasLifecycleCallbacks]
  #[Type]
  class Location {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", options: ["unsigned" => true])]
    private ?int $id = null;

    /** @ORM\Column(length=127) */
    private string $name;

    /** @ORM\Column(length=511) */
    private string $description;

    #[ORM\Column(type: "decimal", precision: 8, scale: 4)]
    private float $latitude;
    #[ORM\Column(type: "decimal", precision: 9, scale: 4)]
    private float $longitude;
    #[ORM\Column(name: "created_at", type: "datetime_immutable")]
    private DateTimeImmutable $createdAt;
    #[ORM\Column(name: "updated_at", type: "datetime_immutable")]
    private DateTimeImmutable $updatedAt;
    #[ORM\ManyToOne(targetEntity: "Account", cascade: ["persist"], inversedBy: "locations")]
    private Account $account;
    #[ORM\OneToMany(mappedBy: "location", targetEntity: "Alert", cascade: ["persist"], orphanRemoval: true)]
    private Collection $alerts;



    public function __construct(string $name, string $description, float $latitude, float $longitude) {
      $this->setName($name);
      $this->setDescription($description);
      $this->setLatitude($latitude);
      $this->setLongitude($longitude);
      $this->alerts = new ArrayCollection();
    }



    /** @Field() */
    public function getId(): ?int {
      return $this->id;
    }



    /** @Field() */
    public function getName(): string {
      return $this->name;
    }

    public function setName(string $name): Location {
      $this->name = Validator::maxLength("name", $name, 127);

      return $this;
    }



    /** @Field() */
    public function getDescription(): string {
      return $this->description;
    }

    public function setDescription(string $description): Location {
      $this->description = Validator::maxLength("description", $description, 511);

      return $this;
    }

    #[Field]
    public function getLatitude(): float {
      return $this->latitude;
    }

    public function setLatitude(float $latitude): Location {
      $this->latitude = Validator::multiple(
        Validator::greaterOrEqual("latitude", $latitude, -90),
        Validator::lessOrEqual("latitude", $latitude, +90)
      );

      return $this;
    }

    #[Field]
    public function getLongitude(): float {
      return $this->longitude;
    }

    public function setLongitude(float $longitude): Location {
      $this->longitude = Validator::multiple(
        Validator::greaterOrEqual("longitude", $longitude, -180),
        Validator::lessOrEqual("longitude", $longitude, 180)
      );

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
    public function getAccount(): Account {
      return $this->account;
    }

    public function setAccount(Account $account): Location {
      $this->account = $account;

      return $this;
    }

    /**
     * @return Alert[]
     */
    #[Field]
    public function getAlerts(): array {
      return $this->alerts->toArray();
    }

    public function addAlert(Alert $alert): Location {
      $alert->setLocation($this);
      $this->alerts->add($alert);

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
