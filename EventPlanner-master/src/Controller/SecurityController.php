<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, UserRepository $userRepository): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }
        $users = $userRepository->findAll();
        $error = $authenticationUtils->getLastAuthenticationError();

        foreach ($users as $user){
            if ($user->isIsActive()) {
                $lastUsername = $authenticationUtils->getLastUsername();

                return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
            }
        }
        // get the login error if there is one

        // last username entered by the user

    }


    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
