<?php

namespace App\Entity;

use App\Repository\SearchDataRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SearchDataRepository::class)]
class SearchData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $eventNameContains;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $fromSearchDateTime;

    #[ORM\Column(type: 'datetime')]
    private $toSearchDateTime;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $connectedUserIsOrganizing;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $connectedUserIsRegistered;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $cancelledEvents;

    #[ORM\ManyToOne(targetEntity: Site::class)]
    private $referentSite;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $connectedUserIsNotRegistered;

    public function __construct() {
        $this->fromSearchDateTime = new \DateTime('now');
        $this->toSearchDateTime = date_add(new \DateTime('now'), new \DateInterval('P30D'));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEventNameContains(): ?string
    {
        return $this->eventNameContains;
    }

    public function setEventNameContains(?string $eventNameContains): self
    {
        $this->eventNameContains = $eventNameContains;

        return $this;
    }

    public function getFromSearchDateTime(): ?\DateTimeInterface
    {
        return $this->fromSearchDateTime;
    }

    public function setFromSearchDateTime(?\DateTimeInterface $fromSearchDateTime): self
    {
        $this->fromSearchDateTime = $fromSearchDateTime;

        return $this;
    }

    public function getToSearchDateTime(): ?\DateTimeInterface
    {
        return $this->toSearchDateTime;
    }

    public function setToSearchDateTime(\DateTimeInterface $toSearchDateTime): self
    {
        $this->toSearchDateTime = $toSearchDateTime;

        return $this;
    }

    public function getConnectedUserIsOrganizing(): ?bool
    {
        return $this->connectedUserIsOrganizing;
    }

    public function setConnectedUserIsOrganizing(?bool $connectedUserIsOrganizing): self
    {
        $this->connectedUserIsOrganizing = $connectedUserIsOrganizing;

        return $this;
    }

    public function getConnectedUserIsRegistered(): ?bool
    {
        return $this->connectedUserIsRegistered;
    }

    public function setConnectedUserIsRegistered(?bool $connectedUserIsRegistered): self
    {
        $this->connectedUserIsRegistered = $connectedUserIsRegistered;

        return $this;
    }

    public function getCancelledEvents(): ?bool
    {
        return $this->cancelledEvents;
    }

    public function setCancelledEvents(?bool $cancelledEvents): self
    {
        $this->cancelledEvents = $cancelledEvents;

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

    public function getConnectedUserIsNotRegistered(): ?bool
    {
        return $this->connectedUserIsNotRegistered;
    }

    public function setConnectedUserIsNotRegistered(?bool $connectedUserIsNotRegistered): self
    {
        $this->connectedUserIsNotRegistered = $connectedUserIsNotRegistered;

        return $this;
    }
}
