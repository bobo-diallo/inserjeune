<?php

namespace App\DataFixtures;

use App\Entity\Region;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class RegionFixtures extends AbstractFixtures implements DependentFixtureInterface{

	public function load(ObjectManager $manager) {
		$regions = [
			[
				'name' => 'Dakar',
				'region' => CountryFixtures::SEN
			],
			[
				'name' => 'Thies',
				'region' => CountryFixtures::SEN
			],
			[
				'name' => 'Diourbel',
				'region' => CountryFixtures::SEN
			],
			[
				'name' => 'Ziguinchor',
				'region' => CountryFixtures::SEN
			],
			[
				'name' => 'Kolda',
				'region' => CountryFixtures::SEN
			],
			[
				'name' => 'Tambacounda',
				'region' => CountryFixtures::SEN
			],
			[
				'name' => 'Kaffrine',
				'region' => CountryFixtures::SEN
			],
			[
				'name' => 'Kaolack',
				'region' => CountryFixtures::SEN
			],
			[
				'name' => 'Kedougou',
				'region' => CountryFixtures::SEN
			],
			[
				'name' => 'Matam',
				'region' => CountryFixtures::SEN
			],
			[
				'name' => 'Podor',
				'region' => CountryFixtures::SEN
			],
			[
				'name' => 'Bamako',
				'region' => CountryFixtures::MAL
			],
			[
				'name' => 'Kayes',
				'region' => CountryFixtures::MAL
			],
			[
				'name' => 'Abidjan',
				'region' => CountryFixtures::CIV
			],
			[
				'name' => 'Conakry',
				'region' => CountryFixtures::GUI
			],
		];

		foreach ($regions as $item) {
			$region = Region::fromFixture($item['name']);
			$region->setCountry($this->getCountryReference($item['region']));

			$manager->persist($region);
			$manager->flush();

			$this->setRegionReference($region);

		}
	}

	public function getDependencies(): array {
		return [
			CountryFixtures::class,
		];
	}
}
