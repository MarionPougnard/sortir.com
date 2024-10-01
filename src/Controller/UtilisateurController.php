<?php

namespace App\Controller;

use App\DTO\RechercheUtilisateur;
use App\Entity\Utilisateur;
use App\Form\RechercheSortieFormType;
use App\Form\UtilisateurModificationType;
use App\Form\UtilisateurSearchType;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UtilisateurController extends AbstractController
{
    #[Route('/profil', name: 'utilisateur_profil')]
    #[Route('/profil/{id<\d+>}', name: 'utilisateur_profil_id')]
    #[IsGranted('ROLE_USER')]
    public function voirProfil(
        ?Utilisateur $utilisateur,
        UserInterface $utilisateurConnecte
    ): Response
    {
        if ($utilisateur === null) {
            $utilisateur = $utilisateurConnecte;
        }

        $estutilisateur = $utilisateur->getId() === $utilisateurConnecte->getId();
        return $this->render('utilisateur/_profil.html.twig', [
            'utilisateur' => $utilisateur,
            'estutilisateur' => $estutilisateur,
       ]);
    }

    // Route page liste utilisateurs
    #[Route('/liste_utilisateurs', name: 'utilisateur_liste')]
    #[IsGranted('ROLE_ADMIN')]
    public function listeUtilisateurs(
        Request $request,
        UtilisateurRepository $utilisateurRepository
    ): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $filtreRecherche = new RechercheUtilisateur();
        $formRecherche = $this->createForm(UtilisateurSearchType::class, $filtreRecherche);
        $formRecherche->handleRequest($request);
        $utilisateurs = $utilisateurRepository->findAll();

        if ($formRecherche->isSubmitted() && $formRecherche->isValid()) {
            $filtreRecherche = $formRecherche->getData();
            $utilisateurs = $utilisateurRepository->chercheUtilisateurAvecFiltre($filtreRecherche);
        }

        return $this->render('utilisateur/_liste.html.twig', [
            'title' => 'liste des utilisateurs',
            'utilisateurs' => $utilisateurs,
            'formRecherche' => $formRecherche->createView(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id<\d+>}/modification', name: 'utilisateur_modification', methods: ['GET', 'POST'])]
    public function modificationProfil(Request $request, Utilisateur $utilisateur, EntityManagerInterface $entityManager): Response
    {
          if ($this->getUser() === $utilisateur) {

            $form = $this->createForm(UtilisateurModificationType::class, $utilisateur);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                /** @var UploadedFile $file */
                $file = $form->get('photo')->getData();
                if (!\is_null($file)) {
                    $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                    try {
                        $file->move('../public/img/profil', $fileName);
                    } catch (FileException $e) {

                    }
                    $utilisateur->setPhoto($fileName);
                }

                $entityManager->flush();

                return $this->redirectToRoute('utilisateur_profil', [
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

    // Pour la modif d'un utilisateur de la part d'un admin
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id<\d+>}/admin/modification', name: 'admin_utilisateur_modification', methods: ['GET', 'POST'])]
    public function adminModificationUtilisateurProfil(Request $request, Utilisateur $utilisateur, EntityManagerInterface $entityManager, UtilisateurRepository $utilisateurRepository): Response
    {
            $form = $this->createForm(UtilisateurModificationType::class, $utilisateur);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                /** @var UploadedFile $file */
                $file = $form->get('photo')->getData();
                if (!\is_null($file)) {
                    $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                    try {
                        $file->move('../public/img/profil', $fileName);
                    } catch (FileException $e) {

                    }
                    $utilisateur->setPhoto($fileName);
                }

                $entityManager->flush();

                return $this->redirectToRoute('utilisateur_liste');
            }

            return $this->render('utilisateur/_modification.html.twig', [
                'utilisateur' => $utilisateur,
                'modifprofilform' => $form->createView(),
            ]);
    }

    #[Route('{id<\d+>}/supprimer', name: 'suppression_profil', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function suppression_profil(
        Request $request,
        Utilisateur $utilisateur,
        EntityManagerInterface $entityManager,
        UtilisateurRepository $utilisateurRepository,
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$utilisateur->getId(), $request->request->get('_token'))) {
            $entityManager->remove($utilisateur);
            $entityManager->flush();
            $this->addFlash('success', "L'étudiant(e) a bien été supprimé(e)");
        }

        return $this->render('utilisateur/_liste.html.twig', [
            'id' => $utilisateur->getId(),
            'utilisateurs' => $utilisateurRepository->findAll()
        ]);
    }

}
