<?php

namespace App\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

final class AppCoreExtension implements ExtensionInterface {

	public function load(array $configs, ContainerBuilder $container) {
		throw new \RuntimeException(__METHOD__ . ' not implemented yet');
	}

	public function getNamespace() {
		throw new \RuntimeException(__METHOD__ . ' not implemented yet');
	}

	public function getXsdValidationBasePath() {
		throw new \RuntimeException(__METHOD__ . ' not implemented yet');
	}

	public function getAlias() {
		throw new \RuntimeException(__METHOD__ . ' not implemented yet');
	}
}
