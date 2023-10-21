<?php



namespace App\Core\Entities {

  use App\Core\Validator;
  use App\External\OpenWeatherApi;
  use App\Utilities\Translation;
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
  #[ORM\Table(name: "locations", options: ["collate" => "utf8_czech_ci", "charset" => "utf8"])]
  #[ORM\HasLifecycleCallbacks]
  #[Type]
  class Location {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", options: ["unsigned" => true])]
    private ?int $id = null;
    #[ORM\Column(name: "city_name", length: 127)]
    private string $cityName;
    #[ORM\Column(name: "country_name", length: 127)]
    private string $countryName;
    #[ORM\Column(length: 511, nullable: true)]
    private ?string $label;
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


    /**
     * @throws GraphQLException
     */
    public function __construct(string $cityName, string $countryName, ?string $label, float $latitude, float $longitude) {
      $this->setCityName($cityName);
      $this->setCountryName($countryName);
      $this->setLabel($label);
      $this->setLatitude($latitude);
      $this->setLongitude($longitude);
      $this->alerts = new ArrayCollection();
    }

    #[Field(outputType: "ID")]
    public function getId(): ?int {
      return $this->id;
    }

    #[Field]
    public function getCityName(): string {
      return $this->cityName;
    }

    /**
     * @throws GraphQLException
     */
    public function setCityName(string $cityName): Location {
      $this->cityName = Validator::maxLength("cityName", $cityName, 127);

      return $this;
    }

    #[Field]
    public function getCountryName(): string {
      return $this->countryName;
    }

    /**
     * @throws GraphQLException
     */
    public function setCountryName(string $countryName): Location {
      $this->countryName = Validator::maxLength("countryName", $countryName, 127);

      return $this;
    }

    #[Field]
    public function getLabel(): ?string {
      return $this->label;
    }

    /**
     * @throws GraphQLException
     */
    public function setLabel(?string $label): Location {
      $this->label = $label === null
        ? $label
        : Validator::maxLength("label", $label, 127);

      return $this;
    }

    #[Field]
    public function getLatitude(): float {
      return $this->latitude;
    }

    /**
     * @throws GraphQLException
     */
    public function setLatitude(float $latitude): Location {
      $this->latitude = Validator::multiple(
        Validator::greaterOrEqual("latitude", $latitude, -90),
        Validator::lessOrEqual("latitude", $latitude, 90)
      );

      return $this;
    }

    #[Field]
    public function getLongitude(): float {
      return $this->longitude;
    }

    /**
     * @throws GraphQLException
     */
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
