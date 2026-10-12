<?php

namespace App\Controller;

use App\Repository\AlbumRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AlbumController extends AbstractController
{
    #[Route('/album/{id}', name: 'app_album_show', requirements: ['id' => '\d+'])]
    public function show(int $id, AlbumRepository $albumRepository): Response
    {
        $album = $albumRepository->find($id);

        if (!$album) {
            throw $this->createNotFoundException('Cet album n\'existe pas.');
        }

        return $this->render('album/show.html.twig', [
            'album' => $album,
        ]);
    }
}