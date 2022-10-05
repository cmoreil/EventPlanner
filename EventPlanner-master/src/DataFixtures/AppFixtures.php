<?php

namespace App\DataFixtures;

use App\Entity\City;
use App\Entity\Event;
use App\Entity\Location;
use App\Entity\Site;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $faker;
    private $manager;
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->faker = Factory::create('fr_FR');
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;
        $this->addUsers($manager);
        $this->addAdmins($manager);
        $this->addLocations($manager);
        //$this->addEvents($manager);
    }

    public function addUsers(ObjectManager $manager)
    {
        $site = $manager->getRepository(Site::class)->findAll();
        for ($i = 0; $i < 25; $i++) {
            $user = new User();
            $user->setLastName($this->faker->lastName)
                ->setFirstname($this->faker->randomElement(['Robert', 'Denis', 'Michel', 'Odette', 'Mireille', 'Micheline']))
                ->setEmail($user->getLastName() . $user->getFirstName() . '@gmail.com')
                ->setRoles(["ROLE_USER"]);
            $password = $this->passwordHasher->hashPassword($user, "ritournelle");
            $user->setPassword($password)
                ->setUsername($user->getFirstName())
                ->setPhoneNumber($this->faker->phoneNumber)
                ->setIsActive($this->faker->randomElement([0, 1]))
                ->setSite($this->faker->randomElement($site));
            $this->manager->persist($user);
        }
        $this->manager->flush();
    }

    public function addAdmins(ObjectManager $manager)
    {
        $site = $manager->getRepository(Site::class)->findAll();
        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setLastName($this->faker->lastName)
                ->setFirstname($this->faker->randomElement(['Laura', 'Yvan', 'Benjamin', 'Steeve', 'Caroline']))
                ->setEmail($user->getLastName() . $user->getFirstName() . '@gmail.com')
                ->setRoles(["ROLE_ADMIN"]);
            $password = $this->passwordHasher->hashPassword($user, "admin");
            $user->setPassword($password)
                ->setUsername($user->getFirstName())
                ->setPhoneNumber($this->faker->phoneNumber)
                ->setIsActive($this->faker->randomElement([0, 1]))
                ->setSite($this->faker->randomElement($site));
            $this->manager->persist($user);
        }
        $this->manager->flush();
    }

    public function addLocations(ObjectManager $manager)
    {
        $cities = $manager->getRepository(City::class)->findAll();
        foreach ($cities as $city) {
            for ($i = 0; $i < 3; $i++) {
                $location = new Location();
                $location->setName($this->faker->word)
                    ->setCity($city)
                    ->setStreet($this->faker->streetAddress)
                    ->setLatitude($this->faker->latitude)
                    ->setLongitude($this->faker->longitude);
                $this->manager->persist($location);
            }
            $this->manager->flush();
        }
    }

    /*public function addEvents(ObjectManager $manager)
    {

        $participants = $manager->getRepository(User::class)->findAll();

       foreach ($participants as $participant) {
           for ($i = 0; $i < 5; $i++) {
                $event = new Event();
                $event->setName($this->faker->word)
                    ->setDescription($this->faker->text)
                    ->setMaxCapacity($this->faker->numberBetween(0, 40))
                    ->setStartDateTime($this->faker->dateTimeBetween('-2 years'))
                    ->setRegistrationLimit($this->faker->dateTimeBetween($event->getStartDateTime()))
                    ->setEndDateTime($this->faker->dateTimeBetween($event->getStartDateTime()))
                    ->setStatus($this->faker->randomElement(['SF','Romantic','Far West','Comedy']))
                    ->addParticipant($participant);
                $this->manager->persist($event);
           }
            $this->manager->flush();

        }

    }*/
}
