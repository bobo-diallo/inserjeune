<?php declare(strict_types=1);

namespace App\Model;

final class UserReadOnly {
	private int $id;
	private ?string $username;
	private ?string $email;
	private ?string $phone;
	private ?string $country;
	private ?string $region;
	private ?string $roles;
	private ?string $pseudos;
    private ?string $adminRegions;
    private ?string $adminCities;

	public function __construct(
		int     $id,
		?string $username,
		?string $email,
		?string $phone,
		?string $country,
		?string $region,
		?string $roles,
		?string $pseudos,
		?string $adminRegions,
		?string $adminCities
	) {
		$this->id = $id;
		$this->username = $username;
		$this->email = $email;
		$this->phone = $phone;
		$this->country = $country;
		$this->region = $region;
		$this->roles = $roles;
		$this->pseudos = $pseudos;
		$this->adminRegions = $adminRegions;
		$this->adminCities = $adminCities;
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

    public function region(): ?string {
        return $this->region;
    }

    public function roles(): ?string {
        return $this->roles;
    }
    public function role(): ?string {
        if($this->roles) {
            $roles = explode(",", $this->roles);
            if ($this->pseudos) {
                $pseudos = explode(",", $this->pseudos);
                return count($pseudos) > 0 ? $pseudos[0] : "";
            }
            return count($roles) > 0 ? $roles[0] : "";
        }
        return null;
    }

    public function adminRegions(): ?string {
        return $this->adminRegions;
    }

	public function adminCities(): ?string {
		return $this->adminCities;
	}

}
