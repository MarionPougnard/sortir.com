<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\Utilisateur;
use App\Form\UtilisateurModificationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UtilisateurController extends AbstractController
{
    // Route page profil utilisateur
    #[Route('/profil', name: 'utilisateur_profil')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        $utilisateur  = $this->getUser();
        return $this->render('utilisateur/_profil.html.twig', [
            'utilisateur' => $utilisateur
               ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id<\d+>}/modification', name: 'utilisateur_modification', methods: ['GET', 'POST'])]
    public function modificationProfil(Request $request, Utilisateur $utilisateur, EntityManagerInterface $entityManager): Response
    {
          if ($this->getUser() == $utilisateur) {
            $form = $this->createForm(UtilisateurModificationType::class, $utilisateur);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                /** @var UploadedFile $file */
                /*$file = $form->get('photo')->getData();
                if (!\is_null($file)) {
                    $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                    try {
                        $file->move('../public/img/profil', $fileName);
                    } catch (FileException $e) {

                    }
                    $utilisateur->setPicture($fileName);
                }*/

                $entityManager->persist($utilisateur);
                $entityManager->flush();

                return $this->redirectToRoute('utilisateur', [
                    'id' => $utilisateur->getId(),
                ]);
            }

            return $this->render('utilisateur/_modification.html.twig', [
                'utilisateur' => $utilisateur,
                'modifprofilform' => $form->createView(),
            ]);
        } else {
            return $this->redirectToRoute('app_accueil');
        }

    }

    #[Route('{id<\d+>}/supprimer', name: 'suppression_profil', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function suppression_profil(
        Request $request,
        Utilisateur $utilisateur,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$utilisateur->getId(), $request->request->get('_token'))) {
            $entityManager->remove($utilisateur);
            $entityManager->flush();
            $this->addFlash('success', "L'étudiant(e) a bien été supprimé(e)");
        }

        return $this->redirectToRoute('accueil');
    }

}
