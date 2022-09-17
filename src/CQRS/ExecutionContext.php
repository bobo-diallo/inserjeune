<?php declare(strict_types=1);

namespace App\CQRS;

interface ExecutionContext {
	/**
	 * Notify the execution context when the handling of the subject has ended
	 *
	 * @param mixed $subject
	 */
	public function notifyEnd(mixed $subject);
}
