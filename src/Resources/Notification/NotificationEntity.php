<?php



namespace App\Resources\Notification {

  use App\Resources\Alert\AlertEntity;
  use DateTimeImmutable;
  use Doctrine\ORM\Event\PrePersistEventArgs;
  use Doctrine\ORM\Mapping as ORM;
  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Type;



  #[ORM\Entity]
  #[ORM\Table(name: "notifications", options: [ "collate" => "utf8_czech_ci", "charset" => "utf8" ])]
  #[ORM\HasLifecycleCallbacks]
  #[Type(name: "Notification")]
  class NotificationEntity {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", options: [ "unsigned" => true ])]
    private ?int $id = null;
    #[ORM\Column(type: "boolean", options: [ "default" => false ])]
    private bool $seen = false;
    #[ORM\Column(name: "created_at", type: "datetime_immutable")]
    private DateTimeImmutable $createdAt;
    #[ORM\ManyToOne(targetEntity: "\App\Resources\Alert\AlertEntity", cascade: [ "persist" ], inversedBy: "notifications")]
    private AlertEntity $alert;

    public function __construct() {}

    #[Field(outputType: "ID")]
    public function getId(): ?int {
      return $this->id;
    }

    #[Field]
    public function getSeen(): bool {
      return $this->seen;
    }

    public function setSeen(bool $seen): NotificationEntity {
      $this->seen = $seen;

      return $this;
    }

    #[Field]
    public function getCreatedAt(): DateTimeImmutable {
      return $this->createdAt;
    }

    #[Field]
    public function getAlert(): AlertEntity {
      return $this->alert;
    }

    public function setAlert(AlertEntity $alert): NotificationEntity {
      $this->alert = $alert;

      return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist(PrePersistEventArgs $args): void {
      $this->createdAt = new DateTimeImmutable();
    }
  }
}
