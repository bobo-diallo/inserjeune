<?php

namespace App\CQRS\Context;

use App\CQRS\ExecutionContext;

final class NoSpecificContext implements ExecutionContext {

	public function notifyEnd(mixed $subject) {
	}
}
