<?php

namespace App\Controller;

use App\Entity\SoundSystem;
use App\Form\SoundSystemType;
use App\Repository\SoundSystemRepository;
use App\Security\AccessChecker;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SoundSystemController extends AbstractController
{
    public function __construct(private AccessChecker $accessChecker)
    {
    }

    private function denyUnlessCanManage(SoundSystem $soundSystem): void
    {
        if (!$this->accessChecker->canManageSoundSystem($soundSystem)) {
            throw $this->createAccessDeniedException('Cette collection est privée.');
        }
    }

    #[Route('/sound-system', name: 'app_sound_system_list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(SoundSystemRepository $soundSystemRepository): Response
    {
        // On récupère toutes les collections de la base de données
        $soundSystems = $soundSystemRepository->findAll();

        return $this->render('sound_system/index.html.twig', [
            'soundSystems' => $soundSystems,
        ]);
    }

    #[Route('/sound-system/new', name: 'app_sound_system_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $soundSystem = new SoundSystem();
        $form = $this->createForm(SoundSystemType::class, $soundSystem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($soundSystem);
            $entityManager->flush();

            // Make sure message will be displayed after redirect
            $this->addFlash('message', 'bien ajouté');

            return $this->redirectToRoute('app_sound_system_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sound_system/new.html.twig', [
            'soundSystem' => $soundSystem,
            'form' => $form,
        ]);
    }

    #[Route('/sound-system/{id}', name: 'app_sound_system_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id, SoundSystemRepository $soundSystemRepository): Response
    {
        // On récupère une collection précise via son ID
        $soundSystem = $soundSystemRepository->find($id);

        if (!$soundSystem) {
            throw $this->createNotFoundException('Cette collection n\'existe pas.');
        }
        $this->denyUnlessCanManage($soundSystem);

        return $this->render('sound_system/show.html.twig', [
            'soundSystem' => $soundSystem,
        ]);
    }

    #[Route('/sound-system/{id}/edit', name: 'app_sound_system_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, SoundSystem $soundSystem, EntityManagerInterface $entityManager): Response
    {
        $this->denyUnlessCanManage($soundSystem);

        $originalSubwoofers = new ArrayCollection();

        // On crée un tableau ArrayCollection mémorisant les caissons de la collection avant la soumission
        foreach ($soundSystem->getSubwoofers() as $subwoofer) {
            $originalSubwoofers->add($subwoofer);
        }

        $form = $this->createForm(SoundSystemType::class, $soundSystem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // une fois que le formulaire est valide, les nouvelles données sont dans $soundSystem
            // on parcourt l'ancien tableau et on vérifie l'impact des suppressions
            foreach ($originalSubwoofers as $subwoofer) {

                if (false === $soundSystem->getSubwoofers()->contains($subwoofer)) {
                    // le Subwoofer n'est plus présent dans le tableau : on le supprime
                    $soundSystem->getSubwoofers()->removeElement($subwoofer);
                    $entityManager->persist($subwoofer);
                    $entityManager->remove($subwoofer);
                }

            }

            $entityManager->flush();

            return $this->redirectToRoute('app_sound_system_show', ['id' => $soundSystem->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sound_system/edit.html.twig', [
            'soundSystem' => $soundSystem,
            'form' => $form,
        ]);
    }

    #[Route('/sound-system/{id}', name: 'app_sound_system_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, SoundSystem $soundSystem, EntityManagerInterface $entityManager): Response
    {
        if (null !== $soundSystem->getMember()) {
            $this->addFlash('error', 'Cette collection appartient à un membre et ne peut pas être supprimée.');

            return $this->redirectToRoute('app_sound_system_show', ['id' => $soundSystem->getId()], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete'.$soundSystem->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($soundSystem);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_sound_system_list', [], Response::HTTP_SEE_OTHER);
    }
}
