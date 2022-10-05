<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserCsvType;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\utils\imageUpload;
use App\Entity\City;
use App\Entity\Site;
use App\Form\CityType;
use App\Form\SiteType;
use App\Repository\CityRepository;
use App\Repository\SiteRepository;
use App\utils\InsertUsersCsv;
use App\utils\UpdatingDatabase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/createUser', name: 'create_user')]
    public function createUser(Request $request, imageUpload $imageUpload, UserRepository $userRepository,UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = new User();
        $userForm = $this->createForm(UserType::class, $user);

        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $user->setRoles(['ROLE_USER']);
                if ($userForm->get('picture')->getData()){
                    $file = $userForm->get('picture')->getData();
                    $newName = $imageUpload->save($file, $user->getUsername(), $this->getParameter('upload_user_profile_picture_dir'));
                    $user->setPicture($newName);;
                }else{
                    $user->setPicture('default.png');
                }

                $user->setIsActive(true)
                    ->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $userForm->get('password')->getData())
                );

            $userRepository->add($user, true);

            return $this->redirectToRoute('user_show', ['id' => $user->getId()]);
        } else if ($userForm->isSubmitted()) {
            dd("ici");
        }
        return $this->render('admin/create.html.twig', ['userForm' => $userForm->createView()]);
    }

    #[Route('/createUserCsv', name: 'create_user_csv')]
    public function createUserCsv(Request $request, InsertUsersCsv $insertUsersCsv,
                                  UserRepository $userRepository,
                                  UserPasswordHasherInterface $userPasswordHasher,
                                            SiteRepository $siteRepository): Response
    {
        $userCsvForm = $this->createForm(UserCsvType::class);

        $userCsvForm->handleRequest($request);

        if ($userCsvForm->isSubmitted() && $userCsvForm->isValid()) {
            if ($userCsvForm->get('fichierCsv')->getData()){
                $file = $userCsvForm->get('fichierCsv')->getData();
                $insertUsersCsv->importUsers($userRepository,$file,$userPasswordHasher,$siteRepository);
            }

            return $this->redirectToRoute('admin_listUsers');
        }
        return $this->render('admin/createUserCsv.html.twig', ['userCsvForm' => $userCsvForm->createView()]);
    }

    #[Route('/Users', name: 'listUsers')]
    public function listUsers(UserRepository $userRepository,UpdatingDatabase $updatingDatabase): Response
    {
        $updatingDatabase ->updatingStatusEvent();

        $users = $userRepository->findAll();

        return $this->render('admin/listUsers.html.twig',compact('users'));
    }


    #[Route('/deactivateUser/{id}', name: 'deactivate_user')]
    public function deactivateUser(UserRepository $userRepository,UpdatingDatabase $updatingDatabase, int $id): Response
    {
        $updatingDatabase ->updatingStatusEvent();

        $user = $userRepository->find($id);
        $user->setIsActive(false);
        $userRepository->add($user,true);

        return $this->redirectToRoute('admin_listUsers',compact('user'));
    }

    #[Route('/deleteUser/{id}', name: 'delete_user')]
    public function deleteUser(UserRepository $userRepository,UpdatingDatabase $updatingDatabase, int $id): Response
    {
        $updatingDatabase ->updatingStatusEvent();

        $user = $userRepository->find($id);
        $userRepository->remove($user, true);

        return $this->redirectToRoute('admin_listUsers',compact('user'));
    }

    #[Route('/createCity', name: 'create_city')]
    public function createCity(Request $request, CityRepository $cityRepository,UpdatingDatabase $updatingDatabase): Response
    {
        $updatingDatabase ->updatingStatusEvent();

        $id=null;
        $city = new City();
        $cities = $cityRepository->findAll();
        $cityForm = $this->createForm(CityType::class, $city);
        $cityForm->handleRequest($request);

        if ($cityForm->isSubmitted() && $cityForm->isValid()){
            foreach ($cities as $existingCyties){
                if ($existingCyties->getName() == $city->getName() ||
                    $existingCyties->getZipCode() == $city->getZipCode()){
                    $this->addFlash("error", "You can't add an already existing city");
                    return $this->redirectToRoute('admin_create_city');
                }
            }
            $cityRepository->add($city, true);
            $this->addFlash("success", "City successfully added");
            return $this->redirectToRoute('admin_create_city');
        }

        return $this->render('admin/city.html.twig', [
            'id'=>$id,
            'cities'=>$cities,
            'cityForm'=>$cityForm->createView()
        ]);
    }

    #[Route('/createCity/{id}', name: 'update_city')]
    public function updateCity(Request $request, CityRepository $cityRepository,UpdatingDatabase $updatingDatabase, int $id): Response
    {
        $updatingDatabase ->updatingStatusEvent();

        $city = $cityRepository->find($id);
        $cities = $cityRepository->findAll();
        $cityForm = $this->createForm(CityType::class, $city);
        $cityForm->handleRequest($request);

        if ($cityForm->isSubmitted() && $cityForm->isValid()){
            $cityRepository->add($city, true);
            $this->addFlash("success", "City successfully updated");
            return $this->redirectToRoute('admin_create_city');
        }

        return $this->render('admin/city.html.twig', [
            'id'=>$id,
            'cities'=>$cities,
            'cityForm'=>$cityForm->createView()
        ]);
    }

    #[Route('/deleteCity/{id}', name: 'delete_city')]
    public function deleteCity(CityRepository $cityRepository,UpdatingDatabase $updatingDatabase, int $id): Response
    {
        $updatingDatabase ->updatingStatusEvent();

        $city = $cityRepository->find($id);
        if (!$city){
            throw $this->createNotFoundException('ERROR 404, City does not exist or could not be found');
        }
        $cityRepository->remove($city, true);
        $this->addFlash("success", "City was succesfully removed");
        return $this->redirectToRoute('admin_create_city');
    }

    #[Route('/createSite', name: 'create_site')]
    public function createSite(SiteRepository $siteRepository,UpdatingDatabase $updatingDatabase, Request $request): Response
    {
        $updatingDatabase ->updatingStatusEvent();

        $id=null;
        $site = new Site();
        $sites = $siteRepository->findAll();
        $siteForm = $this->createForm(SiteType::class, $site);
        $siteForm->handleRequest($request);

        if ($siteForm->isSubmitted() && $siteForm->isValid()){
            foreach ($sites as $existingSite){
                if ($site->getName() == $existingSite->getName()){
                    $this->addFlash("error", "You can't add an already existing site");
                    return $this->redirectToRoute('admin_create_site');
                }
            }
            $siteRepository->add($site, true);
            $this->addFlash("success", "The new site was succesfully added");
            return $this->redirectToRoute('admin_create_site');
        }

        return $this->render('admin/site.html.twig', [
            'id'=>$id,
            'sites'=>$sites,
            'siteForm'=>$siteForm->createView()
        ]);
    }

    #[Route('/createSite/{id}', name: 'update_site')]
    public function updateSite(int $id, SiteRepository $siteRepository,UpdatingDatabase $updatingDatabase, Request $request): Response
    {
        $updatingDatabase ->updatingStatusEvent();

        $site = new Site();
        $site=$siteRepository->find($id);
        if (!$site){
            throw  $this->createNotFoundException('ERROR 404 ! This site could not be found !');
        }
        $sites = $siteRepository->findAll();
        $siteForm = $this->createForm(SiteType::class, $site);
        $siteForm->handleRequest($request);

        if ($siteForm->isSubmitted() && $siteForm->isValid()){
            $siteRepository->add($site, true);
            $this->addFlash("success", "Site succesfully updated");
            return $this->redirectToRoute('admin_create_site');
        }
        return $this->render('admin/site.html.twig', [
            'id'=>$id,
            'sites'=>$sites,
            'siteForm'=>$siteForm->createView()]);
    }

    #[Route('/deleteSite/{id}', name: 'delete_site')]
    public function deleteSite(int $id, SiteRepository $siteRepository,UpdatingDatabase $updatingDatabase): Response
    {
        $updatingDatabase ->updatingStatusEvent();

        $site = $siteRepository->find($id);
        if (!$site){
            throw  $this->createNotFoundException('ERROR 404 ! This site could not be found !');
        }
        $siteRepository->remove($site, true);
        $this->addFlash("success", "Site and all associated users have been deleted");
        return $this->redirectToRoute('admin_create_site');
    }
}
