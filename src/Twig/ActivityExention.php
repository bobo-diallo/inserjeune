<?php

namespace App\Twig;

use App\Entity\Activity;
use App\Entity\SectorArea;
use App\Repository\ActivityRepository;
use App\Repository\CountryRepository;
use App\Repository\PersonDegreeRepository;
use App\Repository\SectorAreaRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormView;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ActivityExention extends AbstractExtension {

	/**
	 * @var EntityManagerInterface
	 */
	private EntityManagerInterface $entityManager;
	private CountryRepository $countryRepository;
	private ActivityRepository $activityRepository;
	private SectorAreaRepository $sectorAreaRepository;
	private PersonDegreeRepository $personDegreeRepository;

	public function __construct(
		EntityManagerInterface $entityManager,
		CountryRepository $countryRepository,
		ActivityRepository $activityRepository,
		SectorAreaRepository $sectorAreaRepository,
		PersonDegreeRepository $personDegreeRepository
	) {
		$this->entityManager = $entityManager;
		$this->countryRepository = $countryRepository;
		$this->activityRepository = $activityRepository;
		$this->sectorAreaRepository = $sectorAreaRepository;
		$this->personDegreeRepository = $personDegreeRepository;
	}

	public function getFunctions(): array {
		return [
			new TwigFunction('show_activities', [$this, 'showActivities'], ['is_safe' => ['html']]),
			new TwigFunction('show_activity', [$this, 'showActivity'], ['is_safe' => ['html']]),
			new TwigFunction('show_allactivities', [$this, 'showAllActivities'], ['is_safe' => ['html']]),
			new TwigFunction('show_activities_rate', [$this, 'showActivitiesRate'], ['is_safe' => ['html']])
		];
	}

	public function showActivities(
		FormView    $formView,
		Collection  $activities,
		?SectorArea $sectorArea,
		string      $lableOther = ''): string {
		if (!$lableOther) $lableOther = "Autre $lableOther";

		$idForm = $formView->vars['id'];
		$fullName = $formView->vars['full_name'];

		if ($activities->toArray()) {
			$selectedActivities = $activities->toArray();
			$html = sprintf('<select id="%s" name="%s" class="form-control" multiple="multiple">', $idForm, $fullName);

			// Ajout des activities selectionnées
			/** @var Activity $selectedActivity */
			foreach ($selectedActivities as $selectedActivity) {
				$html .= sprintf('<option value="%s" selected="selected" >%s</option>', $selectedActivity->getId(), $selectedActivity->getName());
			}
			$otherActivities = $this->activityRepository->getActivitiesWithout($activities);

			// Ajout des autres activités
			/** @var Activity $otherActivity */
			foreach ($otherActivities as $otherActivity) {
				$html .= sprintf('<option value="%s">%s</option>', $otherActivity->getId(), $otherActivity->getName());
			}

			// Ajout autre
			$html .= "<option value=\"\">$lableOther</option>";
			$html .= "</select>";
			return $html;
		} else {
			if ($sectorArea) {
				$allActivities = $this->activityRepository->findBy(['sectorArea' => $sectorArea]);
				$html = sprintf('<select id="%s" name="%s" class="form-control" multiple="multiple">', $idForm, $fullName);

				/** @var Activity $otherActivity */
				foreach ($allActivities as $allActivity) {
					$html .= sprintf("<option value='%s'>%s</option>", $allActivity->getId(), $allActivity->getName());
				}
				$html .= "<option value=\"\">$lableOther</option>";
				$html .= "</select>";
				return $html;
			}
			return sprintf('<select id="%s" name="%s" class="form-control" multiple="multiple"></select>', $idForm, $fullName);
		}
	}

	public function showActivity(
		FormView    $formView,
		?Activity   $activity,
		?SectorArea $sectorArea,
		string      $lableOther = ''): string {
		if (!$lableOther) $lableOther = "Autre $lableOther";

		$idForm = $formView->vars['id'];
		$fullName = $formView->vars['full_name'];

		if ($activity) {
			$html = sprintf('<select id="%s" name="%s" class="form-control">', $idForm, $fullName);
			$html .= sprintf('<option value="%s" selected="selected" >%s</option>', $activity->getId(), $activity->getName());
			$otherActivities = $sectorArea->getActivities();
			$otherActivities->remove($activity->getId());

			/** @var Activity $otherActivity */
			foreach ($otherActivities as $otherActivity) {
				$html .= sprintf('<option value="%s">%s</option>', $otherActivity->getId(), $otherActivity->getName());
			}

			// Ajout autre
			$html .= "<option value=\"\">$lableOther</option>";
			$html .= "</select>";
			return $html;
		} else {
			if ($sectorArea) {
				$html = sprintf('<select id="%s" name="%s" class="form-control">', $idForm, $fullName);

				/** @var Activity $otherActivity */
				foreach ($sectorArea->getActivities() as $activity) {
					$html .= sprintf("<option value='%s'>%s</option>", $activity->getId(), $activity->getName());
				}
				$html .= "<option value=\"\">$lableOther</option>";
				$html .= "</select>";
				return $html;
			}
			return sprintf('<select id="%s" name="%s" class="form-control"></select>', $idForm, $fullName);
		}
	}

	/**
	 * @return string
	 */
	public function showAllActivities(): string {
		$activities = $this->activityRepository->findAll();

		$html = '<select name="" id="allActivities" style="display: none">';

		foreach ($activities as $activity) {
			$html .= sprintf('<option value="%s" name="%s">%s</option>',
				$activity->getId(),
				$activity->getName(),
				$activity->getSectorArea()->getId());
		}

		$html .= '</select>';

		return $html;
	}

	/**
	 * @param integer $idCountry
	 * @return string
	 */
	public function showActivitiesRate(int $idCountry): string {
		$country = $this->countryRepository->find($idCountry);
		$sectorAreas = $this->sectorAreaRepository->findAll();
		$personDegrees = $this->personDegreeRepository->findByCountry($country);
		$html = '';

		// Recherche des personDegree dans les sectorArea
		$html .= '<div class="row">';

		// recherche le secteur avec le plus de métiers
		$maxActivities = 0;
		$numSector = 0;

		foreach ($sectorAreas as $sectorArea) {
			$numSector++;

			// Calcul le nombre de lignes d'activités pour avoir la même hauteur coté pair et impair
			if ($numSector % 2 != 0) { //calcul des lignes si col pair
				$maxActivities = count($sectorArea->getActivities());
				if (($numSector) < count($sectorAreas)) //si ce n'est pas le dernier SectorArea
					if (count($sectorAreas[$numSector]->getActivities()) > $maxActivities) //calcul des lignes si col impair
						$maxActivities = count($sectorAreas[$numSector]->getActivities());
			}

			// Impression des infos sur le secteur d'activités
			$activities = $sectorArea->getActivities();
			$sectorAreaPersonDegrees = $this->personDegreeRepository->getBySectorArea($sectorArea);

			$validPersons = array();
			foreach ($sectorAreaPersonDegrees as $sectorAreaPersonDegree) {
				if ($sectorAreaPersonDegree->getCountry() == $country)
					$validPersons[] = $sectorAreaPersonDegree;
			}
			$sectorAreaPersonDegrees = $validPersons;

			$html .= '<div class="col-sm-6">';
			$html .= '<div class="element-box el-tablo">';

			$sectorAreaPersonDegreesRate = 0;
			if (count($personDegrees) > 0)
				$sectorAreaPersonDegreesRate = count($sectorAreaPersonDegrees) / count($personDegrees) * 100;
			$html .= sprintf('<div class="label"><span>%s = (%s/%s) </span><span>%s%%</span></div>',
				$sectorArea->getName(),
				count($sectorAreaPersonDegrees),
				count($personDegrees),
				number_format($sectorAreaPersonDegreesRate, 2, ',', ' '));


			// Recherche des personDegree dans les activities
			foreach ($activities as $activity) {
				$activityPersonDegrees = $this->personDegreeRepository
					->getByActivity($activity);

				$validPersons = array();
				foreach ($activityPersonDegrees as $activityPersonDegree) {
					if ($activityPersonDegree->getCountry() == $country)
						$validPersons[] = $activityPersonDegree;
				}
				$activityPersonDegrees = $validPersons;

				if (count($activityPersonDegrees) >= 0) {
					$activityPersonDegreesRate = 0;
					if (count($personDegrees) > 0)
						$activityPersonDegreesRate = count($activityPersonDegrees) / count($personDegrees) * 100;
					$html .= sprintf('<div class="value"><span>%s = (%s/%s)</span><span>%s%%</span></div>',
						$activity->getName(),
						count($activityPersonDegrees),
						count($personDegrees),
						number_format($activityPersonDegreesRate, 2, ',', ' '));
				}
			}

			// récupération du nombre des autres activités
			$otherActivityPersonDegrees = array();
			foreach ($sectorAreaPersonDegrees as $sectorAreaPersonDegree) {
				if ($sectorAreaPersonDegree->getActivity())
					$otherActivityPersonDegrees[] = $sectorAreaPersonDegree;
			}

			// incrementation de $maxActivities si autre activits détecté
			if (count($otherActivityPersonDegrees) > 0)
				if ($maxActivities == count($sectorArea->getActivities()))
					$maxActivities++;

			// calcul du nombre de ligne à ajouter et ecriture
			$nbAddLine = $maxActivities - count($sectorArea->getActivities());
			if (count($otherActivityPersonDegrees) > 0) {
				$nbAddLine--;

				$otherActivityPersonDegreesRate = count($otherActivityPersonDegrees) / count($personDegrees) * 100;
				$html .= sprintf('<div class="value"><span>autre activité = (%s/%s) </span><span>%s%%</span></div>',
					count($sectorAreaPersonDegrees),
					count($personDegrees),
					number_format($otherActivityPersonDegreesRate, 2, ',', ' '));
			}

			// ajoute des lignes vides pour le design
			for ($i = 0; $i < $nbAddLine; $i++) {
				$html .= '<div class="value"> &nbsp;</div>';
			}

			// Gestion des lignes bootstrap
			$html .= '</div>';
			$html .= '</div>';
			if ($numSector % 2 == 0) {
				$html .= '</div>';
				$html .= '<div class="row">';
			}
		}
		if ($numSector % 2 != 0)
			$html .= '</div>';

		return $html;
	}

}
