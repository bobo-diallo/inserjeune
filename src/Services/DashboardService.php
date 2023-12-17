<?php

namespace App\Services;

use App\Entity\Role;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Entity\User;
use Symfony\Contracts\Translation\TranslatorInterface;

class DashboardService {
	private TokenStorageInterface $tokenStorage;

	private RequestStack $requestStack;
	private RouterInterface $router;
    private TranslatorInterface $translator;

	public function __construct(
		TokenStorageInterface $tokenStorage,
		RequestStack $requestStack,
		RouterInterface $router,
        TranslatorInterface $translator
	) {
		$this->tokenStorage = $tokenStorage;
		$this->requestStack = $requestStack;
		$this->router = $router;
        $this->translator = $translator;
	}

	public function checkAccountBefore(callable $executionActionController): mixed {
		/** @var User $user */
		$user = $this->tokenStorage->getToken()->getUser();
		if ($user->getPersonDegree() || $user->hasRole(Role::ROLE_DIPLOME)) {
			return new RedirectResponse($this->router->generate('front_persondegree_show'));
		}
		else if (!$user->getCompany() && $user->hasRole(Role::ROLE_ENTREPRISE)) {
			$this->requestStack->getSession()->getFlashBag()->set('warning', $this->translator->trans('flashbag.please_complete_your_profile'));
			return new RedirectResponse($this->router->generate('front_company_new'));
		}
		else if (!$user->getSchool() && $user->hasRole(Role::ROLE_ETABLISSEMENT)) {
			$this->requestStack->getSession()->getFlashBag()->set('warning', $this->translator->trans('flashbag.please_complete_your_profile'));
			return new RedirectResponse($this->router->generate('front_school_new'));
		} else {
			return $executionActionController();
		}
	}
}
