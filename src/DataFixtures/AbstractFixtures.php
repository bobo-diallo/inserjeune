<?php

namespace App\DataFixtures;

use App\Entity\Country;
use App\Entity\Currency;
use App\Entity\Region;
use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;

abstract class AbstractFixtures extends Fixture
{
	final public function setCountryReference(Country $country): void {
		$this->_setReference(
			sprintf('country-%s', $country->getIsoCode()),
			$country
		);
	}

	final public function getCountryReference(string $isoCode): Country {
		return $this->_getReference(sprintf('country-%s', $isoCode));
	}

	final public function setRoleReference(Role $role): void {
		$this->_setReference(
			sprintf('role-%s', $role->getRole()),
			$role
		);
	}

	final public function getRoleReference(string $roleName): Role {
		return $this->_getReference(sprintf('role-%s', $roleName));
	}

	final public function setCurrencyReference(Currency $currency): void {
		$this->_setReference(
			sprintf('currency-%s', $currency->getIsoName()),
			$currency
		);
	}

	final public function getCurrencyReference(string $currencyIsoName): Currency {
		return $this->_getReference(sprintf('currency-%s', $currencyIsoName));
	}

	final public function setRegionReference(Region $region): void {
		$this->_setReference(
			sprintf('region-%s-%s', $region->getName(), $region->getCountry()->getIsoCode()),
			$region
		);
	}

	final public function getRegionReference(string $regionName, string $countryIsoCode): Region {
		return $this->_getReference(sprintf('region-%s-%s', $regionName, $countryIsoCode));
	}

	private function _setReference(string $name, $object): void {
		parent::addReference($name, $object);
	}

	private function _getReference(string $name): object {
		return parent::getReference($name);
	}
}
