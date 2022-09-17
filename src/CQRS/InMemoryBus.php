<?php declare(strict_types=1);

namespace App\CQRS;

use App\CQRS\Exception\CommandBusException;
use App\CQRS\Exception\CommandHandlerException;

final class InMemoryBus implements CommandBus {
	/**
	 * @var CommandHandler[]
	 */
	private array $_handlers = [];

	/**
	 * @param CommandHandler[] $handlers
	 * @throws CommandBusException
	 */
	public function __construct(array $handlers = []) {
		$this->_handlers = array_map(
			function (string $command) use ($handlers) {
				$this->registerHandler($command, $handlers[$command]);
			},
			array_keys($handlers)
		);
	}


	/**
	 * @throws CommandBusException
	 * @throws CommandHandlerException
	 */
	public function handleCommand(Command $command, ExecutionContext $context): void {
		$handler = $this->_getHandler(get_class($command));
		if (! $handler->supportsContext($context)) {
			throw CommandHandlerException::handlerDoNotSupportsContext($handler, $context);
		}

		$handler->handle($command, $context);
	}

	/**
	 * @throws CommandBusException
	 */
	public function registerHandler(string $commandClass, CommandHandler $handler): void {
		if ($this->_hasHandlerForCommand($commandClass)) {
			throw CommandBusException::multipleHandlerForCommand($commandClass, $this->_handlers[$commandClass]);
		}

		$this->_handlers[$commandClass] = $handler;
	}

	private function _hasHandlerForCommand(string $commandClass): bool {
		return isset($this->_handlers[$commandClass]);
	}

	/**
	 * @throws CommandBusException
	 */
	private function _getHandler(string $commandClass): CommandHandler {
		if (! $this->_hasHandlerForCommand($commandClass)) {
			throw CommandBusException::noHandlerFoundForCommand($commandClass);
		}

		return $this->_handlers[$commandClass];
	}
}
