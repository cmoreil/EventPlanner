<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\utils\imageUpload;
use App\utils\PasswordComparer;
use App\utils\UpdatingDatabase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user', name: 'user_')]
class UserController extends AbstractController
{
    #[Route('/update/', name: 'update')]
    public function updateUser(Request                     $request, UserRepository $userRepository,
                               UserPasswordHasherInterface $userPasswordHasher,
                               PasswordComparer            $comparer, imageUpload $imageUpload): Response
    {
        /**
         * @var  User $user
         */
        $user = $this->getUser();
        $userForm = $this->createForm(UserType::class, $user);
        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            if ($userForm->get('password')->getData() != null) {
                if ($comparer->comparer($userForm->get('password')->getData(), $userForm->get('confirmPassword')->getData())) {
                    // matching passwords
                    $user->setPassword(
                        $userPasswordHasher->hashPassword(
                            $user,
                            $userForm->get('password')->getData()
                        )
                    );
                } else {
                    // different password
                    $this->addFlash("error", "Incorrect password ");
                    return $this->redirectToRoute('user_update');
                }
            }
            if ($userForm->get('picture')->getData() != null) {
                $file = $userForm->get('picture')->getData();

                $newName = $imageUpload->save($file, $user->getUsername(), $this->getParameter('upload_user_profile_picture_dir'));
                $user->setPicture($newName);
            }
            $userRepository->add($user, true);
            $this->addFlash("success", "Your profile was succesfully updated");
            return $this->redirectToRoute('user_show', [
                'id'=>$user->getId()]);
        }

        return $this->render('user/update.html.twig', [
            'userForm'=>$userForm->createView()
        ]);
    }


    #[Route('/show/{id}', name: 'show')]
    public function showUser(int $id, UserRepository $userRepository,UpdatingDatabase $updatingDatabase): Response
    {
        $updatingDatabase ->updatingStatusEvent();

        $user = $userRepository->find($id);
        //dd($user);
        if(!$user){
            throw $this->createNotFoundException("Oops ! This user doesn't seem to exist...");
        }
        return $this->render('user/showUser.html.twig', ['user' => $user]);
    }
}
