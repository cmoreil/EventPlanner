<?php

namespace App\Controller;

use App\Repository\CityRepository;
use App\Repository\LocationRepository;
use App\utils\UpdatingDatabase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'main_home')]
    public function home(CityRepository $cr, UpdatingDatabase $updatingDatabase): Response
    {
        $updatingDatabase->updatingStatusEvent();
        return $this->redirectToRoute('event_list');
    }
}
