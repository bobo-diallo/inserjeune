<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

final class AppClient {
	private KernelBrowser $_client;

	private ContainerInterface $_container;

	private Crawler $_crawler;

	public function __construct(KernelBrowser $client) {
		$this->_client = $client;
		$this->_container = $client->getContainer();
	}

	public function getService(string $serviceId): ?object {
		return $this->_container->get($serviceId);
	}

	public function getBrowser(): KernelBrowser {
		return $this->_client;
	}

	public function getContainer(): ContainerInterface {
		return $this->_container;
	}

	public function crawler(): Crawler {
		return $this->_crawler;
	}

	public function startSession(bool $transactional): self {
		if ($transactional) {
			$entityManager = $this->_container->get('doctrine.orm.default_entity_manager');
			$entityManager->beginTransaction();
		}

		return $this;
	}

	public function loginUser(UserInterface $user): void {
		$this->_client->loginUser($user);
	}

	public function getCurrentLoggedUser(): UserInterface {
		$security = $this->_container->get('security.token_storage');

		return $security->getToken()->getUser();
	}

	public function sendRequest(string $method, string $url, $parameters = []): Response {
		$this->_crawler = $this->_client->request($method, $url, $parameters);

		return $this->_client->getResponse();
	}

}
