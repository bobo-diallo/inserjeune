<?php declare(strict_types=1);

namespace App\Event\Listener;

use App\Event\SessionTimeoutEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SessionTimeoutListener implements EventSubscriberInterface
{
	// private SessionInterface $session;
	private TokenStorageInterface $tokenStorage;
	private RequestStack $requestStack;

	public function __construct(RequestStack $requestStack, TokenStorageInterface $tokenStorage)
	{
		$this->tokenStorage = $tokenStorage;
		$this->requestStack = $requestStack;
	}

	public function onSessionTimeout(SessionTimeoutEvent $event): void
	{
		$token = $this->tokenStorage->getToken();
		if ($token) {
			$this->tokenStorage->setToken(null);
			$this->requestStack->getSession()->invalidate();
		}
	}

	public static function getSubscribedEvents(): array
	{
		return [
			SessionTimeoutEvent::class => 'onSessionTimeout',
		];
	}
}
