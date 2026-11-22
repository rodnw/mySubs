<?php

namespace App\Controller;

use App\Repository\ShowcaseRepository;
use App\Repository\SubwooferRepository;
use App\Security\AccessChecker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(ShowcaseRepository $showcaseRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'latest_showcases' => $showcaseRepository->findPublished(3),
        ]);
    }

    #[Route('/my-account', name: 'app_my_account', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function myAccount(Request $request, AccessChecker $accessChecker, SubwooferRepository $subwooferRepository): Response
    {
        $member = $accessChecker->getMember();

        // marque-pages gardés en session, en ne gardant que les caissons encore consultables
        $bookmarks = [];
        $ids = $request->getSession()->get('bookmarks', []);
        if (is_array($ids) && count($ids) > 0) {
            foreach ($subwooferRepository->findBy(['id' => $ids]) as $subwoofer) {
                if ($accessChecker->canViewSubwoofer($subwoofer)) {
                    $bookmarks[] = $subwoofer;
                }
            }
        }

        return $this->render('home/my_account.html.twig', [
            'member' => $member,
            'soundSystem' => $member->getSoundSystem(),
            'showcases' => $member->getShowcases(),
            'bookmarks' => $bookmarks,
        ]);
    }
}
