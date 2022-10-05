<?php

namespace App\Entity;

use App\Repository\LocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups("json_locations")]
    #[Groups("locations_api")]
    public $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups("json_locations")]
    #[Groups("locations_api")]
    public $name;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups("json_locations")]
    #[Groups("locations_api")]
    public $street;

    #[ORM\Column(type: 'float')]
    #[Groups("json_locations")]
    #[Groups("locations_api")]
    public $latitude;

    #[ORM\Column(type: 'float')]
    #[Groups("json_locations")]
    #[Groups("locations_api")]
    public $longitude;

    #[ORM\OneToMany(mappedBy: 'location', targetEntity: Event::class, orphanRemoval: true)]
    public $events;

    #[ORM\ManyToOne(targetEntity: City::class, inversedBy: 'locations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups("json_locations")]
    public $city;

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
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

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->setLocation($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getLocation() === $this) {
                $event->setLocation(null);
            }
        }

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function __toString() {
        return $this->getName();
    }
}
