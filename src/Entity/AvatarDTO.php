<?php

namespace App\Entity;

class AvatarDTO {

	protected string $imageName;


	public function getImageName(): string {
		return $this->imageName;
	}

	public function setImageName(string $imageName): void {
		$this->imageName = $imageName;
	}


}
