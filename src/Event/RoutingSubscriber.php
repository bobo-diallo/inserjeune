<?php

namespace App\Event;

use App\Repository\CompanyRepository;
use App\Repository\PersonDegreeRepository;
use App\Repository\SchoolRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
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

	public static function getSubscribedEvents(): array {
		return [
			KernelEvents::REQUEST => [
				['onKernelLoginRequest', 10],
			]
		];
	}
}
