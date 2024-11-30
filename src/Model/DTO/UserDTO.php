<?php declare(strict_types=1);

namespace App\Model\DTO;

class UserDTO
{
	public function __construct(
		public int $id,
		public string $phone,
		public string $username,
		public string $email,
		public array $roles,
		public ?string $countryName,
		public ?string $regionName
	) {}
}
