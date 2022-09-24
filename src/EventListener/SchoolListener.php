<?php

namespace App\EventListener;

use App\Entity\School;
use App\Repository\SchoolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use App\Tools\Utils;

/**
 * Permet de controller toutes les actions des etablissements
 *
 * Class CompanyListener
 * @package App\EvenListener
 */
class SchoolListener {
	private TokenStorageInterface $tokenStorage;
	private AuthorizationChecker $authorizationChecker;
	private EntityManagerInterface $manager;
	private RouterInterface $router;
	private RequestStack $requestStack;
	private SchoolRepository $schoolRepository;

	public function __construct(
		TokenStorageInterface  $tokenStorage,
		AuthorizationChecker   $authorizationChecker,
		EntityManagerInterface $manager,
		RequestStack           $requestStack,
		RouterInterface        $router,
		SchoolRepository $schoolRepository
	) {
		$this->tokenStorage = $tokenStorage;
		$this->authorizationChecker = $authorizationChecker;
		$this->manager = $manager;
		$this->router = $router;
		$this->requestStack = $requestStack;
		$this->schoolRepository = $schoolRepository;
	}

	public function onKernelRequest(RequestEvent $event): void {
		// if ($this->authorizationChecker->isGranted(Utils::SCHOOL)) {
		// 	$route = $event->getRequest()->attributes->get('_route');
		//
		// 	$route = $this->getGoodRoute($event, $route);
		// 	$school = $this->getSchool();
		// 	// Empêcher un diplomé de faire autre autre chose tant qu'il n'a pas créer son profil
		// 	if ($route != 'front_school_new') {
		// 		// Forcer le diplomé à completer son profil
		// 		if (!$school && !in_array($route, [
		// 				'rgpd_informations',
		// 				'person_degree_update_api',
		// 				'api',
		// 				'check_logout_school'
		// 			])) {
		// 			if (stristr($route, 'user_delete') === false && stristr($route, 'check_logout') === false) {
		// 				$this->requestStack->getSession()->getFlashBag()->set('warning', 'Veuillez completer votre profil');
		// 			}
		// 			$this->redirect($event, 'front_school_new');
		// 		} else {
		// 			// Pour les redirection route = null
		// 			$route = $this->getGoodRoute($event, $route);
		//
		// 			if (!str_contains($route, 'front_school') &&
		// 				!str_contains('/user_delete/', $route) &&
		// 				!str_contains('/rgpd_informations/', $route) &&
		// 				!str_contains('/checkPersonDegree/', $route) &&
		// 				!str_contains('/check_logout/', $route) &&
		// 				!str_contains('/client_data_update/', $route) &&
		// 				!str_contains('/server_data_update/', $route) &&
		// 				!str_contains('/client_check_data_to_update/', $route) &&
		// 				!str_contains('/server_check_data_to_update/', $route) &&
		// 				!str_contains('/server_person_degree_update/', $route) &&
		// 				!str_contains('/server_company_update/', $route) &&
		// 				!str_contains('/clientUpdate/', $route) &&
		// 				!str_contains('/client_school_update/', $route) &&
		// 				!str_contains('/geolocation/', $route) &&
		// 				!str_contains('/jobOffer/', $route)) {
		// 				$this->redirect($event, 'front_school_show');
		// 			}
		// 		}
		// 	} else if ($school) {
		// 		$this->redirect($event, 'front_school_show');
		// 	}
		// }
	}

	private function getGoodRoute(RequestEvent $event, $route): array|string|null {
		if (!$route) $route = preg_replace('/\//', '_', $event->getRequest()->server->all()['REQUEST_URI']);
		return preg_replace('/^_/', '', $route);
	}

	private function getSchool(): ?School {
		$user = $this->tokenStorage->getToken()->getUser();
		return $this->schoolRepository->findOneBy(['user' => $user]);
	}

	public function redirect(RequestEvent $event, $route): RedirectResponse {
		return $event->setResponse(new RedirectResponse($this->router->generate($route)));
	}

}
