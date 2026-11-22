<?php

namespace App\Controller;

use App\Entity\Inventory;
use App\Entity\Subwoofer;
use App\Form\SubwooferType;
use App\Repository\SubwooferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FileUploadError;
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

            return $this->redirectToRoute('app_inventory_show', ['id' => $subwoofer->getInventory()->getId()], Response::HTTP_SEE_OTHER);
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

    // Create dans le contexte d'une collection : la collection est connue grâce à l'id de la route
    #[Route('/subwoofer/newininventory/{id}', name: 'app_subwoofer_newininventory', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function newInInventory(Request $request, EntityManagerInterface $entityManager, Inventory $inventory): Response
    {
        $subwoofer = new Subwoofer();
        // already set an inventory, so as to not need add that field in the form
        $subwoofer->setInventory($inventory);

        $form = $this->createForm(SubwooferType::class, $subwoofer,
            [
                'display_inventory' => false,
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($subwoofer);
            $entityManager->flush();

            return $this->redirectToRoute('app_inventory_show',
                ['id' => $inventory->getId()],
                Response::HTTP_SEE_OTHER);
        }

        return $this->render('subwoofer/newininventory.html.twig', [
            'inventory' => $inventory,
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
