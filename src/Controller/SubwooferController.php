<?php

namespace App\Controller;

use App\Entity\SoundSystem;
use App\Entity\Subwoofer;
use App\Form\SubwooferType;
use App\Repository\SubwooferRepository;
use App\Security\AccessChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FileUploadError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SubwooferController extends AbstractController
{
    public function __construct(private AccessChecker $accessChecker)
    {
    }

    private function denyUnlessCanManage(Subwoofer $subwoofer): void
    {
        if (!$this->accessChecker->canManageSubwoofer($subwoofer)) {
            throw $this->createAccessDeniedException('Ce caisson de basse ne vous appartient pas.');
        }
    }

    #[Route('/subwoofer/new', name: 'app_subwoofer_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // un membre n'ajoute des caissons que dans sa propre collection
        if (!$this->accessChecker->isAdmin()) {
            return $this->redirectToRoute('app_subwoofer_newinsoundsystem',
                ['id' => $this->accessChecker->getMember()->getSoundSystem()->getId()]);
        }

        $subwoofer = new Subwoofer();
        $form = $this->createForm(SubwooferType::class, $subwoofer);
        $form->handleRequest($request);

        // explode the submitted check and the valid check
        $valid=false;
        $submitted=$form->isSubmitted();
        dump($submitted);
        if($submitted) {
            $valid=$form->isValid();
            dump($valid);
        }

        if ($submitted && $valid) {
            $entityManager->persist($subwoofer);
            $entityManager->flush();

            return $this->redirectToRoute('app_sound_system_show', ['id' => $subwoofer->getSoundSystem()->getId()], Response::HTTP_SEE_OTHER);
        }

        // now, we know invalid data was submitted
        if(! $valid) {
            // check all errors from all of the form's fields (in depth)
            $errors = $form->getErrors(true);

            foreach($errors as $error) {
                dump($error);

                // now, if we find a problem of file upload, propagate it to the whole form
                if($error instanceof FileUploadError) {
                    $form->addError($error);
                }
            }
        }

        // in that re-rendering, the main error message should be displayed
        return $this->render('subwoofer/new.html.twig', [
            'subwoofer' => $subwoofer,
            'form' => $form,
        ]);
    }

    #[Route('/subwoofer/newinsoundsystem/{id}', name: 'app_subwoofer_newinsoundsystem', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function newInSoundSystem(Request $request, EntityManagerInterface $entityManager, SoundSystem $soundSystem): Response
    {
        if (!$this->accessChecker->canManageSoundSystem($soundSystem)) {
            throw $this->createAccessDeniedException('Cette collection est privée.');
        }

        $subwoofer = new Subwoofer();
        // already set an soundSystem, so as to not need add that field in the form
        $subwoofer->setSoundSystem($soundSystem);

        $form = $this->createForm(SubwooferType::class, $subwoofer,
            [
                'display_sound_system' => false,
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($subwoofer);
            $entityManager->flush();

            return $this->redirectToRoute('app_sound_system_show',
                ['id' => $soundSystem->getId()],
                Response::HTTP_SEE_OTHER);
        }

        return $this->render('subwoofer/newinsoundsystem.html.twig', [
            'soundSystem' => $soundSystem,
            'subwoofer' => $subwoofer,
            'form' => $form,
        ]);
    }

    #[Route('/subwoofer/{id}', name: 'app_subwoofer_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(int $id, SubwooferRepository $subwooferRepository): Response
    {
        $subwoofer = $subwooferRepository->find($id);

        if (!$subwoofer) {
            throw $this->createNotFoundException('Ce caisson de basse n\'existe pas.');
        }
        if (!$this->accessChecker->canViewSubwoofer($subwoofer)) {
            throw $this->createAccessDeniedException('Ce caisson de basse est privé.');
        }

        return $this->render('subwoofer/show.html.twig', [
            'subwoofer' => $subwoofer,
            'can_manage' => $this->accessChecker->canManageSubwoofer($subwoofer),
        ]);
    }

    /**
     * Ajoute ou retire un caisson de basse des marque-pages du membre, gardés dans sa session
     */
    #[Route('/subwoofer/mark/{id}', name: 'app_subwoofer_mark', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function markAction(Request $request, Subwoofer $subwoofer): Response
    {
        if (!$this->accessChecker->canViewSubwoofer($subwoofer)) {
            throw $this->createAccessDeniedException('Ce caisson de basse est privé.');
        }

        // Récupération du tableau d'id des marque-pages dans la session
        $bookmarks = $request->getSession()->get('bookmarks');
        if( ! is_array($bookmarks) ) {
            $bookmarks = array();
        }

        $id = $subwoofer->getId();
        // si l'identifiant n'est pas présent dans les marque-pages, l'ajouter
        if (! in_array($id, $bookmarks) )
        {
            $bookmarks[] = $id;
            $this->addFlash('message', $subwoofer . ' ajouté aux marque-pages');
        }
        else
        // sinon, le retirer du tableau
        {
            $bookmarks = array_values(array_diff($bookmarks, array($id)));
            $this->addFlash('message', $subwoofer . ' retiré des marque-pages');
        }

        // Sauvegarde du tableau des marque-pages dans la session
        $request->getSession()->set('bookmarks', $bookmarks);

        // retour à la page d'origine (vitrine, fiche ou tableau de bord)
        $referer = $request->headers->get('referer');
        if ($referer && str_starts_with($referer, $request->getSchemeAndHttpHost())) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('app_subwoofer_show',
                                      ['id' => $subwoofer->getId()]);
    }

    #[Route('/subwoofer/{id}/edit', name: 'app_subwoofer_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Subwoofer $subwoofer, EntityManagerInterface $entityManager): Response
    {
        $this->denyUnlessCanManage($subwoofer);

        // seul un administrateur peut déplacer un caisson vers une autre collection
        $form = $this->createForm(SubwooferType::class, $subwoofer, [
            'display_sound_system' => $this->accessChecker->isAdmin(),
        ]);
        $form->handleRequest($request);

        // explode the submitted check and the valid check
        $valid=false;
        $submitted=$form->isSubmitted();
        dump($submitted);
        if($submitted) {
            $valid=$form->isValid();
            dump($valid);
        }

        if ($submitted && $valid) {
            $entityManager->flush();

            return $this->redirectToRoute('app_subwoofer_show', ['id' => $subwoofer->getId()], Response::HTTP_SEE_OTHER);
        }

        // now, we know invalid data was submitted
        if(! $valid) {
            // check all errors from all of the form's fields (in depth)
            $errors = $form->getErrors(true);

            foreach($errors as $error) {
                dump($error);

                // now, if we find a problem of file upload, propagate it to the whole form
                if($error instanceof FileUploadError) {
                    $form->addError($error);
                }
            }
        }

        // in that re-rendering, the main error message should be displayed
        return $this->render('subwoofer/edit.html.twig', [
            'subwoofer' => $subwoofer,
            'form' => $form,
        ]);
    }

    #[Route('/subwoofer/{id}', name: 'app_subwoofer_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, Subwoofer $subwoofer, EntityManagerInterface $entityManager): Response
    {
        $this->denyUnlessCanManage($subwoofer);

        $soundSystemId = $subwoofer->getSoundSystem()->getId();

        if ($this->isCsrfTokenValid('delete'.$subwoofer->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($subwoofer);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_sound_system_show', ['id' => $soundSystemId], Response::HTTP_SEE_OTHER);
    }
}
