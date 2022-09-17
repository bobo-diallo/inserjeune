<?php

namespace App\CQRS\Exception;

use App\CQRS\CommandHandler;

final class CommandBusException extends \Exception {

	public static function multipleHandlerForCommand($commandClass, CommandHandler $handler): self {
		return new self(
			sprintf(
				"The command '%s' is already handled by the handler '%s', no command"
				. " can be handled by multiple handlers.",
				$commandClass,
				get_class($handler)
			)
		);
	}


	public static function noHandlerFoundForCommand($commandClass): self {
		return new self("No command handler was found for the command '{$commandClass}'.");
	}

}
