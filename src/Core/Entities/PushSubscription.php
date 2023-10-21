<?php



namespace App\Core\Entities {

  use DateTimeImmutable;
  use Doctrine\ORM\Event\PrePersistEventArgs;
  use Doctrine\ORM\Event\PreUpdateEventArgs;
  use Doctrine\ORM\Mapping as ORM;
  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Type;



  #[ORM\Entity]
  #[ORM\Table(name: "push_subscriptions", options: ["collate" => "utf8_czech_ci", "charset" => "utf8"])]
  #[ORM\HasLifecycleCallbacks]
  #[Type]
  class PushSubscription {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", options: ["unsigned" => true])]
    private ?int $id = null;
    #[ORM\Column(length: 511)]
    private string $endpoint;
    #[ORM\Column(length: 511)]
    private string $p256dh;
    #[ORM\Column(length: 511)]
    private string $auth;
    #[ORM\Column(name: "user_agent", length: 511)]
    private string $userAgent;
    #[ORM\Column(name: "created_at", type: "datetime_immutable")]
    private DateTimeImmutable $createdAt;
    #[ORM\Column(name: "updated_at", type: "datetime_immutable")]
    private DateTimeImmutable $updatedAt;
    #[ORM\ManyToOne(targetEntity: "Account", cascade: ["persist"], inversedBy: "pushSubscriptions")]
    private Account $account;

    public function __construct(string $endpoint, string $p256dh, string $auth, string $userAgent) {
      $this->endpoint = $endpoint;
      $this->p256dh = $p256dh;
      $this->auth = $auth;
      $this->userAgent = $userAgent;
    }



    /** @Field() */
    public function getId(): ?int {
      return $this->id;
    }

    #[Field]
    public function getEndpoint(): string {
      return $this->endpoint;
    }

    public function setEndpoint(string $endpoint): PushSubscription {
      $this->endpoint = $endpoint;

      return $this;
    }

    #[Field]
    public function getP256dh(): string {
      return $this->p256dh;
    }

    public function setP256dh(string $p256dh): PushSubscription {
      $this->p256dh = $p256dh;

      return $this;
    }

    #[Field]
    public function getAuth(): string {
      return $this->auth;
    }

    public function setAuth(string $auth): PushSubscription {
      $this->auth = $auth;

      return $this;
    }

    #[Field]
    public function getUserAgent(): string {
      return $this->userAgent;
    }

    public function setUserAgent(string $userAgent): PushSubscription {
      $this->userAgent = $userAgent;

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

    public function setAccount(Account $account): PushSubscription {
      $this->account = $account;

      return $this;
    }

    public function on_pre_persist(PrePersistEventArgs $args) {
    #[ORM\PrePersist]
      $this->createdAt = new DateTimeImmutable();
      $this->updatedAt = new DateTimeImmutable();
    }

    public function on_pre_update(PreUpdateEventArgs $args) {
    #[ORM\PreUpdate]
      $this->updatedAt = new DateTimeImmutable();
    }
  }
}
