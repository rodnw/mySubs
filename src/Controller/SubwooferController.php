<?php

namespace App\Controller;

use App\Repository\SubwooferRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SubwooferController extends AbstractController
{
    #[Route('/subwoofer/{id}', name: 'app_subwoofer_show', requirements: ['id' => '\d+'])]
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
}
