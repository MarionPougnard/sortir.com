<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Utilisateur;
use App\Form\CampusModificationType;
use App\Form\UtilisateurModificationType;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CampusController extends AbstractController
{

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/campus', name: 'app_campus')]
    public function index(CampusRepository $campusRepository): Response
    {
        $allCampus = $campusRepository->findAll();

        return $this->render('campus/index.html.twig', [
            'controller_name' => 'CampusController',
            'allCampus' => $allCampus
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

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/campus/{id<\d+>}/modification', name: 'campus_modification', methods: ['GET', 'POST'])]
    public function modificationCampus(Request $request, Campus $campus, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CampusModificationType::class, $campus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($campus);
            $entityManager->flush();

            return $this->redirectToRoute('app_campus');
        }

        return $this->render('campus/modification.html.twig', [
            'campus' => $campus,
            'modifCampusform' => $form->createView(),
        ]);


    }
}
