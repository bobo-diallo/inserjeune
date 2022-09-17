<?php declare(strict_types=1);

namespace App\CQRS;

interface CommandBus {
	const SERVICE_ID = 'app_core.command_bus';

	public function handleCommand(Command $command, ExecutionContext $context): void;

	public function registerHandler(string $commandClass, CommandHandler $handler): void;
}
