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
	const ROLE_ETABLISSEMENT = 'ROLE_ETABLISSEMENT';
	const ROLE_PRINCIPAL = 'ROLE_PRINCIPAL';
	const ROLE_ADMIN_PAYS = 'ROLE_ADMIN_PAYS';
	const ROLE_ADMIN_REGIONS = 'ROLE_ADMIN_REGIONS';
	const ROLE_ADMIN_VILLES = 'ROLE_ADMIN_VILLES';
	const ROLE_DIRECTOR = 'ROLE_DIRECTEUR';

	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private ?int $id = null;

    #[ORM\Column(name: 'role', type: 'string', length: 100, unique: true)]
    private ?string $role;

	#[ORM\Column(name: 'pseudo', type: 'string', length: 100, nullable: true)]
	private ?string $pseudo;

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

    public function getPseudo(): ?string
    {
        return strlen($this->pseudo)>0 ? $this->pseudo : $this->role;
    }

    /**
     * @param string|null $pseudo
     * @return Role
     */
    public function setPseudo(?string $pseudo): Role
    {
        $this->pseudo = $pseudo;
        return $this;
    }

	public function __toString() {
		// return $this->role;
        return strlen($this->pseudo)>0 ? $this->pseudo : $this->role;
	}
}
