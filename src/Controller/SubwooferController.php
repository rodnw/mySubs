<?php

namespace App\Controller;

use App\Entity\Subwoofer;
use App\Form\SubwooferType;
use App\Repository\SubwooferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SubwooferController extends AbstractController
{
    // Create : GET affiche le formulaire, POST le traite puis redirige (303)
    #[Route('/subwoofer/new', name: 'app_subwoofer_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $subwoofer = new Subwoofer();
        $form = $this->createForm(SubwooferType::class, $subwoofer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($subwoofer);
            $entityManager->flush();

            return $this->redirectToRoute('app_inventory_show', ['id' => $subwoofer->getInventory()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('subwoofer/new.html.twig', [
            'subwoofer' => $subwoofer,
            'form' => $form,
        ]);
    }

    #[Route('/subwoofer/{id}', name: 'app_subwoofer_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id, SubwooferRepository $subwooferRepository): Response
    {
        $subwoofer = $subwooferRepository->find($id);

        if (!$subwoofer) {
            throw $this->createNotFoundException('Ce caisson de basse n\'existe pas.');
        }

        return $this->render('subwoofer/show.html.twig', [
            'subwoofer' => $subwoofer,
        ]);
    }

    // Update : même formulaire que la création, pré-rempli avec le caisson existant
    #[Route('/subwoofer/{id}/edit', name: 'app_subwoofer_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Subwoofer $subwoofer, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SubwooferType::class, $subwoofer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_subwoofer_show', ['id' => $subwoofer->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('subwoofer/edit.html.twig', [
            'subwoofer' => $subwoofer,
            'form' => $form,
        ]);
    }

    // Delete : uniquement en POST, protégé par un jeton CSRF
    #[Route('/subwoofer/{id}', name: 'app_subwoofer_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Subwoofer $subwoofer, EntityManagerInterface $entityManager): Response
    {
        $inventoryId = $subwoofer->getInventory()->getId();

        if ($this->isCsrfTokenValid('delete'.$subwoofer->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($subwoofer);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_inventory_show', ['id' => $inventoryId], Response::HTTP_SEE_OTHER);
    }
}
