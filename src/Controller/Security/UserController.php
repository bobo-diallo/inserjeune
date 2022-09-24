<?php

namespace App\Controller\Security;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user')]
#[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_DIPLOME')")]
class UserController extends AbstractController {
	private EntityManagerInterface $em;
	private UserRepository $userRepository;
	private UserPasswordHasherInterface $hasher;
	private RequestStack $requestStack;

	public function __construct(
		EntityManagerInterface      $em,
		UserRepository              $userRepository,
		UserPasswordHasherInterface $hasher,
		RequestStack                $requestStack
	) {
		$this->em = $em;
		$this->userRepository = $userRepository;
		$this->hasher = $hasher;
		$this->requestStack = $requestStack;
	}

	#[Route(path: '/', name: 'user_index', methods: ['GET'])]
	public function indexAction(): Response {
		return $this->render('user/index.html.twig', [
			'users' => $this->userRepository->findAll()
		]);
	}

	#[Route(path: '/new', name: 'user_new', methods: ['GET'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$user = new User();
		$form = $this->createForm(UserType::class, $user);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$user->setPassword($this->hasher->hashPassword($user, $user->getPlainPassword()));

			$this->em->persist($user);
			$this->em->flush();

			return $this->redirectToRoute('user_show', ['id' => $user->getId()]);
		}

		return $this->render('user/new.html.twig', [
			'user' => $user,
			'form' => $form->createView(),
		]);
	}

	#[Route(path: '{id}', name: 'user_show', methods: ['GET'])]
	public function showAction(User $user): Response {
		return $this->render('user/show.html.twig', [
			'user' => $user
		]);
	}

	#[Route(path: '/{id}/edit', name: 'user_edit', methods: ['GET'])]
	public function editAction(Request $request, User $user): RedirectResponse|Response {
		$editForm = $this->createForm(UserType::class, $user);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$this->em->flush();

			return $this->redirectToRoute('user_edit', ['id' => $user->getId()]);
		}

		return $this->render('user/edit.html.twig', [
			'user' => $user,
			'edit_form' => $editForm->createView(),
		]);
	}

	#[Route(path: '/delete/{id}', name: 'user_delete', methods: ['GET'])]
	public function deleteAction(Request $request, ?User $user): RedirectResponse {
		if (array_key_exists('HTTP_REFERER', $request->server->all())) {
			if ($user) {
				$this->removeRelations($user);
				$this->em->remove($user);
				$this->em->flush();
				$this->addFlash('success', 'La suppression est faite avec success');
			} else {
				$this->addFlash('warning', 'Impossible de suppression le pays');
				return $this->redirect($request->server->all()['HTTP_REFERER']);
			}
		}
		return $this->redirectToRoute('user_index');
	}

	/**
	 * @param User $user
	 */
	private function removeRelations(User $user) {
		if ($user->getPersonDegree()) {
			foreach ($user->getPersonDegree() as $diplome) {
				$this->em->remove($diplome);
			}
		}
		if ($user->getCompany()) {
			foreach ($user->getCompany() as $company) {
				$this->em->remove($company);
			}
		}
		if ($user->getSchool()) {
			foreach ($user->getSchool() as $school) {
				$this->em->remove($school);
			}
		}
	}
}
