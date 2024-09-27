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
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LieuController extends AbstractController
{

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
            'ville' => $lieu->getVille()?->getid(),
        ]);
    }
}
