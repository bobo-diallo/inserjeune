<?php declare(strict_types=1);

namespace App\Validatior\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueEmail extends Constraint {

	public string $message = 'Email is used, please choose another email';
	// If the constraint has configuration options, define them as public properties
	public string $mode = 'strict';
}
