<?php declare(strict_types=1);

namespace App\Model;

final class PersonDegreeReceiverNotification {
	private ?int $id;

	private ?string $firstname;

	private ?string $lastname;

	private ?string $schoolName;

	private ?string $email;

	private ?string $phone;

	private ?string $temporaryPassword;

	public function __construct(
		?int $id,
		?string $firstname,
		?string $lastname,
		?string $schoolName,
		?string $email,
		?string $phone,
		?string $temporaryPassword
	) {
		$this->id = $id;
		$this->firstname = $firstname;
		$this->lastname = $lastname;
		$this->schoolName = $schoolName;
		$this->email = $email;
		$this->phone = $phone;
		$this->temporaryPassword = $temporaryPassword;
	}

	public function id(): ?int {
		return $this->id;
	}

	public function firstname(): ?string {
		return $this->firstname;
	}

	public function lastname(): ?string {
		return $this->lastname;
	}

	public function schoolName(): ?string {
		return $this->schoolName;
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
