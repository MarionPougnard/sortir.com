<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Creation de campus
        $campus = new Campus();
        $campus->setNom("ENI - Niort");
        $manager->persist($campus);

        $campus2 = new Campus();
        $campus2->setNom("ENI - Nantes");
        $manager->persist($campus2);

        // Create a few users with random data
        for ($i = 0; $i < 5; $i++) {
            $user = new Utilisateur();
            $user->setEmail('user' . $i . '@user.com');
            $user->setNom('user' . $i);
            $user->setPrenom('user' . $i);
            $user->setTelephone('0123456789');
            $user->setEstActif(true);
            $user->setPseudo('user' . $i);
            $user->setRoles(['ROLE_USER']);

            $user->setPassword($this->passwordHasher->hashPassword($user, '123456'));

            $user->setCampus($campus);

            $manager->persist($user);
        }

        $manager->flush();
    }
}