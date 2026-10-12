<?php

namespace App\Controller;

use App\Repository\InventoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InventoryController extends AbstractController
{
    #[Route('/inventory', name: 'app_inventory_list')]
    public function index(InventoryRepository $inventoryRepository): Response
    {
        // On récupère tous les inventaires de la base de données
        $inventories = $inventoryRepository->findAll();

        return $this->render('inventory/index.html.twig', [
            'inventories' => $inventories,
        ]);
    }

    #[Route('/inventory/{id}', name: 'app_inventory_show', requirements: ['id' => '\d+'])]
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
}