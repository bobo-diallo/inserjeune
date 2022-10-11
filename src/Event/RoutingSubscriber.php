<?php

namespace App\Event;

use App\Entity\Company;
use App\Entity\PersonDegree;
use App\Entity\School;
use App\Repository\CompanyRepository;
use App\Repository\PersonDegreeRepository;
use App\Repository\SchoolRepository;
use App\Repository\UserRepository;
use App\Tools\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class RoutingSubscriber implements EventSubscriberInterface {
	private TokenStorageInterface $tokenStorage;
	private AuthorizationCheckerInterface $authorizationChecker;
	private EntityManagerInterface $manager;
	private RequestStack $requestStack;
	private RouterInterface $router;
	private PersonDegreeRepository $personDegreeRepository;
	private SchoolRepository $schoolRepository;
	private CompanyRepository $companyRepository;
	private UserRepository $userRepository;

	public function __construct(
		TokenStorageInterface  $tokenStorage,
		AuthorizationCheckerInterface   $authorizationChecker,
		EntityManagerInterface $manager,
		RequestStack           $requestStack,
		RouterInterface        $router,
		PersonDegreeRepository $personDegreeRepository,
		SchoolRepository $schoolRepository,
		CompanyRepository $companyRepository,
		UserRepository $userRepository
	) {
		$this->tokenStorage = $tokenStorage;
		$this->authorizationChecker = $authorizationChecker;
		$this->manager = $manager;
		$this->router = $router;
		$this->requestStack = $requestStack;
		$this->personDegreeRepository = $personDegreeRepository;
		$this->schoolRepository = $schoolRepository;
		$this->companyRepository = $companyRepository;
		$this->userRepository = $userRepository;
	}

	public function onKernelLoginRequest(RequestEvent $event) {
		$route = $event->getRequest()->attributes->get('_route');
		$request = $event->getRequest()->request;

		if ($route === 'login' && $event->getRequest()->getMethod() === 'POST') {
			$phone = $request->get('_username');
			$user = $this->userRepository->findOneBy(['phone' => $phone]);

			if ($user) {
				$username = $user->getUsername();
			} else {
				$username = ($this->userRepository->findOneBy(['username' => $phone])) ? ' ' : $phone;
			}

			$request->set('_username', $username);
		}
	}

	public function onKernelCompanyRequest(RequestEvent $event) {
		if ($this->authorizationChecker->isGranted(Utils::COMPANY)) {
			$route = $this->_getGoodRoute($event);
			$company = $this->_getCompany();

			if ($route === 'logout') {
				echo 'company_logout ' . $route;
				die();
			}

			// Empêcher un diplomé de faire autre autre chose tant qu'il n'a pas créer son profil
			if ($route != 'front_company_new') {
				$company = $this->_getCompany();
				// Forcer le diplomé à completer son profil

				if (!$company && !in_array($route, [
						'rgpd_informations',
						'check_logout_company'
					])) {
					if (stristr($route, 'user_delete') === false && stristr($route, 'check_logout') === false) {
						$this->requestStack->getSession()->getFlashBag()->set('warning', 'Veuillez completer votre profil');
					}
					$this->_redirect($event, 'front_company_new');
				} else {
					// Pour les redirection route = null
					$route = $this->_getGoodRoute($event);
					if (!str_contains($route, 'front_company') &&
						!str_contains($route, 'user_delete') &&
						!str_contains($route, 'dashboard_index') &&
						!str_contains($route, 'rgpd_informations') &&
						!str_contains($route, 'check_logout') &&
						!str_contains($route, 'geolocation')) {
						$this->_redirect($event, 'front_company_show');
					}
				}
			} else if ($company) $this->_redirect($event, 'front_company_show');
		}
	}

	public function onKernelPersonDegreeRequest(RequestEvent $event) {
		if ($this->authorizationChecker->isGranted(Utils::PERSON_DEGREE)) {

			// Empêcher un diplomé de faire autre autre chose tant qu'il n'a pas créer son profil
			$route = $this->_getGoodRoute($event);

			if ($route != 'front_persondegree_new') {
				$personDegree = $this->_getPersonDegree();
				// Forcer le diplomé à completer son profil
				if (!$personDegree && !in_array($route, [
						'rgpd_informations',
						'front_persondegree_filters_school',
						'check_logout_persondegree'
					])) {
					if (stristr($route, 'user_delete') === false && stristr($route, 'check_logout') === false) {
						$this->requestStack->getSession()->getFlashBag()->set('warning', 'Veuillez completer votre profil');
					}
					$this->_redirect($event, 'front_persondegree_new');
				} else {
					// Pour les redirection route = null
					$route = $this->_getGoodRoute($event);
					//  echo "route=" . $route;
					if (!str_contains($route, 'front_persondegree') &&
						!str_contains($route, 'user_delete') &&
						!str_contains($route, 'rgpd_informations') &&
						!str_contains($route, 'updateAjax') &&
						!str_contains($route, 'filters') &&
						!str_contains($route, 'geolocation') &&
						!str_contains($route, 'check_logout') &&
						!str_contains($route, 'jobOffer')) {
						$this->_redirect($event, 'front_persondegree_show');
					}
				}
			} else (!$this->_getPersonDegree()) ?: $this->_redirect($event, 'front_persondegree_show');
		}
	}

	public function onKernelSchoolRequest(RequestEvent $event) {
		if ($this->authorizationChecker->isGranted(Utils::SCHOOL)) {
			$route = $this->_getGoodRoute($event);
			$school = $this->_getSchool();

			// Empêcher un diplomé de faire autre autre chose tant qu'il n'a pas créer son profil
			if ($route != 'front_school_new') {
				// Forcer le diplomé à completer son profil
				if (!$school && !in_array($route, [
						'rgpd_informations',
						'person_degree_update_api',
						'api',
						'check_logout_school'
					])) {
					if (stristr($route, 'user_delete') === false && stristr($route, 'check_logout') === false) {
						$this->requestStack->getSession()->getFlashBag()->set('warning', 'Veuillez completer votre profil');
					}
					$this->_redirect($event, 'front_school_new');
				} else {
					// Pour les redirection route = null
					$route = $this->_getGoodRoute($event);

					if (!str_contains($route, 'front_school') &&
						!str_contains($route, 'user_delete') &&
						!str_contains($route, 'rgpd_informations') &&
						!str_contains($route, 'dashboard_index') &&
						!str_contains($route, 'dashboard_') &&
						!str_contains($route, 'checkPersonDegree') &&
						!str_contains($route, 'check_logout') &&
						!str_contains($route, 'client_data_update') &&
						!str_contains($route, 'server_data_update') &&
						!str_contains($route, 'client_check_data_to_update') &&
						!str_contains($route, 'server_check_data_to_update') &&
						!str_contains($route, 'server_person_degree_update') &&
						!str_contains($route, 'server_company_update') &&
						!str_contains($route, 'clientUpdate') &&
						!str_contains($route, 'client_school_update') &&
						!str_contains($route, 'geolocation') &&
						!str_contains($route, 'jobOffer')) {
						$this->_redirect($event, 'front_school_show');
					}
				}
			} else if ($school) {
				$this->_redirect($event, 'front_school_show');
			}
		}
	}

	public function _redirect(RequestEvent $event, $route) {
		$event->setResponse(new RedirectResponse($this->router->generate($route)));
	}

	private function _getSchool(): ?School {
		$user = $this->tokenStorage->getToken()->getUser();
		return $this->schoolRepository->findOneBy(['user' => $user]);
	}

	private function _getCompany(): ?Company {
		$user = $this->tokenStorage->getToken()->getUser();
		return $this->companyRepository->findOneBy(['user' => $user]);
	}

	private function _getPersonDegree(): ?PersonDegree {
		$user = $this->tokenStorage->getToken()->getUser();
		return $this->personDegreeRepository->findOneBy(['user' => $user]);
	}

	private function _getGoodRoute(RequestEvent $event): array|string|null {
		$route = $event->getRequest()->attributes->get('_route');
		if (!$route) {
			$route = preg_replace('/\//', '_', $event->getRequest()->server->all()['REQUEST_URI']);
		}

		return preg_replace('/^_/', '', $route);
	}

	public static function getSubscribedEvents(): array {
		return [
			KernelEvents::REQUEST => [
				['onKernelLoginRequest', 10],
				['onKernelCompanyRequest', -10],
				['onKernelPersonDegreeRequest', -10],
				['onKernelSchoolRequest', -10]
			]
		];
	}
}
