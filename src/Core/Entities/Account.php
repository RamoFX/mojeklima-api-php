<?php



namespace App\Core\Entities {

  use App\Core\Enums\AccountRole;
  use App\Core\Validator;
  use DateTimeImmutable;
  use Doctrine\Common\Collections\ArrayCollection;
  use Doctrine\Common\Collections\Collection;
  use Doctrine\ORM\Event\PrePersistEventArgs;
  use Doctrine\ORM\Event\PreUpdateEventArgs;
  use Doctrine\ORM\Mapping as ORM;
  use Doctrine\ORM\Mapping\Entity;
  use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
  use Doctrine\ORM\Mapping\Table;
  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Type;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  #[Entity]
  #[Table(name: "accounts", options: ["collate" => "utf8_czech_ci", "charset" => "utf8"])]
  #[HasLifecycleCallbacks]
  #[Type]
  class Account {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", options: ["unsigned" => true])]
    private ?int $id = null;
    #[ORM\Column(name: "is_removed", type: "boolean")]
    private bool $isMarkedAsRemoved;
    #[ORM\Column(type: AccountRole::class)]
    private AccountRole $role;
    #[ORM\Column(length: 127)]
    private string $name;
    #[ORM\Column(name: "avatar_url", length: 511, nullable: true)]
    private ?string $avatarUrl;
    #[ORM\Column(length: 255)]
    private string $email;
    #[ORM\Column(name: "password_hash", length: 60, options: ["fixed" => true])]
    private string $passwordHash;
    #[ORM\Column(name: "created_at", type: "datetime_immutable")]
    private DateTimeImmutable $createdAt;
    #[ORM\Column(name: "updated_at", type: "datetime_immutable")]
    private DateTimeImmutable $updatedAt;
    #[ORM\OneToMany(mappedBy: "account", targetEntity: "\App\Core\Entities\Location", cascade: ["persist"], orphanRemoval: true)]
    private Collection $locations;
    #[ORM\OneToMany(mappedBy: "account", targetEntity: "\App\Core\Entities\PushSubscription", cascade: ["persist"], orphanRemoval: true)]
    private Collection $pushSubscriptions;

    /**
     * @throws GraphQLException
     */
    public function __construct(AccountRole $role, string $name, string $email, string $password) {
      $this->setRole($role);
      $this->setIsMarkedAsRemoved(false);
      $this->setName($name);
      $this->setEmail($email);
      $this->setPassword($password);
      $this->locations = new ArrayCollection();
      $this->pushSubscriptions = new ArrayCollection();
    }

    #[Field(outputType: "ID")]
    public function getId(): ?int {
      return $this->id;
    }

    #[Field]
    public function getIsMarkedAsRemoved(): bool {
      return $this->isMarkedAsRemoved;
    }

    public function setIsMarkedAsRemoved(bool $isMarkedAsRemoved): Account {
      $this->isMarkedAsRemoved = $isMarkedAsRemoved;

      return $this;
    }

    #[Field]
    public function getRole(): AccountRole {
      return $this->role;
    }

    public function setRole(AccountRole $role): Account {
      $this->role = $role;

      return $this;
    }

    #[Field]
    public function getName(): string {
      return $this->name;
    }

    /**
     * @throws GraphQLException
     */
    public function setName(string $name): Account {
      $this->name = Validator::maxLength("name", $name, 127);

      return $this;
    }

    #[Field]
    public function getAvatarUrl(): ?string {
      return $this->avatarUrl;
    }

    /**
     * @throws GraphQLException
     */
    public function setAvatarUrl(?string $avatarUrl): Account {
      $this->avatarUrl = $avatarUrl === null
        ? $avatarUrl
        : Validator::maxLength("avatar_url", $avatarUrl, 511);

      return $this;
    }

    #[Field]
    public function getEmail(): string {
      return $this->email;
    }

    /**
     * @throws GraphQLException
     */
    public function setEmail(string $email): Account {
      $this->email = Validator::multiple(
        Validator::maxLength("email", $email, 255),
        Validator::format("email", $email, FILTER_VALIDATE_EMAIL)
      );

      return $this;
    }

    public function getPasswordHash(): string {
      return $this->passwordHash;
    }

    public function setPassword(string $password): Account {
      $this->passwordHash = password_hash($password, PASSWORD_BCRYPT);

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

    /**
     * @return Location[]
     */
    #[Field]
    public function getLocations(): array {
      return $this->locations->toArray();
    }

    public function addLocation(Location $location): Account {
      $location->setAccount($this);
      $this->locations->add($location);

      return $this;
    }

    /**
     * @return PushSubscription[]
     */
    #[Field]
    public function getPushSubscriptions(): array {
      return $this->pushSubscriptions->toArray();
    }

    public function addPushSubscription(PushSubscription $pushSubscription): Account {
      $pushSubscription->setAccount($this);
      $this->pushSubscriptions->add($pushSubscription);

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
