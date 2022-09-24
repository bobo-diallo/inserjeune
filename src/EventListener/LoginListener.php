<?php

namespace App\EventListener;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class LoginListener {
	private EntityManagerInterface $manager;
	private RequestStack $requestStack;
	private UserRepository $userRepository;

	public function __construct(
		EntityManagerInterface $manager,
		RequestStack           $requestStack,
		UserRepository         $userRepository
	) {
		$this->manager = $manager;
		$this->requestStack = $requestStack;
		$this->userRepository = $userRepository;
	}

	public function onKernelRequest(RequestEvent $event): void {
		// $route = $event->getRequest()->attributes->get('_route');
		// $request = $event->getRequest()->request;
		//
		// if ($route === 'logout') {
		// 	$phone = $request->get('_username');
		// 	$user = $this->userRepository->findOneBy(['phone' => $phone]);
		//
		// 	if ($user) {
		// 		$username = $user->getUsername();
		// 	} else {
		// 		$username = ($this->userRepository->findOneBy(['username' => $phone])) ? ' ' : $phone;
		// 	}
		//
		// 	$request->set('_username', $username);
		// }
	}
}
