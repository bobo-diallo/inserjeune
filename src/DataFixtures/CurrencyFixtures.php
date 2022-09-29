<?php

namespace App\DataFixtures;

use App\Entity\Currency;
use Doctrine\Persistence\ObjectManager;

final class CurrencyFixtures extends AbstractFixtures
{
	public const CFA = 'CFA';
	public const MGA = 'MGA';
	public const CDF = 'CDF';
	public const HTG = 'HTG';
	public const MAD = 'MAD';
	public const TND = 'TND';
	public const CVE = 'CVE';
	public const GNF = 'GNF';
	public const RWF = 'RWF';
	public const BIF = 'BIF';
	public const MRO = 'MRO';
	public const FC = 'FC';
	public const KSh = 'KSh';
	public const DJF = 'DJF';

	public function load(ObjectManager $manager): void
	{
		$currencies = [
			[
				'name' => 'Franc CFA',
				'isoName' => 'CFA',
				'isoNum' => 'XAF',
				'isoSymbol' => 'CFA',
			],
			[
				'name' => 'Ariary malgache',
				'isoName' => 'MGA',
				'isoNum' => 'MGA',
				'isoSymbol' => 'MGA',
			],
			[
				'name' => 'Franc congolais',
				'isoName' => 'CDF',
				'isoNum' => 'CDF',
				'isoSymbol' => 'CDF',
			],
			[
				'name' => 'Gourde',
				'isoName' => 'HTG',
				'isoNum' => 'HTG',
				'isoSymbol' => 'HTG',
			],
			[
				'name' => 'Dirham marocain',
				'isoName' => 'MAD',
				'isoNum' => 'MAD',
				'isoSymbol' => 'MAD',
			],
			[
				'name' => 'Dinar tunisien',
				'isoName' => 'TND',
				'isoNum' => 'TND',
				'isoSymbol' => 'TND',
			],
			[
				'name' => 'Escudo cap-verdien',
				'isoName' => 'CVE',
				'isoNum' => 'CVE',
				'isoSymbol' => 'CVE',
			],
			[
				'name' => 'Franc guinéen',
				'isoName' => 'GNF',
				'isoNum' => 'GNF',
				'isoSymbol' => 'GNF',
			],
			[
				'name' => 'Franc rwandais',
				'isoName' => 'RWF',
				'isoNum' => 'RWF',
				'isoSymbol' => 'RWF',
			],
			[
				'name' => 'Franc burundais',
				'isoName' => 'BIF',
				'isoNum' => 'BIF',
				'isoSymbol' => 'BIF',
			],
			[
				'name' => 'Ouguiya mauritanien',
				'isoName' => 'MRO',
				'isoNum' => 'MRO',
				'isoSymbol' => 'MRO',
			],
			[
				'name' => 'Franc Comorien',
				'isoName' => 'FC',
				'isoNum' => 'KMF',
				'isoSymbol' => 'FC',
			],
			[
				'name' => 'Kenyan shilling',
				'isoName' => 'KSh',
				'isoNum' => '4217',
				'isoSymbol' => 'KES',
			],
			[
				'name' => 'Franc Djiboutien',
				'isoName' => 'DJF',
				'isoNum' => '262',
				'isoSymbol' => 'DJF',
			],
		];

		foreach ($currencies as $currency) {
			$manager->persist(
				$currency = Currency::createFixture(
					$currency['name'],
					$currency['isoName'],
					$currency['isoNum'],
					$currency['isoSymbol']
				)
			);

			$manager->flush();
			$this->setCurrencyReference($currency);

		}
	}
}
