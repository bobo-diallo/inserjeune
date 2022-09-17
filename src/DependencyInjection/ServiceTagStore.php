<?php

namespace App\DependencyInjection;

final class ServiceTagStore {
	/**
	 * A command handler is performing one (and only one) operation. This avoids having a good class (Service) with tons of dependencies.
	 *
	 * @see \App\CQRS\CommandHandler
	 * @see \App\CQRS\Command
	 *
	 * Require attribute: "command_class": The FQCN of the Command that this handler supports
	 */
	const COMMAND_HANDLER = 'app_cqrs.command_handler';
}
