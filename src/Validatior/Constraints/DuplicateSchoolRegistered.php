<?php declare(strict_types=1);

namespace App\Validatior\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class DuplicateSchoolRegistered extends Constraint {

	public string $message = 'Registration should be unique. Another please choose another name';
	// If the constraint has configuration options, define them as public properties
	public string $mode = 'strict';
}
