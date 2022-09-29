<?php

namespace App\DataFixtures;

use App\Entity\Country;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class CountryFixtures extends AbstractFixtures implements DependentFixtureInterface {
	public const SEN = 'SEN';
	public const TGO = 'TGO';
	public const MAL = 'MAL';
	public const CIV = 'CIV';
	public const MAD = 'MAD';
	public const BEN = 'BEN';
	public const GAB = 'GAB';
	public const RDC = 'RDC';
	public const TCH = 'TCH';
	public const NIG = 'NIG';
	public const HTI = 'HTI';
	public const MAR = 'MAR';
	public const TUN = 'TUN';
	public const CAB = 'CAB';
	public const GUI = 'GUI';
	public const RCA = 'RCA';
	public const BRK = 'BRK';
	public const RWA = 'RWA';
	public const BUR = 'BUR';
	public const MAU = 'MAU';
	public const BRA = 'BRA';
	public const CMR = 'CMR';
	public const KM = 'KM';
	public const KEN = 'KEN';
	public const DJI = 'DJI';

	public function load(ObjectManager $manager) {
		$countries = [
			[
				'name' => 'Sénégal',
				'iso_code' => 'SEN',
				'valid' => true,
				'phone_code' => '221',
				'phone_digit' => '9',
				'currency' => CurrencyFixtures::CFA
			],
			[
				'name' => 'Togo',
				'iso_code' => 'TGO',
				'valid' => true,
				'phone_code' => '228',
				'phone_digit' => '8',
				'currency' => CurrencyFixtures::CFA
			],
			[
				'name' => 'Mali',
				'iso_code' => 'MAL',
				'valid' => true,
				'phone_code' => '223',
				'phone_digit' => '8',
				'currency' => CurrencyFixtures::CFA
			],
			[
				'name' => 'Côte d\'Ivoire',
				'iso_code' => 'CIV',
				'valid' => false,
				'phone_code' => '225',
				'phone_digit' => '8',
				'currency' => CurrencyFixtures::CFA
			],
			[
				'name' => 'Madagascar',
				'iso_code' => 'MAD',
				'valid' => true,
				'phone_code' => '261',
				'phone_digit' => '9',
				'currency' => CurrencyFixtures::MGA
			],
			[
				'name' => 'Bénin',
				'iso_code' => 'BEN',
				'valid' => false,
				'phone_code' => '229',
				'phone_digit' => '0',
				'currency' => CurrencyFixtures::CFA
			],
			[
				'name' => 'Gabon',
				'iso_code' => 'GAB',
				'valid' => true,
				'phone_code' => '241',
				'phone_digit' => '8',
				'currency' => CurrencyFixtures::CFA
			],
			[
				'name' => 'RDC',
				'iso_code' => 'RDC',
				'valid' => true,
				'phone_code' => '243',
				'phone_digit' => '9',
				'currency' => CurrencyFixtures::CDF
			],
			[
				'name' => 'Tchad',
				'iso_code' => 'TCH',
				'valid' => false,
				'phone_code' => '235',
				'phone_digit' => '0',
				'currency' => CurrencyFixtures::CFA
			],
			[
				'name' => 'Niger',
				'iso_code' => 'NIG',
				'valid' => true,
				'phone_code' => '234',
				'phone_digit' => '8',
				'currency' => CurrencyFixtures::CFA
			],
			[
				'name' => 'Haïti',
				'iso_code' => 'HTI',
				'valid' => true,
				'phone_code' => '509',
				'phone_digit' => '8',
				'currency' => CurrencyFixtures::HTG
			],
			[
				'name' => 'Maroc',
				'iso_code' => 'MAR',
				'valid' => false,
				'phone_code' => '212',
				'phone_digit' => '0',
				'currency' => CurrencyFixtures::MAD
			],
			[
				'name' => 'Tunisie',
				'iso_code' => 'TUN',
				'valid' => false,
				'phone_code' => '216',
				'phone_digit' => '0',
				'currency' => CurrencyFixtures::TND
			],
			[
				'name' => 'Cap Vert',
				'iso_code' => 'CAB',
				'valid' => false,
				'phone_code' => '238',
				'phone_digit' => '0',
				'currency' => CurrencyFixtures::CVE
			],
			[
				'name' => 'Guinée',
				'iso_code' => 'GUI',
				'valid' => true,
				'phone_code' => '224',
				'phone_digit' => '9',
				'currency' => CurrencyFixtures::GNF
			],
			[
				'name' => 'République Centrafricaine',
				'iso_code' => 'RCA',
				'valid' => false,
				'phone_code' => '236',
				'phone_digit' => '0',
				'currency' => CurrencyFixtures::CFA
			],
			[
				'name' => 'Burkina Faso',
				'iso_code' => 'BRK',
				'valid' => false,
				'phone_code' => '226',
				'phone_digit' => '0',
				'currency' => CurrencyFixtures::CFA
			],
			[
				'name' => 'Rwanda',
				'iso_code' => 'RWA',
				'valid' => false,
				'phone_code' => '250',
				'phone_digit' => '0',
				'currency' => CurrencyFixtures::RWF
			],
			[
				'name' => 'Burundi',
				'iso_code' => 'BUR',
				'valid' => true,
				'phone_code' => '257',
				'phone_digit' => '8',
				'currency' => CurrencyFixtures::BIF
			],
			[
				'name' => 'Mauritanie',
				'iso_code' => 'MAU',
				'valid' => false,
				'phone_code' => '222',
				'phone_digit' => '0',
				'currency' => CurrencyFixtures::MRO
			],
			[
				'name' => 'Congo Brazzaville',
				'iso_code' => 'BRA',
				'valid' => false,
				'phone_code' => '242',
				'phone_digit' => '0',
				'currency' => CurrencyFixtures::CFA
			],
			[
				'name' => 'Cameroun',
				'iso_code' => 'CMR',
				'valid' => true,
				'phone_code' => '237',
				'phone_digit' => '9',
				'currency' => CurrencyFixtures::CFA
			],
			[
				'name' => 'Comores',
				'iso_code' => 'KM',
				'valid' => true,
				'phone_code' => '269',
				'phone_digit' => '7',
				'currency' => CurrencyFixtures::FC
			],
			[
				'name' => 'KENYA',
				'iso_code' => 'KEN',
				'valid' => false,
				'phone_code' => '254',
				'phone_digit' => '9',
				'currency' => CurrencyFixtures::KSh
			],
			[
				'name' => 'Djibouti',
				'iso_code' => 'DJI',
				'valid' => true,
				'phone_code' => '253',
				'phone_digit' => '8',
				'currency' => CurrencyFixtures::DJF
			],

		];

		foreach ($countries as $item) {
			$country = Country::fromFixtures(
				$item['name'],
				$item['iso_code'],
				$item['phone_code'],
				$item['phone_digit'],
				$item['valid']
			);
			$country->setCurrency($this->getCurrencyReference($item['currency']));

			$manager->persist($country);
			$manager->flush();

			$this->setCountryReference($country);

		}
	}

	public function getDependencies(): array {
		return [
			CurrencyFixtures::class,
		];
	}
}
