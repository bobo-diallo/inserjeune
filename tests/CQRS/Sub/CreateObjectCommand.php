<?php declare(strict_types=1);

namespace App\Tests\CQRS\Sub;

use App\CQRS\Command;

final class CreateObjectCommand implements Command {
	private string $name;
}
