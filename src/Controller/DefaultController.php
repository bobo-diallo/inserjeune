<?php

namespace App\Controller;

use App\Entity\AvatarDTO;
use App\Entity\User;
use App\Form\AvatarType;
use App\Services\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class DefaultController extends AbstractController {
	#[Route(path: '/', name: 'homepage', methods: ['GET'])]
	public function indexAction(Request $request): RedirectResponse {
		return $this->redirectToRoute('dashboard_index');
	}

	#[Route(path: '/rgpd_informations', name: 'rgpd_informations', methods: ['GET'])]
	public function showRgpdInformationsAction(): Response {

		return $this->render('information_rgpd.html.twig');
	}

	#[Route(path: '/change_profile', name: 'change_profile', methods: ['GET', 'POST'])]
	#[IsGranted('ROLE_USER')]
	public function newAction(
		Request $request,
		Security $security,
		EntityManagerInterface $em,
		FileUploader $fileUploader
	): RedirectResponse|Response {
		$avatar = new AvatarDTO();

		$form = $this->createForm(AvatarType::class, $avatar);
		$form->handleRequest($request);
		/** @var User $user */
		$user = $security->getUser();

		if ($form->isSubmitted() && $form->isValid()) {
			$avatarDescription = $form->get('file')->getData();
			if ($avatarDescription) {
				$avatarDescriptionFileName = $fileUploader->uploadAvatar($avatarDescription);
				$user->setImageName($avatarDescriptionFileName);
			}

			$em->persist($user);
			$em->flush();
		}
		return $this->render('user/avatar.html.twig', [
			'avatar' => $avatar,
			'form' => $form->createView(),
			'username' => $user->getUserIdentifier(),
		]);
	}
}
