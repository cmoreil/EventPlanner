<?php

namespace App\Controller\Api;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/events', name: 'api_events_')]
class EventController extends AbstractController
{

    #[Route('', name: 'all', methods: 'GET')]
    public function all(SerializerInterface $serializer, EventRepository $er)
    {
        $events = $er->findAll();
        return $this->json($events, 200, [], ['groups' => 'events_api']);
    }

}
