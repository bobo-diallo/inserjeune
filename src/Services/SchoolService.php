<?php

namespace App\Services;

use App\Entity\School;
use App\Repository\SchoolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Entity\User;

class SchoolService {
	private TokenStorageInterface $tokenStorage;

	private EntityManagerInterface $manager;
	private SchoolRepository $schoolRepository;
	private RequestStack $requestStack;
	private RouterInterface $router;

	public function __construct(
		TokenStorageInterface $tokenStorage,
		EntityManagerInterface $manager,
		SchoolRepository $schoolRepository,
		RequestStack $requestStack,
		RouterInterface $router
	) {
		$this->tokenStorage = $tokenStorage;
		$this->manager = $manager;
		$this->schoolRepository = $schoolRepository;
		$this->requestStack = $requestStack;
		$this->router = $router;
	}

	public function getSchool(): ?School {
		$user = $this->tokenStorage->getToken()->getUser();
		return $this->schoolRepository->findOneBy(['user' => $user]);
	}

	public function removeRelations(User $user): void {
		$em = $this->manager;

		if ($user->getSchool()) {
			$em->remove($user->getSchool());
		}
	}

	public function checkUnCompletedAccountBefore(callable $executionActionController): mixed {
		$school = $this->getSchool();

		if (!$school) {
			$this->requestStack->getSession()->getFlashBag()->set('warning', 'Veuillez completer votre profil');

			return new RedirectResponse($this->router->generate('front_school_new'));
		} else {
			return $executionActionController();
		}
	}
}
