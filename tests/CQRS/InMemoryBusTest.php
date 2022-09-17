<?php declare(strict_types=1);

namespace App\Tests\CQRS;

use App\CQRS\Command;
use App\CQRS\CommandHandler;
use App\CQRS\Context\NoSpecificContext;
use App\CQRS\Exception\CommandBusException;
use App\CQRS\Exception\CommandHandlerException;
use App\CQRS\InMemoryBus;
use App\Tests\CQRS\Sub\CreateObjectCommand;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class InMemoryBusTest extends TestCase {
	private CommandHandler|MockObject $_handler;
	private InMemoryBus $_commandBus;
	private Command $_command;

	protected function setUp(): void {
		$this->_handler = $this->createMock(CommandHandler::class);
		$this->_commandBus = new InMemoryBus();
		$this->_command = new CreateObjectCommand();
	}

	public function test_it_should_throw_exception_when_command_has_two_handlers(): void {
		$this->expectException(CommandBusException::class);
		$this->expectExceptionMessage(sprintf(
			"The command '%s' is already handled by the handler '%s', no command can be handled by multiple handlers.",
			CreateObjectCommand::class,
			get_class($this->_handler))
		);
		$this->_commandBus->registerHandler(CreateObjectCommand::class, $this->_handler);
		$this->_commandBus->registerHandler(CreateObjectCommand::class, $this->_handler);
	}

	public function test_it_should_throw_exception_when_no_handler_found_for_command(): void {
		$this->expectException(CommandBusException::class);
		$this->expectExceptionMessage("No command handler was found for the command '" . get_class($this->_command) . "'.");
		$this->_commandBus->handleCommand($this->_command, new NoSpecificContext());
	}

	public function test_it_should_exception_when_the_handler_do_not_support_context(): void {
		$this->_handler
			->expects($this->once())
			->method('supportsContext')
			->willReturn(false);

		$this->expectException(CommandHandlerException::class);
		$this->expectExceptionMessage(sprintf(
			"The command handler '%s' do not supports the context '%s'.",
			get_class($this->_handler),
			NoSpecificContext::class
		));
		$this->_commandBus->registerHandler(CreateObjectCommand::class, $this->_handler);
		$this->_commandBus->handleCommand(new CreateObjectCommand(), new NoSpecificContext());
	}

	public function test_it_execute_registered_handler_when_command_received(): void {
		$this->_handler
			->expects(self::once())
			->method('handle')
			->with($this->_command);
		$this->_handler
			->expects(self::once())
			->method('supportsContext')
			->with(self::isInstanceOf(NoSpecificContext::class))
			->willReturn(true);

		$this->_commandBus->registerHandler(CreateObjectCommand::class, $this->_handler);
		$this->_commandBus->handleCommand($this->_command, new NoSpecificContext());
	}
}
