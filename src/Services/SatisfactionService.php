<?php

namespace App\Services;


class SatisfactionService {

	/**
	 * Création de la date de debut de saisie du questionnaire : 6 mois apres l'obstantion du diplôme
	 * @throws
	 */
	public function createBeginDate(?\DateTime $createdDate, int $nbMonthAfter): \DateTime {
		$createdDate1 = $createdDate;
		if (!$createdDate1) {
			$createdDate1 = new \DateTime();
		}

		$createBeginDate = $createdDate;
		if ($nbMonthAfter > 0) {
			$createBeginDate = $createdDate1->add(new \DateInterval('P' . $nbMonthAfter . 'M'));
		} elseif ($nbMonthAfter < 0) {
			$createBeginDate = $createdDate1->sub(new \DateInterval('P' . abs($nbMonthAfter) . 'M'));
		}

		return $createBeginDate;
	}

	/**
	 * Création de la date de fin de validation du questionnaire
	 * @throws
	 */
	public function createEndedUpdateDate(?\DateTime $createdDate, int $nbMonthAfter): \DateTime {
		$createdDate1 = $createdDate;
		$addMonths = 'P' . $nbMonthAfter . 'M';

		if (!$createdDate1) {
			$createdDate1 = new \DateTime();
		}

		return $createdDate1->add(new \DateInterval($addMonths));
	}

	public function getEndedUpdateDateFormatFr(?\DateTime $endedUpdateDate): ?string {
		return $endedUpdateDate?->format('d/m/Y');
	}

	public function getBeginDateFormatFr(?\DateTime $beginDate): ?string {
		return $beginDate?->format('d/m/Y');
	}
}
