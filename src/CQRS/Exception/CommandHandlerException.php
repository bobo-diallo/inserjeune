<?php

namespace App\CQRS\Exception;

use App\CQRS\Command;
use App\CQRS\CommandHandler;
use App\CQRS\ExecutionContext;

final class CommandHandlerException extends \Exception {

	public static function handlerDoNotSupportsContext(CommandHandler $handler, ExecutionContext $context): self {
		return new self(
			sprintf(
				"The command handler '%s' do not supports the context '%s'.",
				get_class($handler),
				get_class($context)
			)
		);
	}

	/**
	 * @param CommandHandler $handler
	 * @param Command $command
	 *
	 * @return CommandHandlerException
	 */
	public static function handlerDoNotSupportsCommand(CommandHandler $handler, Command $command): self {
		return new self(
			sprintf(
				"The command handler '%s' do not supports the command '%s'.",
				get_class($handler),
				get_class($command)
			)
		);
	}
}
