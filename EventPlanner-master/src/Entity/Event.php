<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups("events_api")]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups("events_api")]
    private $name;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $startDateTime;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $endDateTime;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $registrationLimit;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups("events_api")]
    private $maxCapacity;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups("events_api")]
    private $description;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'events')]
    private $participants;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'eventOrganized')]
    #[ORM\JoinColumn(nullable: false)]
    private $organizer;

    #[ORM\ManyToOne(targetEntity: Site::class, inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private $referentSite;

    #[ORM\ManyToOne(targetEntity: Status::class, inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private $status;

    #[ORM\ManyToOne(targetEntity: Location::class, inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: true)]
    private $location;

    #[ORM\Column(type: 'text', nullable: true)]
    private $reasonCancellation;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getStartDateTime(): ?\DateTimeInterface
    {
        return $this->startDateTime;
    }

    public function setStartDateTime(\DateTimeInterface $startDateTime): self
    {
        $this->startDateTime = $startDateTime;

        return $this;
    }

    public function getEndDateTime(): ?\DateTimeInterface
    {
        return $this->endDateTime;
    }

    public function setEndDateTime(\DateTimeInterface $endDateTime): self
    {
        $this->endDateTime = $endDateTime;

        return $this;
    }

    public function getRegistrationLimit(): ?\DateTimeInterface
    {
        return $this->registrationLimit;
    }

    public function setRegistrationLimit(\DateTimeInterface $registrationLimit): self
    {
        $this->registrationLimit = $registrationLimit;

        return $this;
    }

    public function getMaxCapacity(): ?int
    {
        return $this->maxCapacity;
    }

    public function setMaxCapacity(int $maxCapacity): self
    {
        $this->maxCapacity = $maxCapacity;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $user): self
    {
        if (!$this->participants->contains($user)) {
            $this->participants[] = $user;
            $user->addEvent($this);
        }

        return $this;
    }

    public function removeParticipant(User $user): self
    {
        if ($this->participants->removeElement($user)) {
            $user->removeEvent($this);
        }

        return $this;
    }

    public function getOrganizer(): ?User
    {
        return $this->organizer;
    }

    public function setOrganizer(?User $organizer): self
    {
        $this->organizer = $organizer;

        return $this;
    }

    public function getReferentSite(): ?Site
    {
        return $this->referentSite;
    }

    public function setReferentSite(?Site $referentSite): self
    {
        $this->referentSite = $referentSite;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getReasonCancellation(): ?string
    {
        return $this->reasonCancellation;
    }

    public function setReasonCancellation(?string $reasonCancellation): self
    {
        $this->reasonCancellation = $reasonCancellation;

        return $this;
    }
}
