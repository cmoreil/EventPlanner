<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\SearchData;
use App\Entity\Status;
use App\Entity\Location;
use App\Entity\User;
use App\Form\EventType;
use App\Form\SearchDataType;
use App\Form\UpdateEventType;
use App\Repository\CityRepository;
use App\Repository\EventRepository;
use App\Repository\LocationRepository;
use App\Repository\SiteRepository;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use App\utils\UpdatingDatabase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;


#[Route('/event', name: 'event_')]
class EventController extends AbstractController
{
    #[Route('/create', name: 'create')]
    public function createEvent(EntityManagerInterface $em, LocationRepository $lr, EventRepository $eventRepository, StatusRepository $sr, Request $req): Response
    {
        $event = new Event();

        /**
         * @var User $user
         */

        $user = $this->getUser();
        $eventForm = $this->createForm(EventType::class, $event);

        $eventForm->handleRequest($req);

        // isValid() remplacé par une deuxième condition sur la date :
        if ($eventForm->isSubmitted()) {

            //on recherche nos boutons pour conditionner les instructions et redirect
            $boutonRegister = $req->request->get("register");
            $boutonPublish = $req->request->get("publish");

            //ici le $eventJSON fait appel à request pour aller rechercher l'id de la location selectionné
            //afin de la remettre en forme pour setter l'attribut de event avant envoi en bdd :
            $eventJSON = $req->request->all();

            //après reformatage de la location, on recherche l'objet complet pour settage
            $itemEvent = $lr->find($eventJSON['event']['location']);
            $event->setLocation($itemEvent);

            if ($boutonPublish) {

                if ($event->getName() && $event->getStartDateTime() && $event->getEndDateTime() && $event->getRegistrationLimit()
                    && $event->getMaxCapacity() && $event->getdescription() && $event->getLocation()) {

                    if ($event->getStartDateTime() >= $event->getEndDateTime()) {
                        $this->addFlash("error", "The start date must take place before the end date !");
                        return $this->redirectToRoute('event_create');

                    } else {
                        //je sette les attributs de event avant envoi en bdd
                        $event->setOrganizer($user);
                        $event->setReferentSite($user->getSite());

                        //je récupère mon statut opened pour settage
                        $status = $sr->find(2);
                        $event->setStatus($status);

                        //envoi en bdd de event
                        $eventRepository->add($event, true);
                        //message de success
                        $this->addFlash("success", "Event successfully published !");
                        //redirect après success
                        return $this->redirectToRoute('event_list', ['event' => $event]);
                    }
                } else {
                    $this->addFlash("error", "You need to fill all the fields");
                    return $this->redirectToRoute('event_create');
                }
            } elseif ($boutonRegister) {
                //je sette les attributs de event avant envoi en bdd
                $event->setOrganizer($user);
                $event->setReferentSite($user->getSite());

                //je récupère mon statut created pour settage
                $status = $sr->find(1);
                $event->setStatus($status);

                //envoi en bdd de event
                $eventRepository->add($event, true);
                //message de success
                $this->addFlash("success", "Event successfully saved !");
                //redirect après success
                return $this->redirectToRoute('event_list', ['event' => $event]);
            }
        }

        return $this->render('event/create.html.twig', [
            'eventForm' => $eventForm->createView(),
            'event' => $event
        ]);
    }

    #[Route('/update/{eventId}', name: 'update')]
    public function updateEvent(int              $eventId, EventRepository $eventRepository,
                                StatusRepository $statusRepository, LocationRepository $locationRepository,
                                CityRepository   $cityRepository, Request $request): Response
    {
        $event = $eventRepository->find($eventId);
        if (!$event) {
            throw $this->createNotFoundException("ERROR 404 : This event doesn't exist ");
        }
        $user = $this->getUser();

        /**
         * @var User $user
         */

        // check if connected user = organizer
        if ($event->getOrganizer()->getId() != $user->getId()) {
            throw $this->createAccessDeniedException("Error 403 Forbidden - You do not have access to this page");
        }

        $eventForm = $this->createForm(UpdateEventType::class, $event);
        $eventForm->handleRequest($request);
        // submitting form
        if ($eventForm->isSubmitted()) {
            //recovering data from location field
            $eventJSON = $request->request->all();
            $eventSansArray = "";
            foreach ($eventJSON as $itemEvent) {
                //checking if data isn't null
                if ($itemEvent != null)
                    $eventSansArray = $itemEvent;
            }
            $itemEvent = $locationRepository->find($eventSansArray['location']);
            if (!$itemEvent) {
                if ($event->getLocation() != null) {
                    $event->setLocation($event->getLocation());
                }
            } else {
                $event->setLocation($itemEvent);
            }
            if (new \DateTime() >= $event->getStartDateTime()) {
                $this > $this->addFlash("error", "You cannot change an event that has already started");
                return $this->redirectToRoute('event_list');
            }
            if ($request->request->has('save')) {
                $this->addFlash("success", "Event succesfully updated");
            } else if ($request->request->has('publish')) {

                if (!$event->getName() || !$event->getStartDateTime() || !$event->getEndDateTime() || !$event->getRegistrationLimit()
                    || !$event->getMaxCapacity() || !$event->getdescription() || !$event->getLocation()) {
                    $this->addFlash("error", "All fields are required in order to publish an event");
                    return $this->redirectToRoute('event_update', [
                        'eventId' => $event->getId()
                    ]);
                } else {
                    $this->addFlash("success", "Event published");
                    if ($event->getStatus()->getId() == 1)
                        $event->setStatus($statusRepository->find(2));
                }
            }
            $eventRepository->add($event, true);
            return $this->redirectToRoute("event_list");
        }

        return $this->render('event/updateEvent.html.twig', [
            "eventForm" => $eventForm->createView(),
            'event' => $event
        ]);
    }

    #[Route('/cancel/{eventId}', name: 'cancel')]
    public function cancelEvent(int $eventId, Request $request, EventRepository $eventRepository, StatusRepository $statusRepository): Response
    {
        $event = new Event();
        $event = $eventRepository->find($eventId);
        /**
         * @var User $user
         */
        $user = $this->getUser();
        if ($event->getOrganizer()->getId() != $user->getId()) {
            throw $this->createAccessDeniedException("Error 403 Forbidden - You do not have access to this page");
        }

        if (new \DateTime() >= $event->getStartDateTime()) {

            $this->addFlash("error", "Error : you can't cancel an event that has already begun");
            return $this->redirectToRoute("main_home", [
                "id" => $eventId
            ]);
        }
        if ($request->get('motif') != null) {
            $event->setReasonCancellation($request->get('motif'));
            $event->setStatus($statusRepository->find(6));
            $eventRepository->add($event, true);
            $this->addFlash("success", "Your event was succesfully cancelled");
            return $this->redirectToRoute('event_list');
        }

        return $this->render('event/cancelEvent.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/show/{id}', name: 'show',requirements: ['id'=>'\d+'])]
    public function showEvent(int $id,EventRepository $eventRepository,UserRepository $userRepository,UpdatingDatabase $updatingDatabase): Response
    {
        $updatingDatabase ->updatingStatusEvent();

        $event= $eventRepository->findByIdForShow($id);
        $participants = $userRepository->findByShow($id);
        $user = $userRepository->find($id);
        $nbrePlacesUtilisees = count($participants);
        $userConnected = $this->getUser();
        $today = new \DateTime();

        if (!$event) {
            throw $this->createNotFoundException('OOPS ! There is no event with that name');
        }

        return $this->render('event/showEvent.html.twig', compact('event', 'participants', 'nbrePlacesUtilisees', 'today', 'userConnected', 'user'));
    }

    #[Route('/list', name: 'list')]
    public function listEvents(EventRepository $eventRepository, UpdatingDatabase $updatingDatabase, UserRepository $userRepository, CityRepository $cityRepository, SiteRepository $siteRepository, Request $request): Response
    {
        $updatingDatabase->updatingStatusEvent();

        $searchData = new SearchData();
        $searchDataForm = $this->createForm(SearchDataType::class, $searchData);
        $searchDataForm->handleRequest($request);

        if ($searchDataForm->isSubmitted() && $searchDataForm->isValid()) {
            $events = $eventRepository->findFromFilters($searchData);

            return $this->render('event/list.html.twig', [
                'searchDataForm' => $searchDataForm->createView(),
                'events' => $events

            ]);
        } else {
            $events = $eventRepository->findAllExceptPastSortedByDate('ASC');
//            $cities = $cityRepository ->findAll();
//            $sites = $siteRepository -> findAll();
            $today = new \DateTime();
            return $this->render('event/list.html.twig', [
                'searchDataForm' => $searchDataForm->createView(),
                'events' => $events,
                'today' => $today
            ]);
        }

    }

    #[Route('/register/{eventId}', name: 'register')]
    public function registerToEvent(int $eventId,EventRepository $eventRepository,UserRepository $userRepository,UpdatingDatabase $updatingDatabase ): Response
    {
        $updatingDatabase ->updatingStatusEvent();

        $event = $eventRepository->find($eventId);
        $participants = $userRepository->findByShow($eventId);
        $nbrePlacesUtilisees = count($participants);
        $userConnected = $this->getUser();
        $today = new \DateTime();


        foreach ($participants as $participant) {
            if ($userConnected == $participant
                || $event->getRegistrationLimit() < $today
                || $nbrePlacesUtilisees == $event->getMaxCapacity()) {

                throw $this->createNotFoundException('You could not be added to this event sorry !');

            }
        }
        $eventRepository->addParticipant($event,$userConnected);
        $this->addFlash('success', 'You got added from this event');
        return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
    }

    #[Route('/desist/{eventId}', name: 'desist')]
    public function desistFromEvent(int $eventId,EventRepository $eventRepository,UserRepository $userRepository,UpdatingDatabase $updatingDatabase): Response
    {
        $updatingDatabase ->updatingStatusEvent();

        $event = $eventRepository->find($eventId);
        $participants = $userRepository->findByShow($eventId);
        $userConnected = $this->getUser();

        foreach ($participants as $participant){;
            if ($userConnected == $participant){
                $eventRepository->removeParticipant($event,$userConnected);
                $this->addFlash('success', 'You got removed from this event');

                return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
            } else {
                throw $this->createNotFoundException('You are not even registred in this event !');
            }
        }

        return $this->render('event/showEvent.html.twig', []);
    }
}
