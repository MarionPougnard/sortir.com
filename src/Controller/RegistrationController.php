<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\RegistrationFormType;
use App\Repository\UtilisateurRepository;
use App\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

class RegistrationController extends AbstractController
{

    #[Route('/register', name: 'app_register')]
    public function register(Request $request,
                             UserPasswordHasherInterface $userPasswordHasher,
                             EntityManagerInterface $entityManager,
                             SluggerInterface $slugger,
                             #[Autowire('%kernel.project_dir%/public/img/profil')] string $uploadImageDir,
                             UtilisateurRepository $utilisateurRepository,
                             Security $security,
    ): Response
    {
        $user = new Utilisateur();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        $isAdmin = $security->isGranted('ROLE_ADMIN');
        $title = $isAdmin ? 'Créer un nouvel utilisateur' : 'Créer votre compte';

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $user->setRoles(['ROLE_USER']);
            $user->setEstActif(true);

            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            // Upload image
            $uploadImageFile = $form->get('photo')->getData();
            if ($uploadImageFile) {
                $originalFilename = pathinfo($uploadImageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadImageFile->guessExtension();

                try {
                    $uploadImageFile->move($uploadImageDir, $newFilename);
                } catch (FileException $e) {
                    throw $this->createNotFoundException($e->getMessage());
                }
                $user->setPhoto($newFilename);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            if ($isAdmin) {
                return $this->render('utilisateur/_liste.html.twig', [
                    'utilisateurs' => $utilisateurRepository->findAll(),
                    'modifprofilform' => $form->createView(),
                ]);
            } else {
                return $security->login($user, AppAuthenticator::class, 'main');
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
            'title' => $title,
        ]);
    }
}
