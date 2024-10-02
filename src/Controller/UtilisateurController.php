<?php

namespace App\Controller;

use App\DTO\RechercheUtilisateur;
use App\Entity\Campus;
use App\Entity\Utilisateur;
use App\Form\ImportCSVType;
use App\Form\UtilisateurModificationType;
use App\Form\UtilisateurSearchType;
use App\Repository\UtilisateurRepository;
use ContainerEWXSNXm\getSecurity_RoleHierarchyService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
            'isAdmin' => in_array('ROLE_ADMIN', $this->getUser()->getRoles(), true)
       ]);
    }

    // Route page liste utilisateurs
    #[Route('/liste_utilisateurs', name: 'utilisateur_liste')]
    #[IsGranted('ROLE_ADMIN')]
    public function listeUtilisateurs(
        Request $request,
        UtilisateurRepository $utilisateurRepository,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $userPasswordHasher,
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

        $formUpload = $this->createForm(ImportCsvType::class);
        $formUpload->handleRequest($request);

        if ($formUpload->isSubmitted() && $formUpload->isValid()) {
            $file = $formUpload->get('csvFile')->getData();

            if ($file) {
                $filename = uniqid() . '.' . $file->guessExtension();

                try {
                    $file->move($this->getParameter('uploads_directory'), $filename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors de l\'upload du fichier');
                    return $this->redirectToRoute('utilisateur_liste');
                }

                $errors = $this->processCsv($this->getParameter('uploads_directory') . '/' . $filename, $em, $userPasswordHasher);

                if (empty($errors)) {
                    $this->addFlash('success', 'Importation réussie !');
                } else {
                    foreach ($errors as $error) {
                        $this->addFlash('danger', $error);                    }
                }
            }
        }

        return $this->render('utilisateur/_liste.html.twig', [
            'title' => 'liste des utilisateurs',
            'utilisateurs' => $utilisateurs,
            'formRecherche' => $formRecherche->createView(),
            'formUpload' => $formUpload->createView(),
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
            $estParticipant = $utilisateurRepository->estInscritASortiesNonHistorisee($utilisateur);
            if($estParticipant){
                $this->addFlash('danger', "Cet.te utilisateur.trice ne peut pas être supprimé.e car iel participe à des sorties.");
                return $this->redirectToRoute('utilisateur_liste');
            }
            $entityManager->remove($utilisateur);
            $entityManager->flush();
            $this->addFlash('success', "Cet.te utilisateur.trice a bien été supprimé.e");
        }

        return $this->redirectToRoute('utilisateur_liste');
    }

    private function processCsv(
        $filePath,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $userPasswordHasher)
    {
        $csv = fopen($filePath, 'r');
        $header = fgetcsv($csv); // Lecture de l'en-tête
        $campusRepository = $em->getRepository(Campus::class);
        $utilisateurRepository = $em->getRepository(Utilisateur::class);
        $errors = [];

        try {
            $em->beginTransaction();
            while (($row = fgetcsv($csv)) !== false) {
                if (count($row) !== count($header)) {
                    $errors[] = "Nombre de colonnes incorrect pour la ligne : " . json_encode($row);
                    continue;
                }
                $userData = array_combine($header, $row);

                if (empty($userData['nom']) || empty($userData['prenom']) || empty($userData['email']) || empty($userData['password']) || empty($userData['pseudo'])) {
                    $errors[] = "Les champs requis sont manquants pour : " . json_encode($userData);
                    continue;
                }

                $existingUserByEmail = $utilisateurRepository->findOneBy(['email' => $userData['email']]);
                if ($existingUserByEmail) {
                    $errors[] = "L'email est déjà utilisé pour : " . $userData['email'];
                    continue;
                }

                $existingUserByPseudo = $utilisateurRepository->findOneBy(['pseudo' => $userData['pseudo']]);
                if ($existingUserByPseudo) {
                    $errors[] = "Le pseudo est déjà utilisé pour : " . $userData['pseudo'];
                    continue;
                }

                if (isset($userData['campus'])) {
                    $campus = $campusRepository->findOneBy(['id' => $userData['campus']]);
                    if (!$campus) {
                        $errors[] = "Le campus est manquant pour : " . json_encode($userData);
                        continue;
                    }
                }

                $user = new Utilisateur();
                $user->setNom($userData['nom']);
                $user->setPrenom($userData['prenom']);
                $user->setEmail($userData['email']);
                $user->setPseudo($userData['pseudo']);
                $user->setTelephone($userData['telephone']);
                $user->setCampus($campus);


                /** @var string $plainPassword */
                $plainPassword = $userData['password'];
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

                $user->setRoles(['ROLE_USER']);
                $user->setEstActif(true);

                $em->persist($user);
            }
            if (empty($errors)) {
                $em->flush();
                $em->commit();
            } else {
                $em->rollback();
            }


        } catch (\Exception $e) {
            $em->rollback();
            $errors[] = "Une erreur est survenue : " . $e->getMessage();
        } finally {
            fclose($csv);
        }

        return $errors;
    }

}
