<?php

namespace App\Twig;

use App\Entity\PersonDegree;
use App\Entity\SatisfactionCreator;
use App\Entity\SatisfactionSalary;
use App\Entity\SatisfactionSearch;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CheckDatabaseExention extends AbstractExtension {

	/**
	 * @var EntityManagerInterface
	 */
	private EntityManagerInterface $entityManager;

	public function __construct(EntityManagerInterface $entityManager) {
		$this->entityManager = $entityManager;
	}

	public function getFunctions(): array {
		return [
			new TwigFunction('check_person_degree_sector_area', [$this, 'checkPersonDegreeSectorArea'], ['is_safe' => ['html']]),
			new TwigFunction('check_person_degree_satisfaction', [$this, 'checkPersonDegreeSatisfaction'], ['is_safe' => ['html']])
		];
	}


	/**
	 * check if person degree is well as sector area
	 * @return string
	 */
	public function checkPersonDegreeSectorArea(): string {
		$personDegrees = $this->entityManager->getRepository(PersonDegree::class)->findAll();
		$html = '';

		/* Recherche des personDegree sans sectorArea */
		/* ------------------------------------------ */
		$personDegreeWithoutSectorAreas = array();
		foreach ($personDegrees as $personDegree) {
			if (!$personDegree->getSectorArea())
				$personDegreeWithoutSectorAreas[] = $personDegree;
		}

		/* Affichage des personDegree sans sectorArea */
		/* ------------------------------------------ */
		if ($personDegreeWithoutSectorAreas) {
			$html .= '<span>Erreur: Liste des diplômés sans secteur d\'activité</span><br>';
			foreach ($personDegreeWithoutSectorAreas as $personDegreeWithoutSectorArea) {
				$html .= sprintf('<span style="margin-left: 30px"> %d %s</span><br>', $personDegreeWithoutSectorArea->getId(), $personDegreeWithoutSectorArea->getName());
			}
			$html .= '<br>';
		}

		return $html;
	}

	/**
	 * Check if Person degree is well as satisfaction survey
	 *
	 * @param int $personDegreeId
	 * @param string $personDegreeType
	 * @param int|null $lastDegreeMonth
	 * @param int|null $lastDegreeYear
	 * @return string
	 * @throws \Exception
	 */
	public function checkPersonDegreeSatisfaction(
		int $personDegreeId,
		string $personDegreeType,
		?int $lastDegreeMonth,
		?int $lastDegreeYear,
		int $satisfactionCreatorsCount = 0,
		int $satisfactionSalariesCount = 0,
		int $satisfactionSearchesCount = 0
	): string {
		$cRouge = '#FF0000;';
		$cRose = '#FFABC4;';
		$cOrange = '#FF7100;';
		$cVert = '#00FF00;';
		$cBlanc = '#FFFFFF;';
		$cError = '#4E4E4E;';
		$status = 'false';
		$bNone = 'none';
		$bNoir = '1px solid #0c0c0c';
        $value = '';

		$degreeMonth = "1";
		if ($lastDegreeMonth > 0) {
			$degreeMonth = $lastDegreeMonth;
		}

        $currentDate = new \DateTime();
		$degreeDateStr = $lastDegreeYear . "-" . $degreeMonth . "-1"; // 2017-7-1
		$degreeDate = new \DateTime($degreeDateStr);
        $compareDate3Month =  clone $degreeDate;
        $compareDate6Month =  clone $degreeDate;
		$compareDate3Month = $compareDate3Month->add(new \DateInterval('P3M'));
		$compareDate6Month = $compareDate6Month->add(new \DateInterval('P6M'));

		if ($currentDate < $compareDate3Month) {
			$color = $cBlanc;
			$border = $bNoir;
            $value = 0;
		} elseif ($currentDate < $compareDate6Month) {
			$color = $cOrange;
			$border = $bNone;
            $value = 1;
		} else {
			$color = $cRouge;
			$border = $bNone;
            $value = 2;
		}

		if ($lastDegreeMonth > 0) {
			switch ($personDegreeType) {
				case 'TYPE_TRAINING':
					$color = $cRose;
					$status = 'true';
					$border = $bNone;
                    $value = 3;
					break;
				case 'TYPE_EMPLOYED':
					if ($satisfactionSalariesCount > 0) {
						$color = $cVert;
						$status = 'true';
						$border = $bNone;
                        $value = 4;
					}
					break;
				case 'TYPE_CONTRACTOR':
					if ($satisfactionCreatorsCount > 0) {
						$color = $cVert;
						$status = 'true';
						$border = $bNone;
                        $value = 4;
					}
					break;
				case 'TYPE_STUDY':
				case 'TYPE_UNEMPLOYED':
				case 'TYPE_SEARCH':
					if ($satisfactionSearchesCount > 0) {
						$color = $cVert;
						$status = 'true';
						$border = $bNone;
                        $value = 4;
					}
					break;
			}
		} else {
			$color = $cError;
			if ($personDegreeType == 'TYPE_TRAINING') {
				$color = $cRose;
				$status = 'true';
				$border = $bNone;
                $value = 3;
			}
		}

		if ($color != $cError) {
			$html = sprintf('<div style="width: 30px; height: 20px; color: %s; background-color: %s; margin: auto; border: %s">%s</div><div style="display: none">%s</div>', $color, $color, $border,$value, $status);

		} else {
			$html = 'Erreur sur mois d\'obtention';
		}
		return $html;
	}
}
