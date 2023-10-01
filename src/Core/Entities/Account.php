<?php



namespace App\Core\Entities {

  use App\Core\Enums\AccountRoleEnum;
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
   * @ORM\Table(name="accounts", options={"collate"="utf8_czech_ci", "charset"="utf8"})
   * @ORM\HasLifecycleCallbacks()
   * @Type()
   */
  class Account {
    /**
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     */
    private ?int $id = null;

    /**
     * @var string|AccountRoleEnum
     * @ORM\Column(type="string", columnDefinition="enum('USER', 'ADMIN')")
     */
    private $role;

    /** @ORM\Column(length=127) */
    private string $name;

    /** @ORM\Column(name="avatar_url", length=511, nullable=true) */
    private ?string $avatarUrl;

    /** @ORM\Column(length=255) */
    private string $email;

    /** @ORM\Column(name="password_hash", length=60, options={"fixed": true}) */
    private string $passwordHash;

    /** @ORM\Column(name="created_at", type="datetime_immutable") */
    private DateTimeImmutable $createdAt;

    /** @ORM\Column(name="updated_at", type="datetime_immutable") */
    private DateTimeImmutable $updatedAt;

    /** @ORM\OneToMany(targetEntity="\App\Core\Entities\Location", mappedBy="account", orphanRemoval=true, cascade={"persist"}) */
    private Collection $locations;

    /** @ORM\OneToMany(targetEntity="\App\Core\Entities\PushSubscription", mappedBy="account", orphanRemoval=true, cascade={"persist"}) */
    private Collection $pushSubscriptions;



    /**
     * @throws GraphQLException
     */
    public function __construct(string $role, string $name, string $email, string $password) {
      $this->setRole($role);
      $this->setName($name);
      $this->setEmail($email);
      $this->setPassword($password);
      $this->locations = new ArrayCollection();
      $this->pushSubscriptions = new ArrayCollection();
    }



    /** @Field() */
    public function getId(): ?int {
      return $this->id;
    }



    /** @Field() */
    public function getRole(): string {
      return $this->role;
    }

    /**
     * @throws GraphQLException
     */
    public function setRole(string $role): Account {
      $this->role = Validator::oneOf("role", $role, [ 'SYSTEM', 'ADMIN', 'USER' ]);

      return $this;
    }



    /** @Field() */
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



    /** @Field() */
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



    /** @Field() */
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



    /** @Field() */
    public function getCreatedAt(): DateTimeImmutable {
      return $this->createdAt;
    }



    /** @Field() */
    public function getUpdatedAt(): DateTimeImmutable {
      return $this->updatedAt;
    }



    /**
     * @Field()
     * @return Location[]
     */
    public function getLocations(): array {
      return $this->locations->toArray();
    }

    public function addLocation(Location $location): Account {
      $location->setAccount($this);
      $this->locations->add($location);

      return $this;
    }



    /**
     * @Field()
     * @return PushSubscription[]
     */
    public function getPushSubscriptions(): array {
      return $this->pushSubscriptions->toArray();
    }

    public function addPushSubscription(PushSubscription $pushSubscription): Account {
      $pushSubscription->setAccount($this);
      $this->pushSubscriptions->add($pushSubscription);

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
