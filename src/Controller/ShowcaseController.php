<?php

namespace App\Controller;

use App\Entity\Showcase;
use App\Form\ShowcaseType;
use App\Repository\ShowcaseRepository;
use App\Security\AccessChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/showcase')]
#[IsGranted('ROLE_USER')]
class ShowcaseController extends AbstractController
{
    public function __construct(private AccessChecker $accessChecker)
    {
    }

    private function denyUnlessCanManage(Showcase $showcase): void
    {
        if (!$this->accessChecker->canManageShowcase($showcase)) {
            throw $this->createAccessDeniedException('Cette vitrine ne vous appartient pas.');
        }
    }

    #[Route('', name: 'app_showcase_index', methods: ['GET'])]
    public function index(ShowcaseRepository $showcaseRepository): Response
    {
        return $this->render('showcase/index.html.twig', [
            'showcases' => $showcaseRepository->findPublished(),
        ]);
    }

    #[Route('/new', name: 'app_showcase_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $member = $this->accessChecker->getMember();

        $showcase = new Showcase();
        $showcase->setCreator($member);

        $form = $this->createForm(ShowcaseType::class, $showcase, [
            'sound_system' => $member->getSoundSystem(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($showcase);
            $entityManager->flush();

            $this->addFlash('message', 'Vitrine créée');

            return $this->redirectToRoute('app_showcase_show', ['id' => $showcase->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('showcase/new.html.twig', [
            'showcase' => $showcase,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_showcase_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Showcase $showcase): Response
    {
        if (!$this->accessChecker->canViewShowcase($showcase)) {
            throw $this->createAccessDeniedException('Cette vitrine n\'est pas encore publiée.');
        }

        return $this->render('showcase/show.html.twig', [
            'showcase' => $showcase,
            'can_manage' => $this->accessChecker->canManageShowcase($showcase),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_showcase_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Showcase $showcase, EntityManagerInterface $entityManager): Response
    {
        $this->denyUnlessCanManage($showcase);

        $form = $this->createForm(ShowcaseType::class, $showcase, [
            'sound_system' => $showcase->getCreator()->getSoundSystem(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('message', 'Vitrine mise à jour');

            return $this->redirectToRoute('app_showcase_show', ['id' => $showcase->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('showcase/edit.html.twig', [
            'showcase' => $showcase,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_showcase_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Showcase $showcase, EntityManagerInterface $entityManager): Response
    {
        $this->denyUnlessCanManage($showcase);

        if ($this->isCsrfTokenValid('delete'.$showcase->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($showcase);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_my_account', [], Response::HTTP_SEE_OTHER);
    }
}
