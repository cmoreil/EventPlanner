<?php

namespace App\utils;

use App\Entity\Site;
use App\Entity\User;
use App\Repository\SiteRepository;
use App\Repository\UserRepository;
use phpDocumentor\Reflection\Types\This;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class InsertUsersCsv
{
    public function importUsers(UserRepository $userRepository,UploadedFile $file,UserPasswordHasherInterface $userPasswordHasher,SiteRepository $siteRepository)
    {
        if (($handle = fopen($file->getPathname(), "r")) !== FALSE) {
            fgetcsv($handle, 10000, ",");
            while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
                $user = (new User());
                $user->setEmail($data[0])
                    ->setPassword($userPasswordHasher->hashPassword(
                        $user,
                       $data[1]
                    )
                    )
                    ->setFirstName($data[2])
                    ->setLastName($data[3])
                    ->setUsername($data[4])
                    ->setPhoneNumber($data[5])
                    ->setPicture($data[6])
                    ->setSite($siteRepository->findOneBy(['name'=>$data[7]]))
                    ->setRoles(['ROLE_USER'])
                    ->setIsActive(true);

                $userRepository->add($user,true);
            }
            fclose($handle);
        }
    }
}