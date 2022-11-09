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
	 * check if Person degree is well as satisfaction survey
	 * @param PersonDegree $person
	 * @return string
	 * @throws
	 */
	public function checkPersonDegreeSatisfaction(PersonDegree $person): string {
		$cRouge = "#FF0000;";
		$cRose = "#FFABC4;";
		$cOrange = "#FF7100;";
		$cVert = "#00FF00;";
		$cBlanc = "#FFFFFF;";
		$cError = "#4E4E4E;";
		$color = "#EE3233;";
		$status = "false";
		$border = "none";
		$bNone = "none";
		$bNoir = "1px solid #0c0c0c";

		$degreeMonth = "1";
		if ($person->getLastDegreeMonth() > 0) {
			$degreeMonth = $person->getLastDegreeMonth();
		}

		//comparer la date de création du questionnaire avec année/mois d'obstention du diplôme
		$degreeDateStr = $person->getLastDegreeYear() . "-" . $degreeMonth . "-1";
		$degreeDate = new \DateTime($degreeDateStr);
		$compareDate = $degreeDate->add(new \DateInterval('P6M'));
		$offsetDays = $compareDate->diff(new \DateTime())->format('%R%a');

		if ($offsetDays < 0) {
			$color = $cBlanc;
			$border = $bNoir;
		} elseif (($offsetDays >= 0) && ($offsetDays < 20)) {
			$color = $cOrange;
			$border = $bNone;
		} else {
			$color = $cRouge;
			$border = $bNone;
		}
		if (!$offsetDays)
			$offsetDays = "err";

		if ($person->getLastDegreeMonth() > 0) {
			switch ($person->getType()) {
				case "TYPE_TRAINING":
					$color = $cRose;
					$status = "true";
					$border = $bNone;
					break;
				case "TYPE_EMPLOYED":
					if ($this->entityManager->getRepository(SatisfactionSalary::class)->getLastSatisfaction($person)) {
						$color = $cVert;
						$status = "true";
						$border = $bNone;
					}
					break;
				case "TYPE_CONTRACTOR":
					if ($this->entityManager->getRepository(SatisfactionCreator::class)->getLastSatisfaction($person)) {
						$color = $cVert;
						$status = "true";
						$border = $bNone;
					}
					break;
				case "TYPE_STUDY":
				case "TYPE_UNEMPLOYED":
				case "TYPE_SEARCH":
					if ($this->entityManager->getRepository(SatisfactionSearch::class)->getLastSatisfaction($person)) {
						$color = $cVert;
						$status = "true";
						$border = $bNone;
					}
					break;
			}
		} else {
			$color = $cError;
			if ($person->getType() == "TYPE_TRAINING") {
				$color = $cRose;
				$status = "true";
				$border = $bNone;
			}
		}

		if ($color != $cError) {
			$html = sprintf('<div style="width: 30px; height: 20px; background-color: %s; margin: auto; border: %s"></div><div style="display: none">%s</div>', $color, $border, $status);

		} else {
			$html = 'Erreur sur mois d\'obtention';
		}
		return $html;
	}

}
