<?php

namespace App\Services;

use App\Entity\Role;
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
		if ($user->getPersonDegree() || $user->hasRole(Role::ROLE_DIPLOME)) {
			return new RedirectResponse($this->router->generate('front_persondegree_show'));
		}
		else if (!$user->getCompany() && $user->hasRole(Role::ROLE_ENTREPRISE)) {
			$this->requestStack->getSession()->getFlashBag()->set('warning', 'Veuillez completer votre profil');
			return new RedirectResponse($this->router->generate('front_company_new'));
		}
		else if (!$user->getSchool() && $user->hasRole(Role::ROLE_ETABLISSEMENT)) {
			$this->requestStack->getSession()->getFlashBag()->set('warning', 'Veuillez completer votre profil');
			return new RedirectResponse($this->router->generate('front_school_new'));
		} else {
			return $executionActionController();
		}
	}
}
