<?php declare(strict_types=1);

namespace App\Model;

final class CompanyReceiverNotification {
	private ?int $id;

	private ?string $name;

	private ?string $email;

	private ?string $phone;

	private ?string $temporaryPassword;

	public function __construct(
		?int $id,
		?string $name,
		?string $email,
		?string $phone,
		?string $temporaryPassword
	) {
		$this->id = $id;
		$this->email = $email;
		$this->phone = $phone;
		$this->temporaryPassword = $temporaryPassword;
		$this->name = $name;
	}

	public function id(): ?int {
		return $this->id;
	}

	public function name(): ?string {
		return $this->name;
	}

	public function email(): ?string {
		return $this->email;
	}

	public function phone(): ?string {
		return $this->phone;
	}

	public function temporaryPassword(): ?string {
		return $this->temporaryPassword;
	}

}
