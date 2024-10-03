<?php

namespace App\Controller;

use App\DTO\RechercheLieu;
use App\Entity\Lieu;
use App\Entity\Ville;
use App\Form\LieuModificationType;
use App\Form\LieuSearchType;
use App\Form\LieuType;
use App\Repository\LieuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class LieuController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/lieu', name: 'app_lieu')]
    public function index(LieuRepository $lieuRepository, Request $request): Response
    {
        $filtreRecherche = new RechercheLieu();
        $formRecherche = $this->createForm(LieuSearchType::class, $filtreRecherche);
        $formRecherche->handleRequest($request);
        $lieux = $lieuRepository->findAll();

        if ($formRecherche->isSubmitted() && $formRecherche->isValid()) {
            $filtreRecherche = $formRecherche->getData();
            $lieux = $lieuRepository->rechercheLieuAvecFiltre($filtreRecherche);
        }


        return $this->render('lieu/index.html.twig', [
            'title' => 'Les lieux',
            'lieux' => $lieux,
            'formLieu' => $formRecherche->createView(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/lieu/ajouter/ajax', name: 'ajax_ajouter_lieu', methods: ['POST'])]
    public function ajaxCreateLieu(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $lieu = new lieu();
        $form = $this->createForm(LieuType::class, $lieu, ['csrf_protection' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($lieu);
            $entityManager->flush();

            return new JsonResponse(['id' => $lieu->getId()], Response::HTTP_CREATED);
        }

        return new JsonResponse($this->getFormErrors($form), Response::HTTP_BAD_REQUEST);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/lieu/{id<\d+>}/modification', name: 'modification_lieu', methods: ['GET', 'POST'])]
    #[Route('/lieu/ajouter', name: 'ajouter_lieu', methods: ['GET', 'POST'])]
    public function ajouter(
        ?Lieu $lieu,
        Request $request,
        EntityManagerInterface $entityManager): Response
    {
        if (!$lieu) {
            $lieu = new lieu();
        }

        $form = $this->createForm(LieuType::class, $lieu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($lieu);
            $entityManager->flush();

            return $this->redirectToRoute('app_lieu');
        }

        return $this->render('lieu/_creation.html.twig', [
            'lieuModification' => $form->createView(),
            'lieu' => $lieu,
            'isEdit' => $lieu->getId() !== null,
        ]);
    }

    #[Route('/lieu/{id<\d+>}/supprimer', name: 'lieu_suppression', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function suppression_lieu(LieuRepository $lieuRepository, EntityManagerInterface $entityManager, int $id): Response
    {

        $lieuASupprimer = $lieuRepository->find($id);

        $entityManager->remove($lieuASupprimer);
        $entityManager->flush();
        $this->addFlash('success', "La lieu a bien été supprimé");

        return $this->redirectToRoute('app_lieu');
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


    /**
     * @param FormInterface $form
     * @return array
     */
    private function getFormErrors(FormInterface $form): array
    {
        $errors = [];

        // Erreurs globales du formulaire
        foreach ($form->getErrors() as $error) {
            $errors['global'][] = $error->getMessage();
        }

        // Erreurs des champs enfants
        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $childErrors = [];
                foreach ($child->getErrors() as $error) {
                    $childErrors[] = $error->getMessage();
                }
                $errors[$child->getName()] = $childErrors;
            }
        }

        return $errors;
    }
}
