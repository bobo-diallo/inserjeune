<?php

namespace App\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class LocaleSubscriber implements EventSubscriberInterface {

	private string $defaultLocale;

	public function __construct(string $defaultLocale = 'fr') {
		$this->defaultLocale = $defaultLocale;
	}

	public function onKernelRequest(RequestEvent $event) {
		$request = $event->getRequest();
		if (!$request->hasPreviousSession()) {
			return;
		}

		// On vérifie si la langue est passée en paramètre de l'URL
		if ($locale = $request->query->get('_locale')) {
			$request->setLocale($locale);
		} else {
			$locale = $request->getSession()->get('_locale', $this->defaultLocale);
			// Sinon on utilise celle de la session
			$request->setLocale($locale);
			$request->getSession()->set('_locale', $locale);
		}
	}


	public static function getSubscribedEvents(): array {
		return [
			KernelEvents::REQUEST => [['onKernelRequest', 20]
			]
		];
	}
}
