<?php

namespace App\Services;

class PersonDegreeTypes {
	const TYPE_TRAINING = 'TYPE_TRAINING';
	const TYPE_EMPLOYED = 'TYPE_EMPLOYED';
	const TYPE_UNEMPLOYED = 'TYPE_UNEMPLOYED';
	const TYPE_CONTRACTOR = 'TYPE_CONTRACTOR';
	const TYPE_STUDY = 'TYPE_STUDY';
	const TYPE_SEARCH = 'TYPE_SEARCH';
	const TYPE_DROPOUT = 'TYPE_DROPOUT';
	const TYPE_COMPANY = 'TYPE_COMPANY';

	private array $types;

	public function __construct() {
		$this->types = [
			self::TYPE_TRAINING => 'En cours de Formation Professionnelle',
			self::TYPE_EMPLOYED => 'En emploi',
			self::TYPE_CONTRACTOR => 'Entrepreneur',
			self::TYPE_SEARCH => "En recherche d'emploi",
			self::TYPE_STUDY => "En poursuite d'études",
			self::TYPE_UNEMPLOYED => "Sans emploi",
			self::TYPE_DROPOUT => "Décrochage",
		];
	}

	public function getTypes(): array {
		return $this->types;
	}
}
