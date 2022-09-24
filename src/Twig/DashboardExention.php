<?php

namespace App\Twig;

use App\Repository\ActivityRepository;
use App\Repository\CompanyRepository;
use App\Repository\ContractRepository;
use App\Repository\CountryRepository;
use App\Repository\JobNotFoundReasonRepository;
use App\Repository\LegalStatusRepository;
use App\Repository\PersonDegreeRepository;
use App\Repository\RegionRepository;
use App\Repository\SatisfactionCompanyRepository;
use App\Repository\SatisfactionCreatorRepository;
use App\Repository\SatisfactionSalaryRepository;
use App\Repository\SatisfactionSearchRepository;
use App\Repository\SectorAreaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DashboardExention extends AbstractExtension {
	/**
	 * @var EntityManagerInterface
	 */
	private EntityManagerInterface $entityManager;
	private RegionRepository $regionRepository;
	private CompanyRepository $companyRepository;
	private SatisfactionCompanyRepository $satisfactionCompanyRepository;
	private PersonDegreeRepository $personDegreeRepository;
	private CountryRepository $countryRepository;
	private SatisfactionSearchRepository $satisfactionSearchRepository;
	private JobNotFoundReasonRepository $jobNotFoundReasonRepository;
	private SatisfactionSalaryRepository $satisfactionSalaryRepository;
	private SatisfactionCreatorRepository $satisfactionCreatorRepository;
	private SectorAreaRepository $sectorAreaRepository;
	private ActivityRepository $activityRepository;
	private LegalStatusRepository $legalStatusRepository;
	private ContractRepository $contractRepository;

	public function __construct(
		EntityManagerInterface $entityManager,
		RegionRepository $regionRepository,
		CompanyRepository $companyRepository,
		SatisfactionCompanyRepository $satisfactionCompanyRepository,
		PersonDegreeRepository $personDegreeRepository,
		CountryRepository $countryRepository,
		SatisfactionSearchRepository $satisfactionSearchRepository,
		JobNotFoundReasonRepository $jobNotFoundReasonRepository,
		SatisfactionSalaryRepository $satisfactionSalaryRepository,
		SatisfactionCreatorRepository $satisfactionCreatorRepository,
		SectorAreaRepository $sectorAreaRepository,
		ActivityRepository $activityRepository,
		LegalStatusRepository $legalStatusRepository,
		ContractRepository $contractRepository
	) {
		$this->entityManager = $entityManager;
		$this->regionRepository = $regionRepository;
		$this->companyRepository = $companyRepository;
		$this->satisfactionCompanyRepository = $satisfactionCompanyRepository;
		$this->personDegreeRepository = $personDegreeRepository;
		$this->countryRepository = $countryRepository;
		$this->satisfactionSearchRepository = $satisfactionSearchRepository;
		$this->jobNotFoundReasonRepository = $jobNotFoundReasonRepository;
		$this->satisfactionSalaryRepository = $satisfactionSalaryRepository;
		$this->satisfactionCreatorRepository = $satisfactionCreatorRepository;
		$this->sectorAreaRepository = $sectorAreaRepository;
		$this->activityRepository = $activityRepository;
		$this->legalStatusRepository = $legalStatusRepository;
		$this->contractRepository = $contractRepository;
	}

	public function getFunctions(): array {
		return [
			new TwigFunction('company_satisfaction_skill_rate', [$this, 'companySatisfactionSkillRate'], ['is_safe' => ['html']]),
			new TwigFunction('company_satisfaction_hire_rate', [$this, 'companySatisfactionHireRate'], ['is_safe' => ['html']]),
			new TwigFunction('person_degree_activities_rate', [$this, 'personDegreeActivitiesRate'], ['is_safe' => ['html']]),
			new TwigFunction('person_degree_situation_rate', [$this, 'personDegreeSituationRate'], ['is_safe' => ['html']]),
			new TwigFunction('person_degree_unemployed_pursuit_last_degree_rate', [$this, 'personDegreeUnemployedPursuitLastDegreeRate'], ['is_safe' => ['html']]),
			new TwigFunction('person_degree_reason_graph', [$this, 'personDegreeReasonGraph'], ['is_safe' => ['html']]),
			new TwigFunction('entity_portion_cheese', [$this, 'EntityPortionCheese'], ['is_safe' => ['html']]),
		];
	}

	public function getTableColor(): array {
		$tableColors = array();
		$tableColors[] = ["oif1_green", "#85C441", "#56802F"];
		$tableColors[] = ["oif2_yellow", "#FFD403", "#9D8106"];
		$tableColors[] = ["oif3_blue", "#74AAFF", "#456293"];
		$tableColors[] = ["oif4_red", "#EE3233", "#871D1D"];
		$tableColors[] = ["oif5_purple", "#8E1682", "#550D4E"];
		$tableColors[] = ["orange", "#FF8B00", "#894B01"];
		$tableColors[] = ["black2", "#1C1817", "#98837E"];
		$tableColors[] = ["darkgrey", "#BFBFBF", "#737473"];
		$tableColors[] = ["aqua", "#00ffff", "#b8bbe8"];
		$tableColors[] = ["lime", "#00ff00", "#45de45"];
		$tableColors[] = ["silver", "#c0c0c0", "#acacac"];
		$tableColors[] = ["yellow", "#ffff00", "#9D8106"];
		$tableColors[] = ["green", "#008000", "#10de10"];
		$tableColors[] = ["blue", "#0000ff", "#aaaaff"];
		$tableColors[] = ["fuchsia", "#ff00ff", "#ffbbff"];
		$tableColors[] = ["gray", "#808080", "#c0c0c0"];
		$tableColors[] = ["red", "#ff0000", "#ff9797"];
		$tableColors[] = ["olive", "#808000", "#bcbc2e"];
		$tableColors[] = ["teal", "#008080", "#14bcbc"];
		$tableColors[] = ["purple", "#800080", "#b81eb8"];
		$tableColors[] = ["maroon", "#800000", "#c44545"];
		$tableColors[] = ["navy", "#000080", "#3232e1"];
		$tableColors[] = ["black", "#000000", "#7e7e7e"];

		return $tableColors;
	}

	/**
	 * @param integer $idCountry
	 * @param integer idRegion
	 * @param string $personType
	 * @param array $skillLevels
	 * @return string
	 * @throws
	 */
	public function companySatisfactionHireRate(int $idCountry, $idRegion, string $personType, array $skillLevels): string {
		$region = $this->regionRepository->find($idRegion);

		if (!$region) {
			$companies = $this->companyRepository->findByCountry($idCountry);
		} else {
			$companies = $this->companyRepository->findByRegion($idRegion);
		}
		$html = "";
		$nbSatisfactionCompany = 0;

		// création tableau pour stokage du nombre de chaque satisfactions
		$tableSatisfactions = array();
		foreach ($skillLevels as $skillLevel) {
			$satisfaction_temp = [$skillLevel, 0];
			$tableSatisfactions[] = $satisfaction_temp;
		}

		foreach ($companies as $company) {
			$satisfaction = $this->satisfactionCompanyRepository->getLastSatisfaction($company);

			if ($satisfaction) {
				$nbHiring = null;
				if ($personType == "ouvrier") {
					$nbHiring = $satisfaction->getHiring6MonthsWorker();
				} elseif ($personType == "technicien") {
					$nbHiring = $satisfaction->getHiring6MonthsTechnician();
				} elseif ($personType == "apprenti") {
					$nbHiring = $satisfaction->getHiring6MonthsApprentice();
				} elseif ($personType == "stagiaire") {
					$nbHiring = $satisfaction->getHiring6MonthsStudent();
				}

				$nbSatisfactionCompany++;
				for ($i = 0; $i < count($tableSatisfactions); $i++) {
					if ($tableSatisfactions[$i][0] == $nbHiring) {
						$tableSatisfactions[$i][1]++;
					}
				}
			}
		}

		$html .= "<table>";
		foreach ($tableSatisfactions as $tableSatisfaction) {
			$html .= "<tr>";
			$nbSatisfactionCompanyRate = 0;
			if ($nbSatisfactionCompany > 0)
				$nbSatisfactionCompanyRate = number_format($tableSatisfaction[1] / $nbSatisfactionCompany * 100, 2);
			$html .= sprintf("<td>%s = (%d/%d)</td> <td>%s%%</td>", $tableSatisfaction[0], $tableSatisfaction[1], $nbSatisfactionCompany, $nbSatisfactionCompanyRate);
			$html .= "</tr>";
		}
		$html .= "</table>";
		return $html;
	}

	/**
	 * @param integer $idCountry
	 * @param integer $idRegion
	 * @param string $skillName
	 * @param array $skillLevels
	 * @return string
	 * @throws
	 */
	public function companySatisfactionSkillRate(int $idCountry, int $idRegion, string $skillName, array $skillLevels): string {

		$region = $this->regionRepository->find($idRegion);

		if (!$region) {
			$companies = $this->companyRepository->findByCountry($idCountry);
		} else {
			$companies = $this->companyRepository->findByRegion($idRegion);
		}

		$html = "";
		$nbSatisfactionCompany = 0;

		/* création tableau pour stokage du nombre de chaque satisfactions */
		/* --------------------------------------------------------------- */
		$tableSatisfactions = array();
		foreach ($skillLevels as $skillLevel) {
			$satisfaction_temp = [$skillLevel, 0];
			$tableSatisfactions[] = $satisfaction_temp;
		}

		foreach ($companies as $company) {
			$satisfaction = $this->satisfactionCompanyRepository->getLastSatisfaction($company);

			if ($satisfaction) {
				$skill = null;
				if ($skillName == "levelSkill") {
					$skill = $satisfaction->getLevelSkill();
				} elseif ($skillName == "levelGlobalSkill") {
					$skill = $satisfaction->getLevelGlobalSkill();
				} elseif ($skillName == "levelTechnicalSkill") {
					$skill = $satisfaction->getLevelTechnicalSkill();
				} elseif ($skillName == "levelCommunicationHygieneHealthEnvSkill") {
					$skill = $satisfaction->getLevelCommunicationHygieneHealthEnvSkill();
				}

				if ($skill == "1") {
					$skill = "très insatisfaisant";
				} elseif ($skill == "2") {
					$skill = "insatisfaisant";
				} elseif ($skill == "3") {
					$skill = "satisfaisant ; entre les deux";
				} elseif ($skill == "4") {
					$skill = "bon";
				} elseif ($skill == "5") {
					$skill = "excellent";
				}

				$nbSatisfactionCompany++;
				for ($i = 0; $i < count($tableSatisfactions); $i++) {
					if ($tableSatisfactions[$i][0] == $skill) {
						$tableSatisfactions[$i][1]++;
					}
				}
			}
		}

		$html .= sprintf("<table>");
		foreach ($tableSatisfactions as $tableSatisfaction) {
			$html .= sprintf("<tr>");
			$nbSatisfactionCompanyRate = 0;
			if ($nbSatisfactionCompany > 0)
				$nbSatisfactionCompanyRate = number_format($tableSatisfaction[1] / $nbSatisfactionCompany * 100, 2);
			$html .= sprintf("<td>%s = (%d/%d)</td> <td>%s%%</td>", $tableSatisfaction[0], $tableSatisfaction[1], $nbSatisfactionCompany, $nbSatisfactionCompanyRate);
			$html .= sprintf("</tr>");
		}
		$html .= sprintf("</table>");
		return $html;
	}

	/**
	 * @param integer $idCountry
	 * @param integer $idRegion
	 * @param array $situations
	 * @return string
	 * @throws
	 */
	public function personDegreeSituationRate($idCountry, $idRegion, $situations) {
		$region = $this->regionRepository->find($idRegion);

		$personDegrees = array();
		if (!$region) {
			$personDegrees = $this->personDegreeRepository->findByCountry($idCountry);
		} else {
			$personDegrees = $this->personDegreeRepository->findByRegion($idRegion);
		}

		$situationsPersonDegrees = array();
		foreach ($situations as $situation) {
			$situationPersonDegrees = $this->personDegreeRepository
				->getByType($situation);

			/* Stockage du diplômé */
			/* ------------------- */
			foreach ($situationPersonDegrees as $situationPersonDegree) {
				if ($situationPersonDegree->getCountry()->getId() == $idCountry)
					$situationsPersonDegrees[] = $situationPersonDegree;
			}
		}

		$situationsPersonDegreesRate = 0;
		if (count($personDegrees) > 0)
			$situationsPersonDegreesRate = count($situationsPersonDegrees) / count($personDegrees) * 100;

		$html = sprintf('<div><table><tr><td><span class="small">(%s/%s)</span></td><td><span> %s%%</span></td></tr></table></div>',
			count($situationsPersonDegrees),
			count($personDegrees),
			number_format($situationsPersonDegreesRate, 2, ',', ' '));
		return $html;
	}

	/**
	 * @param integer $idCountry
	 * @param integer $idRegion
	 * @param boolean $lien
	 * @return string
	 * @throws
	 */
	public function personDegreeUnemployedPursuitLastDegreeRate(int $idCountry, int $idRegion, bool $lien): string {
		$country = $this->countryRepository->find($idCountry);
		$region = $this->regionRepository->find($idRegion);

		$StudyPersonDegrees = array();
		if (!$region) {
			$StudyPersonDegrees = $this->personDegreeRepository->getByCountryAndType($country, "TYPE_STUDY");
		} else {
			$StudyPersonDegrees = $this->personDegreeRepository->getByRegionAndType($region, "TYPE_STUDY");
		}

		$SatisfactionPersonDegrees = array();
		$situationsPersonDegrees = array();

		/* Recherche si la poursuite d'étude est en lien avec le premier diplôme dans le questionnaire de satisfaction */
		/* ----------------------------------------------------------------------------------------------------------- */
		foreach ($StudyPersonDegrees as $StudyPersonDegree) {
			$satisfaction = $this->satisfactionSearchRepository->getLastSatisfaction($StudyPersonDegree);
			if ($satisfaction) {
				$SatisfactionPersonDegrees[] = $StudyPersonDegree;
				if ($satisfaction->getFormationPursuitLastDegree() == $lien) {
					$situationsPersonDegrees[] = $StudyPersonDegree;
				}
			}
		}

		$situationsPersonDegreesRate = 0;
		if (count($SatisfactionPersonDegrees) > 0)
			$situationsPersonDegreesRate = count($situationsPersonDegrees) / count($SatisfactionPersonDegrees) * 100;

		return sprintf('<div><table><tr><td><span class="small">(%s/%s)</span></td><td><span> %s%%</span></td></tr></table></div>',
			count($situationsPersonDegrees),
			count($SatisfactionPersonDegrees),
			number_format($situationsPersonDegreesRate, 2, ',', ' '));
	}

	/**
	 * @param array $personDegrees
	 * @param string $personTypeName
	 * @return array
	 * @throws
	 */
	public function personDegreeReasonRate(array $personDegrees, string $personTypeName) {
		$reasons = $this->jobNotFoundReasonRepository->findAll();

		/* création tableau pour stokage du nombre de chaque raison */
		/* -------------------------------------------------------- */
		$tableReasons = array();
		foreach ($reasons as $reason) {
			$reason_temp = [$reason->getName(), 0];
			$tableReasons[] = $reason_temp;
		}
		$reason_temp = ["autre raison", 0];
		$tableReasons[] = $reason_temp;

		/* Recherche le nombre de raisons de chômage dans l'ensemble des diplômés dans le questionnaire de satisfaction */
		/* ------------------------------------------------------------------------------------------------------------ */
		foreach ($personDegrees as $personDegree) {
			$satisfaction = array();
			if ($personTypeName == "PersonDegreeUnemployed") {
				$satisfaction = $this->satisfactionSearchRepository->getLastSatisfaction($personDegree);
			} elseif ($personTypeName == "PersonDegreeEmployed") {
				$satisfaction = $this->satisfactionSalaryRepository->getLastSatisfaction($personDegree);
			} elseif ($personTypeName == "PersonDegreeContractor") {
				$satisfaction = $this->satisfactionCreatorRepository->getLastSatisfaction($personDegree);
			}
			if ($satisfaction) {
				if ($satisfaction->getJobNotFoundReasons()) {
					foreach ($satisfaction->getJobNotFoundReasons() as $JobNotFoundReason) {
						for ($i = 0; $i < count($tableReasons); $i++) {
							if ($JobNotFoundReason == $tableReasons[$i][0]) {
								$tableReasons[$i][1]++;
							}
						}
					}
				}
				if ($satisfaction->getJobNotFoundOther()) {
					$tableReasons[count($tableReasons) - 1][1]++;
				}
			}
		}
		return $tableReasons;
	}

	/**
	 * @param array $personDegrees
	 * @param string $personTypeName
	 * @return array
	 * @throws
	 */
	public function personDegreeActivityRate(array $personDegrees, string $personTypeName): array {
		$sectorAreas = $this->sectorAreaRepository->findAll();

		/* création tableau pour stokage du nombre de chaque raison */
		/* -------------------------------------------------------- */
		$tableActivities = array();
		foreach ($sectorAreas as $sectorArea) {
			$activities = $this->activityRepository->findBySectorArea($sectorArea);
			foreach ($activities as $activity) {
				$activityTemp = [$activity->getName(), 0];
				$tableActivities[] = $activityTemp;
			}
		}
		$activityTemp = ["autre activité", 0];
		$tableActivities[] = $activityTemp;

		/* Recherche le nombre de raisons de chômage dans l'ensemble des diplômés dans le questionnaire de satisfaction */
		/* ------------------------------------------------------------------------------------------------------------ */
		foreach ($personDegrees as $personDegree) {
			$satisfaction = [];
			if ($personTypeName == "PersonDegreeUnemployed") {
				$satisfaction = $this->satisfactionSearchRepository->getLastSatisfaction($personDegree);
			} elseif ($personTypeName == "PersonDegreeEmployed") {
				$satisfaction = $this->satisfactionSalaryRepository->getLastSatisfaction($personDegree);
			} elseif ($personTypeName == "PersonDegreeContractor") {
				$satisfaction = $this->satisfactionCreatorRepository->getLastSatisfaction($personDegree);
			}
			if ($satisfaction) {
				if ($satisfaction->getActivities()) {
					foreach ($satisfaction->getActivities() as $activity) {
						for ($i = 0; $i < count($tableActivities); $i++) {
							if ($activity == $tableActivities[$i][0]) {
								$tableActivities[$i][1]++;
							}
						}
					}
				}
				if ($satisfaction->getJobNotFoundOther()) {
					$tableActivities[count($tableActivities) - 1][1]++;
				}
			}
		}
		return $tableActivities;
	}

	/**
	 * @param integer $idCountry
	 * @param integer $idRegion
	 * @param string $objectRateName
	 * @param string $personTypeName
	 * @param array $situations
	 * @param boolean $multiColumn
	 * @return string
	 * @throws
	 */
	public function personDegreeReasonGraph(
		int    $idCountry,
		int    $idRegion,
		string $objectRateName,
		array  $situations,
		string $personTypeName,
		bool   $multiColumn) {
		$personDegrees = array();
		$country = $this->countryRepository->find($idCountry);
		$region = $this->regionRepository->find($idRegion);

		foreach ($situations as $situation) {
			$situationPersonDegrees = [];
			if (!$region) {
				$situationPersonDegrees = $this->personDegreeRepository->getByCountryAndType($country, $situation);
			} else {
				$situationPersonDegrees = $this->personDegreeRepository->getByRegionAndType($region, $situation);
			}
			foreach ($situationPersonDegrees as $situationPersonDegree) {
				$personDegrees[] = $situationPersonDegree;
			}
		}

		$objectRate = array();
		if ($objectRateName == "Reason") {
			$objectRate = $this->personDegreeReasonRate($personDegrees, $personTypeName);
		} elseif ($objectRateName == "Activity") {
			$objectRate = $this->personDegreeActivityRate($personDegrees, $personTypeName);
		}

		/* Ecriture du canvas graph */
		/* ------------------------ */
		$html = "";
		$html .= sprintf('<div class="row element-box element-box-reduct">');
		if ($objectRateName == "Activity") {
			$html .= sprintf('<div class="col-sm-12 el-chart-w2">');
			$html .= sprintf('<canvas height="100px" id="%s%sGraph"></canvas>', $personTypeName, $objectRateName);
		} else {
			$html .= sprintf('<div class="col-sm-4 el-chart-w">');
			$html .= sprintf('<canvas height="100px" id="%s%sGraph" width="80px"></canvas>', $personTypeName, $objectRateName);
		}

		$html .= sprintf('  <p style="text-align: center"><strong>%d</strong>', count($personDegrees));

		$typePersonDegree = "";
		if ($personTypeName == "PersonDegreeUnemployed") {
			$typePersonDegree = "sans emploi";
		} elseif ($personTypeName == "PersonDegreeEmployed") {
			$typePersonDegree = "salariés";
		} elseif ($personTypeName == "PersonDegreeContractor") {
			$typePersonDegree = "entrepreneurs";
		}

		if ($objectRateName == "Activity") {
			$html .= sprintf(' Total Diplômés %s', $typePersonDegree);
		} else {
			$html .= sprintf(' Total Diplômés<br>%s', $typePersonDegree);
		}
		$html .= sprintf('</p>');

		$html .= sprintf('</div>'); //fin div el-chart-w

		/* Ecriture de la légende */
		/* ---------------------- */
		if ($objectRateName == "Activity") {
			$html .= sprintf('<div class="col-sm-12 legend_activity">');
		} else {
			$html .= sprintf('<div class="col-sm-8 legend_activity">');
		}

		$html .= $this->getLegend($objectRate, count($personDegrees), $multiColumn);
		$html .= sprintf('</div>'); //fin legend_activity
		$html .= sprintf('</div>'); //fin element-box

		/* Creation des select pour le JS (canvas) */
		/* --------------------------------------- */
		$html .= $this->getJsSelect("Graph", $personTypeName, $objectRateName, $objectRate, count($personDegrees));

		return $html;
	}

	/**
	 * @param integer $idCountry
	 * @param integer $idRegion
	 * @param string $entityName
	 * @param string $portionEntityName
	 * @param boolean $multiColumn
	 * @return string
	 * @throws
	 */
	public function EntityPortionCheese(
		int    $idCountry,
		int    $idRegion,
		string $entityName,
		string $portionEntityName,
		bool   $multiColumn): string {
		$globalEntities = [];
		$portionEntities = [];

		$country = $this->countryRepository->find($idCountry);
		$region = $this->regionRepository->find($idRegion);

		if ($entityName == "Company") {
			if (!$region) {
				$globalEntities = $this->companyRepository->findByCountry($country);
			} else {
				$globalEntities = $this->companyRepository->findByRegion($region);
			}
		} elseif ($entityName == "PersonDegree") {
			if ($portionEntityName == "Contract") {
				if (!$region) {
					$globalEntities = $this->personDegreeRepository->getByCountryAndType($country, "TYPE_EMPLOYED");
				} else {
					$globalEntities = $this->personDegreeRepository->getByRegionAndType($region, "TYPE_EMPLOYED");
				}
			} else {
				if (!$region) {
					$globalEntities = $this->personDegreeRepository->findByCountry($country);
				} else {
					$globalEntities = $this->personDegreeRepository->findByRegion($region);
				}
			}
		}
		if ($portionEntityName == "SectorArea") {
			$portionEntities = $this->sectorAreaRepository->findAll();
		} elseif ($portionEntityName == "LegalStatus") {
			$portionEntities = $this->legalStatusRepository->findAll();
		} elseif ($portionEntityName == "Contract") {
			$portionEntities = $this->contractRepository->findAll();
		}

		/* Cas du Contract: Suppression des diplômés qui n'ont pas répondu à la satisfactionSearch */
		if ($portionEntityName == "Contract") {
			for ($i = 0; $i < count($globalEntities); $i++) {
				$satisfaction = $this->satisfactionSalaryRepository->getLastSatisfaction($globalEntities[$i]);
				if (!$satisfaction) {
					array_splice($globalEntities, $i, 1);
					$i--;
				}
			}
		}

		/* création tableau pour stokage du nombre de chaque raison */
		$tablePortionEntities = array();
		foreach ($portionEntities as $portionEntity) {
			$portions = [];
			if ($entityName == "Company") {
				if ($portionEntityName == "SectorArea") {
					if (!$region) {
						$portions = $this->companyRepository->getByCountryAndSectorArea($country, $portionEntity);
					} else {
						$portions = $this->companyRepository->getByRegionAndSectorArea($region, $portionEntity);
					}
				} elseif ($portionEntityName == "LegalStatus") {
					if (!$region) {
						$portions = $this->companyRepository->getByCountryAndLegalStatus($country, $portionEntity);
					} else {
						$portions = $this->companyRepository->getByRegionAndLegalStatus($region, $portionEntity);
					}
				}
			} elseif ($entityName == "PersonDegree") {
				if ($portionEntityName == "SectorArea") {
					if (!$region) {
						$portions = $this->personDegreeRepository->getByCountryAndSectorArea($country, $portionEntity);
					} else {
						$portions = $this->personDegreeRepository->getByRegionAndSectorArea($region, $portionEntity);
					}

				} elseif ($portionEntityName == "Contract") {
					/* recupération type de contrat du salarié dans le questionnaire de satisfaction */
					/* ----------------------------------------------------------------------------- */
					foreach ($globalEntities as $globalEntity) {
						$satisfaction = $this->satisfactionSalaryRepository->getLastSatisfaction($globalEntity);
						if ($satisfaction)
							if ($portionEntity == $satisfaction->getContract()) {
								$portions[] = $globalEntity;
							}
					}
				}
			}
			$tableTemp = [trim($portionEntity->getName()), count($portions)];
			$tablePortionEntities[] = $tableTemp;
		}

		/* Ecriture du canvas donut */
		/* ------------------------ */
		$html = "";
		$html .= sprintf('<div class="row element-box element-box-reduct">');
		if ($multiColumn == true) {
			$html .= sprintf('<div class="col-sm-3 el-chart-w">');
		} else {
			$html .= sprintf('<div class="col-sm-4 el-chart-w">');
		}
		$html .= sprintf('<canvas height="120" id="donut%s%s" width="120" data-value="[', $entityName, $portionEntityName);
		for ($i = 0; $i < count($tablePortionEntities); $i++) {
			$html .= sprintf('"%s", ', $tablePortionEntities[$i][0]);
		}
		$html .= sprintf(']"></canvas>');

		$html .= sprintf('<div class="inside-donut-chart-label">');
		$html .= sprintf('  <strong>%d</strong>', count($globalEntities));
		if ($entityName == "Company") {
			$html .= sprintf('  <span>Total Entreprises</span>');
		} elseif ($entityName == "PersonDegree") {
			if ($portionEntityName == "Contract") {
				$html .= sprintf('  <span>Total Diplômés<br>Salariés</span>');
			} else {
				$html .= sprintf('  <span>Total Diplômés</span>');
			}
		}
		$html .= sprintf('</div>'); //fin inside-donut
		$html .= sprintf('</div>'); //fin div el-chart-w

		/* Ecriture de la légende */
		/* ---------------------- */
		if ($multiColumn == true) {
			$html .= sprintf('<div class="col-sm-9 legend_activity">');
		} else {
			$html .= sprintf('<div class="col-sm-8 legend_activity">');
		}
		$html .= $this->getLegend($tablePortionEntities, count($globalEntities), $multiColumn);
		$html .= sprintf('</div>'); //fin legend_activity

		$html .= sprintf('</div>'); //fin element-box

		/* Creation des select pour le JS (canvas) */
		/* --------------------------------------- */
		$html .= $this->getJsSelect("Cheese", $entityName, $portionEntityName, $tablePortionEntities, count($globalEntities));

		return $html;
	}

	public function getJsSelect(
		string $name,
		string $entityName,
		string $portionEntityName,
		array  $tablePortionEntities,
		int    $totalElements): string {
		$tableColors = $this->getTableColor();

		/* Creation du select des Data pour le JS (canvas) */
		/* --------------------------------------------------- */
		$html = sprintf('<select style="visibility: hidden" id="%s%s%sData">', $entityName, $portionEntityName, $name);
		for ($i = 0; $i < count($tablePortionEntities); $i++) {
			$tablePortionEntitiesName = strtoupper($tablePortionEntities[$i][0]);
			if (strpos($tablePortionEntitiesName, ' ') != false)
				$tablePortionEntitiesName = substr($tablePortionEntitiesName, 0, strpos($tablePortionEntitiesName, ' '));

			if ($totalElements > 0) {
				$html .= sprintf('<option value="%s">%s</option>',
					number_format($tablePortionEntities[$i][1] / $totalElements * 100, 2), $tablePortionEntitiesName);
			} else {
				$html .= sprintf('<option value="0">%s</option>', $tablePortionEntitiesName);
			}
		}
		$html .= sprintf('</select>');

		/* Creation du select des couleurs pour le JS (canvas) */
		/* --------------------------------------------------- */
		$html .= sprintf('<select style="visibility: hidden" id="%s%s%sColor">', $entityName, $portionEntityName, $name);
		$j = 0;
		for ($i = 0; $i < count($tablePortionEntities); $i++) {
			if ($j >= count($tableColors))
				$j = 0;
			$html .= sprintf('<option value="%s">%s</option>', $tableColors[$j][1], $tableColors[$j][2]);
			$j++;
		}
		$html .= sprintf('</select>');

		return $html;
	}

	/**
	 * @param array $elements
	 * @param integer $totalElements
	 * @param boolean $multiColumn
	 * @return string
	 * @throws
	 */
	public function getLegend(array $elements, int $totalElements, bool $multiColumn = true): string {
		$tableColors = $this->getTableColor();

		$html = sprintf('<div class="col-sm-12 el-legend">');
		$html .= sprintf('<div class="row">');
		if ($multiColumn == true) {
			$html .= sprintf('<div class="col-sm-6">');
		} else {
			$html .= sprintf('<div class="col-sm-12">');
		}
		$j = 0;

		for ($i = 0; $i < count($elements); $i++) {
			if ($j >= count($tableColors))
				$j = 0;
			if ($multiColumn == true) {
				if (($i == count($elements) / 2) || (($i > count($elements) / 2) && ($i <= count($elements) / 2 + 0.5))) {
					$html .= sprintf('</div>');
					$html .= sprintf('<div class="col-sm-6">');
				}
			}
			$html .= sprintf('<div class="legend-value-w">');
			$html .= sprintf('  <div class="legend-pin" style="background-color: %s;"></div>', $tableColors[$j][1]);

			/* Rajout d'un retour charriot si le nom est trop long */
			$name = $elements[$i][0];
			if ($multiColumn == true) {
				if (strlen($name) > 30) {
					if (($pos = strrpos($name, " ")) !== false) {
						$name = substr_replace($elements[$i][0], "<br>", $pos, 1);
					}
				}
			}

			$elementRate = 0;
			if ($totalElements)
				$elementRate = $elements[$i][1] / $totalElements * 100;

			$html .= sprintf('  <div class="legend-value"><span>%s: (%s/%s) </span><span>%s%%</span></div>',
				$name,
				$elements[$i][1],
				$totalElements,
				number_format($elementRate, 2));
			$html .= '</div>'; //fin legend-value
			$j++;
		}
		$html .= '</div>'; //fin col
		$html .= '</div>'; //fin row
		$html .= '</div>'; //fin el-legend

		return $html;
	}

}
