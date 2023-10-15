<?php



namespace App\Core\Entities {

  use DateTimeImmutable;
  use Doctrine\ORM\Event\PrePersistEventArgs;
  use Doctrine\ORM\Mapping as ORM;
  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Type;



  /**
   * @ORM\Entity()
   * @ORM\Table(name="notifications", options={"collate"="utf8_czech_ci", "charset"="utf8"})
   * @ORM\HasLifecycleCallbacks()
   * @Type()
   */
  class Notification {
    /**
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     */
    private ?int $id = null;

    /** @ORM\Column(type="boolean", options={"default": false}) */
    private bool $seen = false;

    /** @ORM\Column(name="created_at", type="datetime_immutable") */
    private DateTimeImmutable $createdAt;

    /** @ORM\ManyToOne(targetEntity="Alert", inversedBy="pushNotifications", cascade={"persist"}) */
    private Alert $alert;



    public function __construct() {
    }



    /** @Field() */
    public function getId(): ?int {
      return $this->id;
    }



    /** @Field() */
    public function getSeen(): bool {
      return $this->seen;
    }

    public function setSeen(bool $seen): Notification {
      $this->seen = $seen;

      return $this;
    }



    /** @Field() */
    public function getCreatedAt(): DateTimeImmutable {
      return $this->createdAt;
    }



    /** @Field() */
    public function getAlert(): Alert {
      return $this->alert;
    }

    public function setAlert(Alert $alert): Notification {
      $this->alert = $alert;

      return $this;
    }



    /** @ORM\PrePersist() */
    public function on_pre_persist(PrePersistEventArgs $args) {
      $this->createdAt = new DateTimeImmutable();
    }
  }
}
