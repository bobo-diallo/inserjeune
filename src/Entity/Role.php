<?php

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "role")]
#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role {

	const ROLE_DIPLOME = 'ROLE_DIPLOME';
	const ROLE_ENTREPRISE = 'ROLE_ENTREPRISE';
	const ROLE_ADMIN = 'ROLE_ADMIN';

	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private ?int $id;

	#[ORM\Column(name: 'role', type: 'string', unique: true, length: 100)]
	private ?string $role;

	/**
	 * Role constructor.
	 * @param string|null $role
	 */
	public function __construct(string $role = null) {
		$this->role = $role;
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getRole(): ?string {
		return $this->role;
	}

	public function setRole(string $role) {
		$this->role = strtoupper($role);

		return $this;
	}

	public function __toString() {
		return $this->role;
	}
}