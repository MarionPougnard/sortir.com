<?php

namespace App\Controller;

use App\DTO\RechercheVille;
use App\Entity\Ville;
use App\Form\VilleSearchType;
use App\Form\VilleType;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class VilleController extends AbstractController
{
    #[Route('/ville', name: 'app_ville')]
    public function index(
        VilleRepository $villeRepository,
        Request $request,
    ): Response
    {
        $filtreRecherche = new RechercheVille();
        $formRecherche = $this->createForm(VilleSearchType::class, $filtreRecherche);
        $formRecherche->handleRequest($request);
        $villes = $villeRepository->findAll();

        if ($formRecherche->isSubmitted() && $formRecherche->isValid()) {
            $filtreRecherche = $formRecherche->getData();
            $villes = $villeRepository->rechercheVilleAvecFiltre($filtreRecherche);
        }

        return $this->render('ville/index.html.twig', [
            'title' => 'Les villes',
            'villes' => $villes,
            'formVille' => $formRecherche->createView(),

        ]);
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/ville/ajouter', name: 'ajouter_ville', methods: ['GET', 'POST'])]
    public function ajouter(Request $request, EntityManagerInterface $entityManager, VilleRepository $villeRepository): Response
    {
        $ville = new ville();
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        //TODO: faire que le couple nom, code postal soit unique
        if ($form->isSubmitted() && $form->isValid()) {
            $ville = $form->getData();

            $entityManager->persist($ville);
            $entityManager->flush();

            $villes = $villeRepository->findAll();

            return $this->redirectToRoute('app_ville');
        }

        return $this->render('ville/_creation.html.twig', [
            'villeForm' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/ville/{id<\d+>}/modification', name: 'ville_modification', methods: ['GET', 'POST'])]
    public function modificationville(Request $request, Ville $ville, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($ville);
            $entityManager->flush();

            return $this->redirectToRoute('app_ville');
        }

        return $this->render('ville/_modification.html.twig', [
            'ville' => $ville,
            'villeModification' => $form->createView(),
        ]);
    }

    #[Route('/ville/{id<\d+>}/supprimer', name: 'suppression_ville', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function suppression_ville(VilleRepository $villeRepository, EntityManagerInterface $entityManager, int $id): Response {

        $villeASupprimer = $villeRepository->find($id);

        $entityManager->remove($villeASupprimer);
        $entityManager->flush();
        $this->addFlash('success', "La ville a bien été supprimée");

        return $this->redirectToRoute('app_ville');
    }
}
