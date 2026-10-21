<?php

namespace App\Controller;

use App\Entity\Inventory;
use App\Form\InventoryType;
use App\Repository\InventoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InventoryController extends AbstractController
{
    #[Route('/inventory', name: 'app_inventory_list', methods: ['GET'])]
    public function index(InventoryRepository $inventoryRepository): Response
    {
        // On récupère tous les inventaires de la base de données
        $inventories = $inventoryRepository->findAll();

        return $this->render('inventory/index.html.twig', [
            'inventories' => $inventories,
        ]);
    }

    // Create : GET affiche le formulaire, POST le traite puis redirige (303)
    #[Route('/inventory/new', name: 'app_inventory_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $inventory = new Inventory();
        $form = $this->createForm(InventoryType::class, $inventory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($inventory);
            $entityManager->flush();

            // Make sure message will be displayed after redirect
            $this->addFlash('message', 'bien ajouté');

            return $this->redirectToRoute('app_inventory_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('inventory/new.html.twig', [
            'inventory' => $inventory,
            'form' => $form,
        ]);
    }

    #[Route('/inventory/{id}', name: 'app_inventory_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id, InventoryRepository $inventoryRepository): Response
    {
        // On récupère un inventaire précis via son ID
        $inventory = $inventoryRepository->find($id);

        if (!$inventory) {
            throw $this->createNotFoundException('Cet inventaire n\'existe pas.');
        }

        return $this->render('inventory/show.html.twig', [
            'inventory' => $inventory,
        ]);
    }

    // Update : même formulaire que la création, pré-rempli avec la collection existante
    #[Route('/inventory/{id}/edit', name: 'app_inventory_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Inventory $inventory, EntityManagerInterface $entityManager): Response
    {
        $originalSubwoofers = new ArrayCollection();

        // On crée un tableau ArrayCollection mémorisant les caissons de la collection avant la soumission
        foreach ($inventory->getSubwoofers() as $subwoofer) {
            $originalSubwoofers->add($subwoofer);
        }

        $form = $this->createForm(InventoryType::class, $inventory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // une fois que le formulaire est valide, les nouvelles données sont dans $inventory
            // on parcourt l'ancien tableau et on vérifie l'impact des suppressions
            foreach ($originalSubwoofers as $subwoofer) {

                if (false === $inventory->getSubwoofers()->contains($subwoofer)) {
                    // le Subwoofer n'est plus présent dans le tableau : on le supprime
                    $inventory->getSubwoofers()->removeElement($subwoofer);
                    $entityManager->persist($subwoofer);
                    $entityManager->remove($subwoofer);
                }

            }

            $entityManager->flush();

            return $this->redirectToRoute('app_inventory_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('inventory/edit.html.twig', [
            'inventory' => $inventory,
            'form' => $form,
        ]);
    }

    // Delete : uniquement en POST, protégé par un jeton CSRF
    #[Route('/inventory/{id}', name: 'app_inventory_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Inventory $inventory, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$inventory->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($inventory);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_inventory_list', [], Response::HTTP_SEE_OTHER);
    }
}
