<?php

namespace App\DependencyInjection\Complier;

use App\Assertion\AppAssertion;
use App\CQRS\CommandBus;
use App\DependencyInjection\ServiceTagStore;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class CommandHandlerCompilerPass implements CompilerPassInterface {

	public function process(ContainerBuilder $container) {
		$commandBusDefinition = $container->getDefinition(CommandBus::SERVICE_ID . '.registry');
		$servicesIds = $container->findTaggedServiceIds(ServiceTagStore::COMMAND_HANDLER);

		foreach ($servicesIds as $commandHandlerId => $tags) {
			foreach ($tags as $tag) {
				if (! array_key_exists('command_class', $tag)) {
					$tag['command_class'] = $this->getMessageClass(
						$container->getDefinition($commandHandlerId)->getClass()
					);
				}

				$commandBusDefinition->addMethodCall(
					'registerHandler',
					[
						$tag['command_class'],
						new Reference($commandHandlerId)
					]
				);
			}
		}
	}

	private function getMessageClass(string $handlerClass): string {
		AppAssertion::endsWith($handlerClass, 'Handler');

		return \substr($handlerClass, 0, strripos($handlerClass, 'Handler'));
	}
}
