<?php declare(strict_types=1);

namespace App\Model;

final class UserReadOnly {
	private int $id;
	private ?string $username;
	private ?string $email;
	private ?string $phone;
	private ?string $country;
	private ?string $roles;

	public function __construct(
		int     $id,
		?string $username,
		?string $email,
		?string $phone,
		?string $country,
		?string $roles
	) {
		$this->id = $id;
		$this->username = $username;
		$this->email = $email;
		$this->phone = $phone;
		$this->country = $country;
		$this->roles = $roles;
	}

	public function id(): int {
		return $this->id;
	}

	public function username(): ?string {
		return $this->username;
	}

	public function email(): ?string {
		return $this->email;
	}

	public function phone(): ?string {
		return $this->phone;
	}

	public function country(): ?string {
		return $this->country;
	}

	public function roles(): ?string {
		return $this->roles;
	}

}
