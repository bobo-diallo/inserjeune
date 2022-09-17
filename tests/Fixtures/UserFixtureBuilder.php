<?php

namespace App\Tests\Fixtures;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class UserFixtureBuilder {
	private ContainerInterface $_container;
	private UserRepository $_users;
	private EntityManagerInterface $_entityManager;

	public function __construct(ContainerInterface $container) {
		$this->_container = $container;
		$this->_entityManager = $container->get('doctrine.orm.default_entity_manager');
		$this->_users = $container->get(UserRepository::class);
	}

	public function createUser() {

	}

}
