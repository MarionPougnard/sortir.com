<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Entity\Utilisateur;
use App\Enum\EtatEnum;
use App\Form\AnnulationSortieFormType;
use App\Form\SortieCreationModificationType;
use App\Repository\SortieRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/sorties', name: 'sorties_')]
class SortieController extends AbstractController
{
    #[Route('/{id<\d+>}', name: 'detail', methods: ['GET'])]
    public function voirDetailSortir(
        SortieRepository $sortieRepository,
        int $id =null
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $sortie = $sortieRepository->find($id);


        return $this->render('sortie/afficherSortie.html.twig', [
            'title' => 'Afficher une sortie',
            'sortie' => $sortie,
            'utilisateur' => $user,
        ]);

    }

    #[Route('/creer', name: 'creer', methods: ['GET', 'POST'])]
    #[Route('/{id<\d+>}/modifier', name: 'modifier', methods: ['GET', 'POST'])]
    public function creerOuModifierSortie(
        Request $request,
        EntityManagerInterface $entityManager,
        ?Sortie $sortie = null,
    ): Response
    {
        if ($sortie === null) {
            $sortie = new Sortie();
            $organisateur = $this->getUser();
            $sortie->setOrganisateur($organisateur);
        } else {
            $organisateur = $this->getUser();
            if ($sortie->getOrganisateur() !== $organisateur) {
                $this->addFlash('error', 'Vous ne pouvez pas modifier cette sortie car vous n\'en êtes pas l\'organisateur.');
                return $this->redirectToRoute('app_accueil');
            }
            if ($sortie->getEtat()->getLibelle() !== EtatEnum::EN_CREATION->value) {
                $this->addFlash('error', 'Vous ne pouvez pas modifier cette sortie car elle n\'est plus en création.');
                return $this->redirectToRoute('app_accueil');
            }
        }

        $form = $this->createForm(SortieCreationModificationType::class, $sortie);

        if ($sortie->getLieu()) {
            $ville = $sortie->getLieu()->getVille();
            $form->get('ville')->setData($ville);
        }

        $form->handleRequest($request);

        $action = $request->get('action');

        if ($form->isSubmitted() && $form->isValid()) {
            $etatRepo = $entityManager->getRepository(Etat::class);
            if ($action === 'enregistrer') {
                $etat = $etatRepo->findOneBy(['libelle' => EtatEnum::EN_CREATION]);
                $sortie->setEtat($etat);
                $message = 'La sortie a bien été enregistrée.';
            }
            if ($action === 'publier') {
                $etat = $etatRepo->findOneBy(['libelle' => EtatEnum::OUVERTE]);
                $sortie->setEtat($etat);
                $message = 'La sortie a bien été publiée.';
            }
            try {
                $entityManager->persist($sortie);
                $entityManager->flush();
                $this->addFlash('success', $message);
            } catch (\Exception $exception) {
                $this->addFlash('error', 'Une erreur est survenue lors de l\'enregistrement de la sortie : ' . $exception->getMessage());
            }


            return $this->redirectToRoute('app_accueil');
        }
        return $this->render('sortie/creerSortie.html.twig', [
            'sortieform' => $form->createView(),
            'title' => $sortie->getId() ? 'Modifier une sortie' : 'Créer une sortie',
            'sortie' => $sortie
        ]);
    }

    #[Route('/{id<\d+>}/publier', name: 'publier', methods: ['GET','POST'])]
    public function publier(
        Sortie $sortie,
        EntityManagerInterface $entityManager
    ): Response {
        $etatRepo = $entityManager->getRepository(Etat::class);
        $etat = $etatRepo->findOneBy(['libelle' => EtatEnum::OUVERTE]);
        $sortie->setEtat($etat);
        $entityManager->persist($sortie);
        $entityManager->flush();
        $this->addFlash('success', 'La sortie a bien été publiée');

        return $this->redirectToRoute('app_accueil');
    }
    #[Route('/{id<\d+>}/supprimer', name: 'supprimer', methods: ['GET','POST'])]
    public function suppression(
        Sortie $sortie,
        EntityManagerInterface $entityManager
    ): Response {
        $entityManager->remove($sortie);
        $entityManager->flush();
        $this->addFlash('success', 'La sortie a bien été supprimée');

        return $this->redirectToRoute('app_accueil');
    }

    #[Route('/{id}/annuler', name: 'annuler', methods: ['GET', 'POST'])]
    public function annulerSortie(
        Request $request,
        Sortie $sortie,
        EntityManagerInterface $entityManager

    ): Response
    {
        $user = $this->getUser();
        if ($user !== $sortie->getOrganisateur()) {
           $this->addFlash('error', 'Vous n\'êtes pas autorisé à annuler cette sortie.');
        }
        $etatAnnule = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => 'Annulée']);

        $annulationSortieForm = $this->createForm(AnnulationSortieFormType::class, $sortie);
        $annulationSortieForm->handleRequest($request);

        if ($annulationSortieForm->isSubmitted() && $annulationSortieForm->isValid() && $sortie->getDateHeureDebut() > new DateTime('now'))
        {
            $sortie->setEtat($etatAnnule);
            $sortie->setMotifAnnulation($annulationSortieForm->get('motifAnnulation')->getData());
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'L\'annulation de la sortie a bien été enregistrée');

            return $this->redirectToRoute('app_accueil');
        }

        return $this->render('sortie/annulerSortie.html.twig', [
            'title'=>'Annuler une sortie',
            "sortie"=>$sortie,
            'annulationSortieForm'=>$annulationSortieForm->createView(),
        ]);
    }

    #[Route('/{id<\d+>}/inscrire', name: 'inscrire', methods: ['GET','POST'])]
    public function inscrire(
        Sortie $sortie,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var Utilisateur|null $utilisateur */
        $utilisateur = $this->getUser();
        if (!$utilisateur) {
            return $this->redirectToRoute('app_login');
        }

        if ($sortie
            && $sortie->getEtat()->getLibelle() === EtatEnum::OUVERTE->value
            && !$sortie->getParticipants()->contains($utilisateur)) {
            $sortie->addParticipant($utilisateur);
            $entityManager->flush();
            $this->addFlash('success', 'Vous êtes inscrit à la sortie');
        }

        if (count($sortie->getParticipants()) >= $sortie->getNbInscriptionMax()) {
            $etatCloturee = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => EtatEnum::CLOTUREE->value]);
            $sortie->setEtat($etatCloturee);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_accueil');
    }

    #[Route('/{id<\d+>}/desinscrire', name: 'desinscrire', methods: ['GET','POST'])]
    public function desinscrire(
        Sortie $sortie,
        EntityManagerInterface $entityManager
    ): Response
    {
        /** @var Utilisateur|null $utilisateur */
        $utilisateur = $this->getUser();

        if ($sortie
            && $sortie->getParticipants()->contains($utilisateur)
            && ($sortie->getEtat()->getLibelle() === EtatEnum::OUVERTE->value
            || $sortie->getEtat()->getLibelle() === EtatEnum::CLOTUREE->value)) {

            $sortie->removeParticipant($utilisateur);
            $entityManager->flush();
            $this->addFlash('success', 'Vous êtes désinscrit ');
        }

        return $this->redirectToRoute('app_accueil');
    }

}
