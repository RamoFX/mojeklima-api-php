<?php



namespace App\Resources\PushSubscription {

  use App\Resources\Account\AccountEntity;
  use DateTimeImmutable;
  use Doctrine\ORM\Event\PrePersistEventArgs;
  use Doctrine\ORM\Event\PreUpdateEventArgs;
  use Doctrine\ORM\Mapping as ORM;
  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Type;



  #[ORM\Entity]
  #[ORM\Table(name: "push_subscriptions", options: ["collate" => "utf8_czech_ci", "charset" => "utf8"])]
  #[ORM\HasLifecycleCallbacks]
  #[Type(name: "PushSubscription")]
  class PushSubscriptionEntity {
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
    #[ORM\ManyToOne(targetEntity: "\App\Resources\Account\AccountEntity", cascade: ["persist"], inversedBy: "pushSubscriptions")]
    private AccountEntity $account;

    public function __construct(string $endpoint, string $p256dh, string $auth, string $userAgent) {
      $this->endpoint = $endpoint;
      $this->p256dh = $p256dh;
      $this->auth = $auth;
      $this->userAgent = $userAgent;
    }

    #[Field(outputType: "ID")]
    public function getId(): ?int {
      return $this->id;
    }

    #[Field]
    public function getEndpoint(): string {
      return $this->endpoint;
    }

    public function setEndpoint(string $endpoint): PushSubscriptionEntity {
      $this->endpoint = $endpoint;

      return $this;
    }

    #[Field]
    public function getP256dh(): string {
      return $this->p256dh;
    }

    public function setP256dh(string $p256dh): PushSubscriptionEntity {
      $this->p256dh = $p256dh;

      return $this;
    }

    #[Field]
    public function getAuth(): string {
      return $this->auth;
    }

    public function setAuth(string $auth): PushSubscriptionEntity {
      $this->auth = $auth;

      return $this;
    }

    #[Field]
    public function getUserAgent(): string {
      return $this->userAgent;
    }

    public function setUserAgent(string $userAgent): PushSubscriptionEntity {
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
    public function getAccount(): AccountEntity {
      return $this->account;
    }

    public function setAccount(AccountEntity $account): PushSubscriptionEntity {
      $this->account = $account;

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
