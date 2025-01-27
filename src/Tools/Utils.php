<?php

namespace App\Tools;

class Utils {
   const COMPANY = 'ROLE_ENTREPRISE';
   const PERSON_DEGREE = 'ROLE_DIPLOME';
   const SCHOOL = 'ROLE_ETABLISSEMENT';
   const LEGISLATOR = 'ROLE_LEGISLATEUR';
   const DIRECTOR = 'ROLE_DIRECTEUR';
   const ADMINISTRATOR = 'ROLE_ADMIN';

   // Type flashbag
   const FB_SUCCESS = 'success';
   const FB_WARNING = 'warning';
   const FB_DANGER = 'danger';

   // Type flashbag
   const OFB_SUCCESS = 'oth_success';
   const OFB_WARNING = 'oth_warning';
   const OFB_DANGER = 'oth_danger';

	const FORMAT_US = 'm/d/Y';
	const FORMAT_FR = 'd/m/Y';

	public static function sanitizeName(string $name): string
	{
		// Supprime les accents en conservant les caractères non modifiés
		$name = iconv('UTF-8', 'ASCII//TRANSLIT', $name);

		// Remplace les caractères non alphanumériques (sauf les espaces) par rien
		$name = preg_replace('/[^A-Za-z0-9 ]/', '', $name);

		// Remplacer les espaces par des tirets
		$name = preg_replace('/\s+/', '', $name);

		// Supprimer les tirets au début et à la fin
		return trim($name, '-');
	}


}
