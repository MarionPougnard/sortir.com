<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Repository\LieuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LieuController extends AbstractController
{
    #[Route('/lieu', name: 'app_lieu')]
    public function index(): Response
    {
        return $this->render('lieu/index.html.twig', [
            'controller_name' => 'LieuController',
        ]);
    }

    #[Route('/lieux/{ville}', name: 'lieux_par_ville', methods: ['GET'])]
    public function getLieuxByVille(Ville $ville, EntityManagerInterface $em): JsonResponse
    {
        $lieux = $em->getRepository(Lieu::class)->findBy(['ville' => $ville]);

        $lieuxArray = [];

        foreach ($lieux as $lieu) {
            $lieuxArray[] = [
                'id' => $lieu->getId(),
                'nom' => $lieu->getNom(),
                'rue' => $lieu->getRue(),
                'latitude' => $lieu->getLatitude(),
                'longitude' => $lieu->getLongitude(),
                'ville' => [
                    'id' => $ville->getId(),
                    'nom' => $ville->getNom(),
                    'codePostal' => $ville->getCodePostal(),
                ]
            ];
        }

        return new JsonResponse($lieuxArray);
    }

    #[Route("/lieu/details/{id}", name:"lieu_details", methods: ["GET"])]
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
        ]);
    }
}
