<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController {
	#[Route(path: '/', name: 'homepage', methods: ['GET'])]
	public function indexAction(Request $request): RedirectResponse {
		return $this->redirectToRoute('dasboard_index');
	}

	#[Route(path: '/rgpd_informations', name: 'rgpd_informations', methods: ['GET'])]
	public function showRgpdInformationsAction(): Response {

		return $this->render('information_rgpd.html.twig');
	}
}
