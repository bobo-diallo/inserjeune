<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface {
	const ROLE_DEFAULT = 'ROLE_USER';

	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private ?int $id;

	#[ORM\Column(type: 'string', length: 180, unique: true)]
	private ?string $username;

	#[ORM\Column(type: 'json')]
	private array $roles = [];

	#[ORM\Column(type: 'string')]
	private string $password;

	#[ORM\Column(name: 'api_token', type: 'string', unique: true, nullable: true)]
	private string $apiToken;

	#[ORM\ManyToOne(targetEntity: Country::class)]
	private ?Country $country;

	#[ORM\Column(name: 'phone', type: 'string', nullable: false, unique: true)]
	#[Assert\NotBlank]
	protected string $phone;

	#[ORM\Column(name: 'email', type: 'string', nullable: false, unique: true)]
	#[Assert\NotBlank]
	protected string $email;

	#[ORM\Column(name: 'valid_code', type: 'string', nullable: true)]
	protected string $validCode;

	#[ORM\OneToOne(targetEntity: PersonDegree::class, cascade: ['persist', 'remove'], mappedBy: 'user')]
	private ?PersonDegree $personDegree;

	#[ORM\OneToOne(targetEntity: Company::class, cascade: ['persist', 'remove'], mappedBy: 'user')]
	private ?Company $company;

	#[ORM\OneToOne(targetEntity: School::class, cascade: ['persist', 'remove'], mappedBy: 'user')]
	private ?School $school;

	#[ORM\ManyToMany(targetEntity: Role::class, cascade: ['persist'])]
	#[ORM\JoinTable(name: 'user_role')]
	#[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
	#[ORM\InverseJoinColumn(name: 'role_id', referencedColumnName: 'id')]
	protected Collection $profils;

	public function __construct() {
		$this->profils = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getUsername(): ?string {
		return $this->username;
	}

	public function setUsername(string $username): self {
		$this->username = $username;

		return $this;
	}

	/**
	 * A visual identifier that represents this user.
	 *
	 * @see UserInterface
	 */
	public function getUserIdentifier(): string {
		return (string)$this->username;
	}

	/**
	 * @see UserInterface
	 */
	public function getRoles(): array {
		$profiles = $this->profils->map(function (Role $role) {
			return $role->getRole();
		})->toArray();
		return array_merge($profiles, [self::ROLE_DEFAULT]);
	}

	public function removeRole(string $role): void {
		$role = $this->profils->filter(function (Role $roleItem) use ($role) {
			return $roleItem->getRole() == $role;
		})->filter();

		if ($role) {
			$this->profils->removeElement($role);
		}
	}

	public function setRoles(array $roles): self {
		$this->profils->clear();
		foreach ($roles as $roleName) {
			$this->addRole(new Role($roleName));
		}

		$this->roles = $roles;

		return $this;
	}

	public function addRole(Role $role) {
		if ($this->hasRole($role->getRole())) {
			$this->profils->add($role);
		}
	}

	public function getRole(string $role): ?string {
		foreach ($this->getRoles() as $roleItem) {
			if ($role == $roleItem) {
				return $roleItem;
			}
		}
		return null;
	}

	public function hasRole(string $role): bool {
		if ($this->getRole($role)) {
			return true;
		}
		return false;
	}

	/**
	 * @see PasswordAuthenticatedUserInterface
	 */
	public function getPassword(): string {
		return $this->password;
	}

	public function setPassword(string $password): self {
		$this->password = $password;

		return $this;
	}

	/**
	 * @see UserInterface
	 */
	public function eraseCredentials() {
		// If you store any temporary, sensitive data on the user, clear it here
		// $this->plainPassword = null;
	}

	public function addProfil(Role $profil): self {
		$this->profils->add($profil);

		return $this;
	}

	public function removeProfil(Role $profil): void {
		$this->profils->removeElement($profil);
	}

	public function getProfils(): ArrayCollection {
		return $this->profils;
	}


	public function getPersonDegree(): ?PersonDegree {
		return $this->personDegree;
	}

	public function getPhone(): string {
		return $this->phone;
	}

	public function setPhone(string $phone): void {
		$this->phone = $phone;
	}

	public function email(): string {
		return $this->email;
	}

	/**
	 * @param string $email
	 */
	public function setEmail(string $email): void {
		$this->email = $email;
	}

	public function getValidCode(): string {
		return $this->validCode;
	}

	public function setValidCode(string $validCode): void {
		$this->validCode = $validCode;
	}

	public function getApiToken(): string {
		return $this->apiToken;
	}

	public function setApiToken(string $apiToken): self {
		$this->apiToken = $apiToken;
		return $this;
	}

	public function getCountry(): ?Country {
		return $this->country;
	}

	public function setCountry(Country $country): void {
		$this->country = $country;
	}

	public function getSchool(): ?School {
		return $this->school;
	}

	public function setSchool(School $school): void {
		$this->school = $school;
	}

	public function getCompany(): ?CSchool {
		return $this->company;
	}

	public function setCompany(Company $company): self {
		$this->company = $company;
		return $this;
	}
}