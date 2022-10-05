<?php

namespace App\utils;

use App\Repository\EventRepository;
use App\Repository\StatusRepository;

class UpdatingDatabase
{

    private EventRepository $eventRepository;
    private StatusRepository $statusRepository;

    public function __construct(EventRepository $eventRepository, StatusRepository $statusRepository){
        $this->eventRepository = $eventRepository;
        $this->statusRepository = $statusRepository;
    }

    /**
     * Cette fonction permet de mettre à jour le statut des events
     * lorsqu'ils sont liés à des dates et non à des actions spécifiques.
     * 1. Si le dateTime actuel est compris entre la date limite d'inscription et la date de début de l'évènement,
     * le statut est setté à "closed".
     * 2. Si le dateTime actuel est compris entre la date de début de l'event et sa date de fin,
     * le statut est setté à "ongoing"
     * 3. Si le dateTime actuel est postérieur à la date de fin de l'event,
     * le statut est setté à "past"
     *
     * ATTENTION : les statuts "opened", "created" et "cancelled" ne peuvent être settés que par une action directe de l'organisateur
     *
     * @return void
     */
    public function updatingStatusEvent():void {

        $events = $this->eventRepository->findAll();
        $currentTime = new \DateTime("now");
        $createdStatus = $this->statusRepository->find("1");
        $closedStatus = $this->statusRepository->find("3");
        $ongoingStatus = $this->statusRepository->find("4");
        $pastStatus = $this->statusRepository->find("5");
        $cancelledStatus = $this->statusRepository->find("6");
        $archivedStatus = $this->statusRepository->find("7");

        foreach ($events as $event){
            if($event->getStatus() !== $cancelledStatus && $event->getStatus() !== $createdStatus
            && $event->getRegistrationLimit() != null && $event->getStartDateTime() != null && $event->getEndDateTime() != null){
                if($event->getRegistrationLimit() < $currentTime && $currentTime < $event->getStartDateTime()) {
                    $event->setStatus($closedStatus);
                }elseif($event->getStartDateTime() < $currentTime && $currentTime < $event->getEndDateTime()){
                    $event->setStatus($ongoingStatus);
                }elseif($currentTime > $event->getEndDateTime() && $currentTime < date_add(\DateTime::createFromInterface($event->getEndDateTime()),new \DateInterval('P30D'))){
                    $event->setStatus($pastStatus);
                }elseif ($currentTime >= date_add(\DateTime::createFromInterface($event->getEndDateTime()),new \DateInterval('P30D'))){
                    $event->setStatus($archivedStatus);
                    }
            }
            $this->eventRepository->add($event, true);
        }
    }
}