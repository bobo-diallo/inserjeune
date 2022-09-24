<?php

namespace App\EventListener;

use App\Entity\PersonDegree;
use App\Repository\PersonDegreeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use App\Tools\Utils;

/**
 * Permet de controller toutes les actions des diplômés
 *
 * Class PersonDegreeListener
 * @package App\EvenListener
 */
class PersonDegreeListener {
	private TokenStorageInterface $tokenStorage;
	private AuthorizationChecker $authorizationChecker;
	private EntityManagerInterface $manager;
	private RequestStack $requestStack;
	private RouterInterface $router;
	private PersonDegreeRepository $personDegreeRepository;

	public function __construct(
		TokenStorageInterface  $tokenStorage,
		AuthorizationChecker   $authorizationChecker,
		EntityManagerInterface $manager,
		RequestStack           $requestStack,
		RouterInterface        $router,
		PersonDegreeRepository $personDegreeRepository
	) {
		$this->tokenStorage = $tokenStorage;
		$this->authorizationChecker = $authorizationChecker;
		$this->manager = $manager;
		$this->router = $router;
		$this->requestStack = $requestStack;
		$this->personDegreeRepository = $personDegreeRepository;
	}

	public function onKernelRequest(RequestEvent $event): void {
		// if ($this->authorizationChecker->isGranted(Utils::PERSON_DEGREE)) {
		//
		// 	// Empêcher un diplomé de faire autre autre chose tant qu'il n'a pas créer son profil
		// 	$route = $event->getRequest()->attributes->get('_route');
		// 	$route = $this->getGoodRoute($event, $route);
		//
		// 	if ($route != 'front_persondegree_new') {
		// 		$personDegree = $this->getPersonDegree();
		// 		// Forcer le diplomé à completer son profil
		// 		if (!$personDegree && !in_array($route, [
		// 				'rgpd_informations',
		// 				'front_persondegree_filters_school',
		// 				'check_logout_persondegree'
		// 			])) {
		// 			if (stristr($route, 'user_delete') === false && stristr($route, 'check_logout') === false) {
		// 				$this->requestStack->getSession()->getFlashBag()->set('warning', 'Veuillez completer votre profil');
		// 			}
		// 			$this->redirect($event, 'front_persondegree_new');
		// 		} else {
		// 			// Pour les redirection route = null
		// 			$route = $this->getGoodRoute($event, $route);
		// 			//  echo "route=" . $route;
		// 			if (!str_contains($route, 'front_persondegree') &&
		// 				!str_contains($route, 'user_delete') &&
		// 				!str_contains($route, 'rgpd_informations') &&
		// 				!str_contains($route, 'filters') &&
		// 				!str_contains($route, 'geolocation') &&
		// 				!str_contains($route, 'check_logout') &&
		// 				!str_contains($route, 'jobOffer')) {
		// 				$this->redirect($event, 'front_persondegree_show');
		// 			}
		// 		}
		// 	} else (!$this->getPersonDegree()) ?: $this->redirect($event, 'front_persondegree_show');
		// }
	}

	private function getGoodRoute(RequestEvent $event, $route): array|string|null {
		if (!$route) $route = preg_replace('/\//', '_', $event->getRequest()->server->all()['REQUEST_URI']);
		return preg_replace('/^_/', '', $route);
	}

	private function getPersonDegree(): ?PersonDegree {
		$user = $this->tokenStorage->getToken()->getUser();
		return $this->personDegreeRepository->findOneBy(['user' => $user]);
	}

	/**
	 * @param PersonDegree $personDegree
	 * @return boolean
	 */
	private function checkSatisfaction(PersonDegree $personDegree) {
		return true;
	}

	public function redirect(RequestEvent $event, $route): RedirectResponse {
		return $event->setResponse(new RedirectResponse($this->router->generate($route)));
	}

}
