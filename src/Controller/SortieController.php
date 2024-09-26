<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Enum\EtatEnum;
use App\Form\AnnulationSortieFormType;
use App\Form\SortieCreationModificationType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sorties', name: 'sorties_')]
class SortieController extends AbstractController
{
    #[Route('/', name: 'liste')]
    public function montrerSorties(
        SortieRepository $sortieRepository,
        EtatRepository $etatRepository,
        EntityManagerInterface $entityManager,
    )
    {
        $sorties = $sortieRepository->findAll();
        foreach ($sorties as $sortie) {
            $sortie->verifierEtat($etatRepository);
        }

        $entityManager->flush();

        return $this->render('sortie/index.html.twig', [
            'title' => 'Liste des sorties',
            'sorties' => $sorties,
        ]);
    }

    #[Route('/creerSortie', name: 'creerSortie', methods: ['GET', 'POST'])]
    public function creerSortie(
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $sortie = new Sortie();
        $organisateur = $this->getUser();
        $sortie->setOrganisateur($organisateur);
        $form = $this->createForm(SortieCreationModificationType::class, $sortie);

        $form->handleRequest($request);

        $action = $request->get('action');

        if ($form->isSubmitted() && $form->isValid()) {
            if ($action === 'enregistrer') {
                $etat = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => EtatEnum::EN_CREATION]);
                $sortie->setEtat($etat);
            }
            if ($action === 'publier') {
                $etat = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => EtatEnum::OUVERTE]);
                $sortie->setEtat($etat);
            }
            try {
                $entityManager->persist($sortie);
                $entityManager->flush();
                $this->addFlash('success', 'La sortie a bien été créée');
            } catch (\Exception $exception) {
                $this->addFlash('error', 'Une erreur est survenue lors de l\'enregistrement de la sortie : ' . $exception->getMessage());
            }


            return $this->redirectToRoute('sorties_liste');
        }
        return $this->render('sortie/creerSortie.html.twig', [
            'sortieform' => $form->createView(),
            'title' => 'Créer une sortie',
            'sortie' => null
        ]);
    }

    #[Route('/creerSortie/{id}', name: 'modification', methods: ['GET', 'POST'])]
    public function modification(
        Request $request,
        Sortie $sortie,
        EntityManagerInterface $entityManager)
    {
        $form = $this->createForm(SortieCreationModificationType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('sorties_liste');
        }

        return $this->render('sortie/creerSortie.html.twig', [
            'sortieform' => $form->createView(),
            'sortie' => $sortie,
            'title' => 'Modifier une sortie',
        ]);
    }

    #[Route('/{id}', name: 'suppression', methods: ['POST'])]
    public function suppression(
        Request $request,
        Sortie $sortie,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$sortie->getId(), $request->request->get('_token'))) {
            $entityManager->remove($sortie);
            $entityManager->flush();
            $this->addFlash('success', 'La sortie a bien été supprimée');
        }

        return $this->redirectToRoute('sorties_liste');
    }

    #[Route('/{id}/annuler', name: 'annuler', methods: ['GET', 'POST'])]
    public function annulerSortie(
        Request $request,
        Sortie $sortie,
        EntityManagerInterface $entityManager

    ): Response
    {
        /*$user = $this->getUser();
        if ($user !== $sortie->getOrganisateur()) {
            return new JsonResponse(['message' => 'Vous n\'êtes pas autorisé à annuler cette sortie.'], JsonResponse::HTTP_FORBIDDEN);
        }*/
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

            return $this->redirectToRoute('sorties_liste');
        }

        return $this->render('sortie/annulerSortie.html.twig', [
            'title'=>'Annuler une sortie',
            "sortie"=>$sortie,
            'annulationSortieForm'=>$annulationSortieForm->createView(),
        ]);
    }

}
