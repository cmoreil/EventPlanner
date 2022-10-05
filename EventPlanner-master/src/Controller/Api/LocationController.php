<?php

namespace App\Controller\Api;

use App\Repository\CityRepository;
use App\Repository\EventRepository;
use App\Repository\LocationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/locations', name: 'api_locations_')]
class LocationController extends AbstractController
{

    #[Route('', name: 'all', methods: 'GET')]
    public function all(SerializerInterface $serializer, LocationRepository $lr)
    {
        $locations = $lr->findAll();
        return $this->json($locations, 200, [], ['groups' => 'locations_api']);
    }

    #[Route('/updateLocations', name: 'updateLocations')]
    public function updateLocations(Request $req, CityRepository $cr, SerializerInterface $serializer): Response
    {
        $json = json_decode($req->getContent());
        $cityId = $cr->find($json->cityId);
        $city = $cr->find($cityId);
        $locations = $city->getLocations();

        //$json = $serializer->serialize(['locations' => $locations], 'json', ['groups' => 'json_locations']);
        $json = $serializer->serialize($locations, 'json', ['groups' => 'json_locations']);

        return $this->json($json);
    }

    #[Route('/infoLocations', name: 'infoLocations')]
    public function infoLocations(Request $req, LocationRepository $lr, SerializerInterface $serializer): Response
    {
        $json = json_decode($req->getContent());
        $locationId = $lr->find($json->locationId);
        $location = $lr->find($locationId);

        $json = $serializer->serialize($location, 'json', ['groups' => 'locations_api']);

        return $this->json($json);
    }

    #[Route('/infoCity', name: 'infoCity')]
    public function infoCity(Request $req, CityRepository $cr, SerializerInterface $serializer): Response
    {
        $json = json_decode($req->getContent());
        $CityId = $cr->find($json->CityId);
        $city = $cr->find($CityId);

        $json = $serializer->serialize($city, 'json', ['groups' => 'locations_api']);

        return $this->json($json);
    }


}