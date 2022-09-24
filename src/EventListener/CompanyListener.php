<?php

namespace App\EventListener;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use App\Tools\Utils;

/**
 * Permet de controller toutes les actions des diplômés
 *
 * Class CompanyListener
 * @package App\EvenListener
 */
class CompanyListener {
	private TokenStorageInterface $tokenStorage;
	private AuthorizationChecker $authorizationChecker;
	private EntityManagerInterface $manager;
	private RouterInterface $router;
	private RequestStack $requestStack;
	private CompanyRepository $companyRepository;

	public function __construct(
		TokenStorageInterface  $tokenStorage,
		AuthorizationChecker   $authorizationChecker,
		EntityManagerInterface $manager,
		RequestStack           $requestStack,
		RouterInterface        $router,
		CompanyRepository $companyRepository
	) {
		$this->tokenStorage = $tokenStorage;
		$this->authorizationChecker = $authorizationChecker;
		$this->manager = $manager;
		$this->router = $router;
		$this->requestStack = $requestStack;
		$this->companyRepository = $companyRepository;
	}

	public function onKernelRequest(RequestEvent $event): void {
		// if ($this->authorizationChecker->isGranted(Utils::COMPANY)) {
		// 	$route = $event->getRequest()->attributes->get('_route');
		// 	$route = $this->getGoodRoute($event, $route);
		// 	$company = $this->getCompany();
		//
		// 	if ($route === 'logout') {
		// 		echo 'company_logout ' . $route;
		// 		die();
		// 	}
		//
		// 	// Empêcher un diplomé de faire autre autre chose tant qu'il n'a pas créer son profil
		// 	if ($route != 'front_company_new') {
		// 		$company = $this->getCompany();
		// 		// Forcer le diplomé à completer son profil
		//
		// 		if (!$company && !in_array($route, [
		// 				'rgpd_informations',
		// 				'check_logout_company'
		// 			])) {
		// 			if (stristr($route, 'user_delete') === false && stristr($route, 'check_logout') === false) {
		// 				$this->requestStack->getSession()->getFlashBag()->set('warning', 'Veuillez completer votre profil');
		// 			}
		// 			$this->redirect($event, 'front_company_new');
		// 		} else {
		// 			// Pour les redirection route = null
		// 			$route = $this->getGoodRoute($event, $route);
		// 			if (!str_contains($route, 'front_company') &&
		// 				!str_contains($route, 'user_delete') &&
		// 				!str_contains($route, 'rgpd_informations') &&
		// 				!str_contains($route, 'check_logout') &&
		// 				!str_contains($route, 'geolocation')) {
		// 				$this->redirect($event, 'front_company_show');
		// 			}
		// 		}
		// 	} else if ($company) $this->redirect($event, 'front_company_show');
		// }
	}

	private function getGoodRoute(RequestEvent $event, $route): array {
		if (!$route) {
			$route = preg_replace('/\//', '_', $event->getRequest()->server->all()['REQUEST_URI']);
		}
		return preg_replace('/^_/', '', $route);
	}

	private function getCompany(): ?Company {
		$user = $this->tokenStorage->getToken()->getUser();
		return $this->companyRepository->findOneBy(['user' => $user]);
	}

	public function redirect(RequestEvent $event, $route): RedirectResponse {
		return $event->setResponse(new RedirectResponse($this->router->generate($route)));
	}

}
