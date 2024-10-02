<?php

namespace App\Controller;

use App\DTO\RechercheVille;
use App\Entity\Lieu;
use App\Entity\Ville;
use App\Form\LieuModificationType;
use App\Form\LieuType;
use App\Form\VilleSearchType;
use App\Form\VilleType;
use App\Repository\LieuRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LieuController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/lieu', name: 'app_lieu')]
    public function index(LieuRepository $lieuRepository, Request $request): Response
    {
        $lieux = $lieuRepository->findAll();
/*        $filtreRecherche = new RechercheLieu();
        $formRecherche = $this->createForm(LieuSearchType::class, $filtreRecherche);
        $formRecherche->handleRequest($request);
        $lieux = $lieuRepository->findAll();

        if ($formRecherche->isSubmitted() && $formRecherche->isValid()) {
            $filtreRecherche = $formRecherche->getData();
            $lieux = $lieuRepository->rechercheLieuAvecFiltre($filtreRecherche);
        }*/

        return $this->render('lieu/index.html.twig', [
            'title' => 'Les lieux',
            'lieux' => $lieux,
        /*    'formLieu' => $formRecherche->createView(),*/

        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/lieu/ajouter', name: 'ajouter_lieu', methods: ['GET', 'POST'])]
    public function ajouter(Request $request, EntityManagerInterface $entityManager, LieuRepository $lieuRepository): Response
    {
        $lieu = new lieu();
        $form = $this->createForm(LieuModificationType::class, $lieu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $lieu = $form->getData();

            $entityManager->persist($lieu);
            $entityManager->flush();

            $lieux = $lieuRepository->findAll();

            return $this->redirectToRoute('app_lieu');
        }

        return $this->render('lieu/_creation.html.twig', [
            'lieuModification' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/lieu/{id<\d+>}/modification', name: 'modification_lieu', methods: ['GET', 'POST'])]
    public function lieuModification(Request $request, Lieu $lieu, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LieuModificationType::class, $lieu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($lieu);
            $entityManager->flush();

            return $this->redirectToRoute('app_lieu');
        }

        return $this->render('lieu/_modification.html.twig', [
            'lieu' => $lieu,
            'lieuModification' => $form->createView(),
        ]);
    }

    #[Route('/lieu/{id<\d+>}/supprimer', name: 'lieu_suppression', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function suppression_lieu(LieuRepository $lieuRepository, EntityManagerInterface $entityManager, int $id): Response {

        $lieuASupprimer = $lieuRepository->find($id);

        $entityManager->remove($lieuASupprimer);
        $entityManager->flush();
        $this->addFlash('success', "La lieu a bien été supprimé");

        return $this->redirectToRoute('app_lieu');
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/lieux/{ville}', name: 'lieux_par_ville', methods: ['GET'])]
    public function getLieuxByVille(Ville $ville, EntityManagerInterface $em): JsonResponse
    {
        $lieux = $em->getRepository(Lieu::class)->findBy(['ville' => $ville]);

        $lieuxArray = [];

        foreach ($lieux as $lieu) {
            $lieuxArray[] = [
                'id' => $lieu->getId(),
                'nom' => $lieu->getNom(),
            ];
        }

        return new JsonResponse($lieuxArray);
    }

    #[Route("/lieu/details/{id}", name: "lieu_details", methods: ["GET"])]
    public function getLieuDetails(LieuRepository $lieuRepository, $id): JsonResponse
    {
        $lieu = $lieuRepository->find($id);

        if (!$lieu) {
            return $this->json(['error' => 'Lieu not found'], 404);
        }

        return $this->json([
            'rue' => $lieu->getRue(),
            'latitude' => $lieu->getLatitude(),
            'longitude' => $lieu->getLongitude(),
            'ville' => $lieu->getVille()?->getid(),
        ]);
    }
}
