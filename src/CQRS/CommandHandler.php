<?php declare(strict_types=1);

namespace App\CQRS;

interface CommandHandler {
	/**
	 * @param Command $command
	 * @param ExecutionContext $context
	 * @return mixed
	 */
	public function handle(Command $command, ExecutionContext $context): mixed;

	/**
	 * @param ExecutionContext $context
	 * @return bool
	 */
	public function supportsContext(ExecutionContext $context): bool;
}
