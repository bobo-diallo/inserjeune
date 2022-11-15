<?php

namespace App\Entity;

class ChangePasswordDTO {
	private ?string $plainPassword = null;

	protected string $phone;

	public function getPlainPassword(): ?string {
		return $this->plainPassword;
	}

	/**
	 * @param string $plainPassword
	 */
	public function setPlainPassword(string $plainPassword): void {
		$this->plainPassword = $plainPassword;
	}

	public function getPhone(): string {
		return $this->phone;
	}

	public function setPhone(string $phone): void {
		$this->phone = $phone;
	}


}
