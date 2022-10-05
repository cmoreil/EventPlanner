<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Location;
use App\Form\LocationType;
use App\Repository\EventRepository;
use App\Repository\LocationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/location', name: 'location_')]
class LocationController extends AbstractController
{
    #[Route('/create', name: 'create')]
    public function create(Request $request, LocationRepository $locationRepository): Response
    {
        $location = new Location();
        $locationForm = $this->createForm(LocationType::class, $location);

        //associe la requête au formulaire
        $locationForm->handleRequest($request);

        //teste la soumission du formulaire
        if ($locationForm->isSubmitted() && $locationForm->isValid()) {

            //enregistrement de le série en BDD
            $locationRepository->add($location, true);

            $this->addFlash("success", "Location successfully added !");
            return $this->redirectToRoute('event_create');

        }

        return $this->render('location/create.html.twig', [
            'locationForm' => $locationForm->createView()
        ]);
    }

    #[Route('/add/{eventId}', name: 'add')]
    public function add(int $eventId, Request $request, LocationRepository $locationRepository, EventRepository $eventRepository): Response
    {
        $location = new Location();
        /**
         * @var Event $event
         */

        $event = $eventRepository->find($eventId);

        $location->setCity($event->getLocation()->getCity());
        $locationForm = $this->createForm(LocationType::class, $location);

        //associe la requête au formulaire
        $locationForm->handleRequest($request);

        if ($locationForm->isSubmitted() && $locationForm->isValid()) {
            $event->setLocation($location);
            $locationRepository->add($location, true);

            $this->addFlash("success", "Location successfully added !");
            return $this->redirectToRoute('event_update', [
                'eventId'=>$event->getId()]);
        }

        return $this->render('location/create.html.twig', [
            'locationForm' => $locationForm->createView()
        ]);
    }

}
