<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Entity\User;

class DashboardService {
	private TokenStorageInterface $tokenStorage;

	private RequestStack $requestStack;
	private RouterInterface $router;

	public function __construct(
		TokenStorageInterface $tokenStorage,
		RequestStack $requestStack,
		RouterInterface $router
	) {
		$this->tokenStorage = $tokenStorage;
		$this->requestStack = $requestStack;
		$this->router = $router;
	}

	public function checkAccountBefore(callable $executionActionController): mixed {
		/** @var User $user */
		$user = $this->tokenStorage->getToken()->getUser();
		if ($user->getSchool())
			return new RedirectResponse($this->router->generate('front_school_show'));
		else if ($user->getCompany()) {
			return new RedirectResponse($this->router->generate('front_company_show'));
		}
		else if ($user->getPersonDegree()) {
			return new RedirectResponse($this->router->generate('front_persondegree_show'));
		} else {
			return $executionActionController();
		}
	}
}
