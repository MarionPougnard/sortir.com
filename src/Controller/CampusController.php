<?php

namespace App\Controller;

use App\DTO\RechercheCampus;
use App\Entity\Campus;
use App\Form\CampusSearchType;
use App\Form\CampusType;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CampusController extends AbstractController
{

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/campus', name: 'app_campus')]
    public function index(
        Request $request,
        CampusRepository $campusRepository): Response
    {
        $filtreRecherche = new RechercheCampus();
        $formRecherche = $this->createForm(CampusSearchType::class, $filtreRecherche);
        $formRecherche->handleRequest($request);
        $campuses = $campusRepository->findAll();

        if ($formRecherche->isSubmitted() && $formRecherche->isValid()) {
            $filtreRecherche = $formRecherche->getData();
            $campuses = $campusRepository->rechercheCampusAvecFiltre($filtreRecherche);
        }

        return $this->render('campus/index.html.twig', [
            'title' => 'les campus',
            'campuses' => $campuses,
            'formRecherche' => $formRecherche->createView(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/campus/ajouter', name: 'ajouter_campus', methods: ['GET', 'POST'])]
    #[Route('/campus/{id<\d+>}/modification', name: 'campus_modification', methods: ['GET', 'POST'])]
    public function ajouterModifier(
        ?Campus $campus,
        Request $request,
        EntityManagerInterface $entityManager): Response
    {
        if (!$campus) {
            $campus = new Campus();
        }

        $form = $this->createForm(CampusType::class, $campus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($campus);
            $entityManager->flush();

            return $this->redirectToRoute('app_campus');
        }

        return $this->render('campus/creation.html.twig', [
            'campusForm' => $form->createView(),
            'campus' => $campus,
            'isEdit' =>  $campus->getId() !== null
        ]);
    }

    #[Route('/campus/{id<\d+>}/supprimer', name: 'suppression_campus', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function suppression_campus(CampusRepository $campusRepository, EntityManagerInterface $entityManager, int $id): Response {

        $campusASupprimer = $campusRepository->find($id);

            $entityManager->remove($campusASupprimer);
            $entityManager->flush();
            $this->addFlash('success', "Le campus a bien été supprimé(e)");

        return $this->redirectToRoute('app_campus');
    }
}
