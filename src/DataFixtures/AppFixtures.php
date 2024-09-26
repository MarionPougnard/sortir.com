<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Utilisateur;
use App\Entity\Ville;
use App\Enum\EtatEnum;
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

        $campus3 = new Campus();
        $campus3->setNom("ENI - Rennes");
        $manager->persist($campus3);

        $campus4 = new Campus();
        $campus4->setNom("ENI - Quimper");
        $manager->persist($campus4);

        // Creation des villes
        $ville = new Ville();
        $ville->setNom('Niort');
        $ville->setCodepostal('79000');
        $manager->persist($ville);

        $ville1 = new Ville();
        $ville1->setNom('La Rochelle');
        $ville1->setCodepostal('17000');
        $manager->persist($ville1);

        $ville2 = new Ville();
        $ville2->setNom('Paris');
        $ville2->setCodepostal('75000');
        $manager->persist($ville2);

        $ville3 = new Ville();
        $ville3->setNom('Poitiers');
        $ville3->setCodepostal('86000');
        $manager->persist($ville3);

        $ville4 = new Ville();
        $ville4->setNom('Bordeaux');
        $ville4->setCodepostal('33000');
        $manager->persist($ville4);

        // Creation des lieux
        $lieu = new Lieu();
        $lieu->setNom("Prison Island");
        $lieu->setVille($ville);
        $lieu->setRue("Zone commeciale Mendes France");
        $lieu->setLatitude(1.02668);
        $lieu->setLongitude(0.23654);
        $manager->persist($lieu);

        $lieu2 = new Lieu();
        $lieu2->setNom("Patinoire");
        $lieu2->setVille($ville);
        $lieu2->setRue("6 Rue des équarts");
        $lieu2->setLatitude(1.02668);
        $lieu2->setLongitude(0.23654);
        $manager->persist($lieu2);

        $lieu3 = new Lieu();
        $lieu3->setNom("Cervoiserie");
        $lieu3->setVille($ville);
        $lieu3->setRue("37 rue Sainte-Déville");
        $lieu3->setLatitude(1.02668);
        $lieu3->setLongitude(0.23654);
        $manager->persist($lieu3);


        $lieu4 = new Lieu();
        $lieu4->setNom("Port des minimes");
        $lieu4->setVille($ville1);
        $lieu4->setRue("les minimes");
        $lieu4->setLatitude(1.02668);
        $lieu4->setLongitude(0.23654);
        $manager->persist($lieu4);


        $lieu6 = new Lieu();
        $lieu6->setNom("Futuroscope");
        $lieu6->setVille($ville3);
        $lieu6->setRue("6 rue du futur");
        $lieu6->setLatitude(1.02668);
        $lieu6->setLongitude(0.23654);
        $manager->persist($lieu6);

        $lieu7 = new Lieu();
        $lieu7->setNom("CGR Cimena");
        $lieu7->setVille($ville4);
        $lieu7->setRue("Place gambetta");
        $lieu7->setLatitude(1.02668);
        $lieu7->setLongitude(0.23654);
        $manager->persist($lieu7);

        $lieu8 = new Lieu();
        $lieu8->setNom("Ecole ENI");
        $lieu8->setVille($ville);
        $lieu8->setRue("16 rue Léo Lagrange");
        $lieu8->setLatitude(1.02668);
        $lieu8->setLongitude(0.23654);
        $manager->persist($lieu8);

        // Create a few users with random data
        for ($i = 0; $i < 5; $i++) {
            $user = new Utilisateur();
            $user->setEmail('user' . $i . '@user.com');
            $user->setNom('user' . $i);
            $user->setPrenom('user' . $i);
            $user->setTelephone('0123456789');
            $user->setEstActif(true);
            $user->setRoles(['ROLE_USER']);
            $user->setPseudo('user' . $i);
            $user->setPassword($this->passwordHasher->hashPassword($user, '123456'));
            $user->setCampus($campus);
            $manager->persist($user);
        }

        // Création d'un admin
        $admin = new Utilisateur();
        $admin->setEmail('admin@admin.com');
        $admin->setNom('admin');
        $admin->setPrenom('admin');
        $admin->setTelephone('0123456789');
        $admin->setEstActif(true);
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPseudo('admin');
        $admin->setPassword($this->passwordHasher->hashPassword($user, '123456'));
        $admin->setCampus($campus);
        $manager->persist($admin);

        // Creation Etat
        $etat1 = new Etat();
        $etat1->setLibelle(EtatEnum::EN_CREATION);
        $manager->persist($etat1);

        $etat2 = new Etat();
        $etat2->setLibelle(EtatEnum::OUVERTE);
        $manager->persist($etat2);

        $etat3 = new Etat();
        $etat3->setLibelle(EtatEnum::CLOTUREE);
        $manager->persist($etat3);

        $etat4 = new Etat();
        $etat4->setLibelle(EtatEnum::EN_COURS);
        $manager->persist($etat4);

        $etat5 = new Etat();
        $etat5->setLibelle(EtatEnum::TERMINEE);
        $manager->persist($etat5);

        $etat6 = new Etat();
        $etat6->setLibelle(EtatEnum::ANNULEE);
        $manager->persist($etat6);

        $etat7 = new Etat();
        $etat7->setLibelle(EtatEnum::HISTORISEE);
        $manager->persist($etat7);

        // Creation de Sortie
        $sortie = new Sortie();
        $sortie->setNom("Prison Island");
        $sortie->setDateHeureDebut(new \DateTime('2023-12-01 14:00'));
        $sortie->setDuree(180);
        $sortie->setDateLimiteInscription(new \DateTime('2023-11-21 14:00'));
        $sortie->setNbInscriptionMax(9);
        $sortie->setInfosSortie("Sortie au Prison Island avec 3 équipes de 3");
        $sortie->setCampus($campus);
        $sortie->setLieu($lieu);
        $sortie->setEtat($etat5);
        $sortie->setOrganisateur($user);
        $manager->persist($sortie);

        $sortie1 = new Sortie();
        $sortie1->setNom("Patinoire");
        $sortie1->setDateHeureDebut(new \DateTime('2023-12-01 14:00'));
        $sortie1->setDuree(180);
        $sortie1->setDateLimiteInscription(new \DateTime('2023-11-21 14:00'));
        $sortie1->setNbInscriptionMax(2);
        $sortie1->setInfosSortie("Sortie à la patinoire de niort.");
        $sortie1->setCampus($campus);
        $sortie1->setLieu($lieu2);
        $sortie1->setEtat($etat7);
        $sortie1->setOrganisateur($user);
        $manager->persist($sortie1);

        $sortie4 = new Sortie();
        $sortie4->setNom("Pique-Nique à La Rochelle");
        $sortie4->setDateHeureDebut(new \DateTime('2023-10-21 12:00'));
        $sortie4->setDuree(180);
        $sortie4->setDateLimiteInscription(new \DateTime('2023-10-21 11:00'));
        $sortie4->setNbInscriptionMax(20);
        $sortie4->setInfosSortie("Pique-nique au bord de la plage des minimes.");
        $sortie4->setCampus($campus);
        $sortie4->setLieu($lieu4);
        $sortie4->setEtat($etat6);
        $sortie4->setOrganisateur($user);
        $manager->persist($sortie4);

        $sortie5 = new Sortie();
        $sortie5->setNom("Journée au futuroscope");
        $sortie5->setDateHeureDebut(new \DateTime('2024-10-04 09:00'));
        $sortie5->setDuree(420);
        $sortie5->setDateLimiteInscription(new \DateTime('2024-09-27 12:00'));
        $sortie5->setNbInscriptionMax(20);
        $sortie5->setInfosSortie("Journée au futuroscope. Prévoir de quoi manger sur place.");
        $sortie5->setCampus($campus);
        $sortie5->setLieu($lieu6);
        $sortie5->setEtat($etat3);
        $sortie5->setOrganisateur($user);
        $manager->persist($sortie5);

        $sortie6 = new Sortie();
        $sortie6->setNom("Journée au futuroscope");
        $sortie6->setDateHeureDebut(new \DateTime('2024-11-04 09:00'));
        $sortie6->setDuree(420);
        $sortie6->setDateLimiteInscription(new \DateTime('2024-10-27 23:00'));
        $sortie6->setNbInscriptionMax(20);
        $sortie6->setInfosSortie("Journée au futuroscope. Prévoir de quoi manger sur place.");
        $sortie6->setCampus($campus2);
        $sortie6->setLieu($lieu6);
        $sortie6->setEtat($etat2);
        $sortie6->setOrganisateur($user);
        $manager->persist($sortie6);

        $sortie8 = new Sortie();
        $sortie8->setNom("Journée au futuroscope");
        $sortie8->setDateHeureDebut(new \DateTime('2023-11-04 09:00'));
        $sortie8->setDuree(420);
        $sortie8->setDateLimiteInscription(new \DateTime('2023-10-27 12:00'));
        $sortie8->setNbInscriptionMax(20);
        $sortie8->setCampus($campus3);
        $sortie8->setLieu($lieu6);
        $sortie8->setEtat($etat5);
        $sortie8->setOrganisateur($user);
        $manager->persist($sortie8);

        $sortie9 = new Sortie();
        $sortie9->setNom("Journée au futuroscope");
        $sortie9->setDateHeureDebut(new \DateTime('2024-12-04 09:00'));
        $sortie9->setDuree(420);
        $sortie9->setDateLimiteInscription(new \DateTime('2024-11-27 12:00'));
        $sortie9->setNbInscriptionMax(20);
        $sortie9->setCampus($campus4);
        $sortie9->setLieu($lieu6);
        $sortie9->setEtat($etat1);
        $sortie9->setOrganisateur($user);
        $manager->persist($sortie9);

        $sortie7 = new Sortie();
        $sortie7->setNom("Cine-concert le seigneur des anneaux");
        $sortie7->setDateHeureDebut(new \DateTime('2023-10-28 18:00'));
        $sortie7->setDuree(180);
        $sortie7->setDateLimiteInscription(new \DateTime('2023-10-10 12:00'));
        $sortie7->setNbInscriptionMax(2);
        $sortie7->setInfosSortie("Voir le film le seigneur des anneaux en ciné-concert à bordeaux.");
        $sortie7->setCampus($campus2);
        $sortie7->setLieu($lieu7);
        $sortie7->setEtat($etat6);
        $sortie7->setOrganisateur($user);
        $manager->persist($sortie7);

        $sortie10 = new Sortie();
        $sortie10->setNom("Soirée d'intégration 2024");
        $sortie10->setDateHeureDebut(new \DateTime('2024-10-24 17:30'));
        $sortie10->setDuree(360);
        $sortie10->setDateLimiteInscription(new \DateTime('2024-10-24 12:00'));
        $sortie10->setNbInscriptionMax(30);
        $sortie10->setCampus($campus);
        $sortie10->setLieu($lieu3);
        $sortie10->setEtat($etat2);
        $sortie10->setOrganisateur($user);
        $manager->persist($sortie10);

        $manager->flush();
    }
}