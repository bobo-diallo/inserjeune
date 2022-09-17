<?php declare(strict_types=1);

namespace App\Tests;

use Symfony\Component\DependencyInjection\ContainerInterface;

final class AppFixtureBuilder {
	private ContainerInterface $_container;

	public function __construct(ContainerInterface $_container) {
		$this->_container = $_container;
	}

	public function user(): UserFixtureBuilder {
		return new UserFixtureBuilder($this->_container);
	}

}
