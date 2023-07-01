<?php

namespace App\Twig;

use App\Entity\SatisfactionSearch;
use App\Entity\School;
use App\Repository\ActivityRepository;
use App\Repository\CompanyRepository;
use App\Repository\SchoolRepository;
use App\Repository\ContractRepository;
use App\Repository\CountryRepository;
use App\Repository\DegreeRepository;
use App\Repository\JobNotFoundReasonRepository;
use App\Repository\LegalStatusRepository;
use App\Repository\PersonDegreeRepository;
use App\Repository\RegionRepository;
use App\Repository\SatisfactionCompanyRepository;
use App\Repository\SatisfactionCreatorRepository;
use App\Repository\SatisfactionSalaryRepository;
use App\Repository\SatisfactionSearchRepository;
use App\Repository\SectorAreaRepository;
use App\Tools\Utils;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Translation\Translator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DashboardExtension extends AbstractExtension {
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    private RegionRepository $regionRepository;
    private CompanyRepository $companyRepository;
    private SchoolRepository $schoolRepository;
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
    private DegreeRepository $degreeRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        RegionRepository $regionRepository,
        CompanyRepository $companyRepository,
        SchoolRepository $schoolRepository,
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
        ContractRepository $contractRepository,
        DegreeRepository $degreeRepository,
    ) {
        $this->entityManager = $entityManager;
        $this->regionRepository = $regionRepository;
        $this->companyRepository = $companyRepository;
        $this->schoolRepository = $schoolRepository;
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
        $this->degreeRepository = $degreeRepository;
    }

    public function getFunctions(): array {
        return [
            new TwigFunction('company_satisfaction_skill_rate', [$this, 'companySatisfactionSkillRate'], ['is_safe' => ['html']]),
            new TwigFunction('company_satisfaction_hire_rate', [$this, 'companySatisfactionHireRate'], ['is_safe' => ['html']]),
            new TwigFunction('person_degree_activities_rate', [$this, 'personDegreeActivitiesRate'], ['is_safe' => ['html']]),
            new TwigFunction('person_degree_situation_rate', [$this, 'personDegreeSituationRate'], ['is_safe' => ['html']]),
            new TwigFunction('person_degree_unemployed_pursuit_last_degree_rate', [$this, 'personDegreeUnemployedPursuitLastDegreeRate'], ['is_safe' => ['html']]),
            new TwigFunction('person_degree_reason_graph', [$this, 'personDegreeReasonGraph'], ['is_safe' => ['html']]),
            new TwigFunction('entity_barre_graph', [$this, 'entityBarreGraph'], ['is_safe' => ['html']]),
            new TwigFunction('entity_portion_cheese', [$this, 'EntityPortionCheese'], ['is_safe' => ['html']]),
            new TwigFunction('convert_date_from_duration', [$this, 'convertDateFromDuration'], ['is_safe' => ['html']]),
            new TwigFunction('nb_actor_evolution', [$this, 'nbActorEvolution'], ['is_safe' => ['html']]),
        ];
    }

    public function getTableColor(): array {
        $tableColors = [];
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

    public function companySatisfactionHireRate(
        int $idCountry,
        int $idRegion,
        string $personType,
        array $skillLevels,
        DateTime $beginDate,
        DateTime $endDate
    ): string {
        $region = $this->regionRepository->find($idRegion);
        $country = $this->countryRepository->find($idCountry);

        if (!$region) {
            $companies = $this->companyRepository->getByCountryBetweenCreatedDateAndEndDate($country, $beginDate, $endDate);
        } else {
            $companies = $this->companyRepository->getByRegionBetweenCreatedDateAndEndDate($region, $beginDate, $endDate);
        }

        $html = '';
        $nbSatisfactionCompany = 0;

        // création tableau pour stokage du nombre de chaque satisfactions
        $tableSatisfactions = [];
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

    public function companySatisfactionSkillRate(
        int $idCountry,
        int $idRegion,
        string $skillName,
        array $skillLevels,
        ?DateTime $beginDate,
        ?DateTime $endDate): string {

        $region = $this->regionRepository->find($idRegion);
        $country = $this->countryRepository->find($idCountry);
        $companies = ($region) ?
            $this->companyRepository->getByRegionBetweenCreatedDateAndEndDate($region, $beginDate, $endDate) :
            $this->companyRepository->getByCountryBetweenCreatedDateAndEndDate($country, $beginDate, $endDate);

        $html = '';
        $nbSatisfactionCompany = 0;

        // création tableau pour stokage du nombre de chaque satisfactions
        $tableSatisfactions = [];
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

    public function personDegreeSituationRate(
        int       $idCountry,
        int       $idRegion,
        array     $situations,
        ?School    $school,
        DateTime $beginDate,
        DateTime $endDate): string {
        $region = $this->regionRepository->find($idRegion);
        $country = $this->countryRepository->find($idCountry);

        if (!$region) {
            $personDegrees = ($school) ?
                $this->personDegreeRepository->getByCountryAndSchoolBetweenCreatedDateAndEndDate($country, $school, $beginDate, $endDate) :
                $this->personDegreeRepository->getByCountryBetweenCreatedDateAndEndDate($country, $beginDate, $endDate);
        } else {
            $personDegrees = ($school) ?
                $this->personDegreeRepository->getByRegionAndSchoolBetweenCreatedDateAndEndDate($region, $school, $beginDate, $endDate) :
                $this->personDegreeRepository->getByRegionBetweenCreatedDateAndEndDate($region, $beginDate, $endDate);
        }

        $situationsPersonDegrees = [];
        foreach ($situations as $situation) {
            $situationPersonDegrees = ($school) ?
                $this->personDegreeRepository->getByTypeAndSchoolBetweenCreatedDateAndEndDate($situation, $school, $beginDate, $endDate) :
                $this->personDegreeRepository->getByTypeBetweenCreatedDateAndEndDate($situation, $beginDate, $endDate);

            // Stockage du diplômé
            if (!$region) {
                foreach ($situationPersonDegrees as $situationPersonDegree) {
                    if ($situationPersonDegree->getCountry()->getId() == $idCountry)
                        $situationsPersonDegrees[] = $situationPersonDegree;
                }
            } else {
                foreach ($situationPersonDegrees as $situationPersonDegree) {
                    //if ($situationPersonDegree->getRegion()->getId() == $idRegion)
                    //$situationsPersonDegrees[] = $situationPersonDegree;
                }
            }
        }

        $situationsPersonDegreesRate = 0;
        if (count($personDegrees) > 0)
            $situationsPersonDegreesRate = count($situationsPersonDegrees) / count($personDegrees) * 100;

        return sprintf('<div><table><tr><td><span class="small">(%s/%s)</span></td><td><span> %s%%</span></td></tr></table></div>',
            count($situationsPersonDegrees),
            count($personDegrees),
            number_format($situationsPersonDegreesRate, 2, ',', ' '));
    }

    public function personDegreeUnemployedPursuitLastDegreeRate(
        int $idCountry,
        int $idRegion,
        bool $lien,
        ?School $school,
        DateTime $beginDate,
        DateTime $endDate
    ): string {
        $country = $this->countryRepository->find($idCountry);
        $region = $this->regionRepository->find($idRegion);

        if (!$region) {
            $StudyPersonDegrees = ($school) ?
                $this->personDegreeRepository->getByCountryAndTypeAndSchoolBetweenCreatedDateAndEndDate($country, 'TYPE_STUDY', $school, $beginDate, $endDate) :
                $this->personDegreeRepository->getByCountryAndTypeBetweenCreatedDateAndEndDate($country, 'TYPE_STUDY', $beginDate, $endDate);
        } else {
            $StudyPersonDegrees = ($school) ?
                $this->personDegreeRepository->getByRegionAndTypeAndSchoolBetweenCreatedDateAndEndDate($region, 'TYPE_STUDY', $school, $beginDate, $endDate) :
                $this->personDegreeRepository->getByRegionAndTypeBetweenCreatedDateAndEndDate($region, 'TYPE_STUDY', $beginDate, $endDate);
        }

        $SatisfactionPersonDegrees = [];
        $situationsPersonDegrees = [];

        // Recherche si la poursuite d'étude est en lien avec le premier diplôme dans le questionnaire de satisfaction
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
    public function personDegreeReasonRate(array $personDegrees, string $personTypeName): array {
        $reasons = $this->jobNotFoundReasonRepository->findAll();

        // Création tableau pour stokage du nombre de chaque raison
        $tableReasons = [];
        foreach ($reasons as $reason) {
            $reason_temp = [$reason->getName(), 0];
            $tableReasons[] = $reason_temp;
        }
        $reason_temp = ["autre raison", 0];
        $tableReasons[] = $reason_temp;

        // Recherche le nombre de raisons de chômage dans l'ensemble des diplômés dans le questionnaire de satisfaction
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
        $tableActivities = [];
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

    public function personDegreeReasonGraph(
        int    $idCountry,
        int    $idRegion,
        string $objectRateName,
        array  $situations,
        string $personTypeName,
        bool   $multiColumn,
        ?School $school,
        DateTime $beginDate,
        DateTime $endDate): string {
        $personDegrees = [];
        $country = $this->countryRepository->find($idCountry);
        $region = $this->regionRepository->find($idRegion);

        foreach ($situations as $situation) {
            if (!$region) {
                $situationPersonDegrees = ($school) ?
                    $this->personDegreeRepository->getByCountryAndTypeAndSchoolBetweenCreatedDateAndEndDate($country, $situation, $school, $beginDate, $endDate) :
                    $this->personDegreeRepository->getByCountryAndTypeBetweenCreatedDateAndEndDate($country, $situation, $beginDate, $endDate);
            } else {
                $situationPersonDegrees = ($school) ?
                    $this->personDegreeRepository->getByRegionAndTypeAndSchoolBetweenCreatedDateAndEndDate($region, $situation, $school, $beginDate, $endDate) :
                    $this->personDegreeRepository->getByRegionAndTypeBetweenCreatedDateAndEndDate($region, $situation, $beginDate, $endDate);
            }

            foreach ($situationPersonDegrees as $situationPersonDegree) {
                $personDegrees[] = $situationPersonDegree;
            }
        }

        $objectRate = [];
        if ($objectRateName == 'Reason') {
            $objectRate = $this->personDegreeReasonRate($personDegrees, $personTypeName);
        } elseif ($objectRateName == 'Activity') {
            $objectRate = $this->personDegreeActivityRate($personDegrees, $personTypeName);
        }

        // Ecriture du canvas graph
        $html = sprintf('<div class="row element-box element-box-reduct">');
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

    public function entityBarreGraph(
        int    $idCountry,
        int    $idRegion,
        string $entityName,
        string $objectBarreName,
        bool   $multiColumn,
        DateTime $beginDate,
        DateTime $endDate): string
    {
        $globalEntities = [];
        $barreEntities = [];
        $barreNbEntities = [];

        $country = $this->countryRepository->find($idCountry);
        $region = $this->regionRepository->find($idRegion);

        if ($entityName == 'School') {
            $globalEntities = ($region) ?
                $this->schoolRepository->getByRegionBetweenCreatedDateAndEndDate($region, $beginDate, $endDate) :
                $this->schoolRepository->getByCountryBetweenCreatedDateAndEndDate($country, $beginDate, $endDate);
        }

        if ($objectBarreName == 'SectorArea') {
            $barreEntities = $this->sectorAreaRepository->findAll();
        }

        // Compte le nombre d'établissements par Secteur
        $index=0;
        foreach ($barreEntities as $barreEntity) {
            $nbEntities = 0;
            foreach ($globalEntities as $globalEntity) {
                if ($barreEntity->getId() == $globalEntity->getSectorArea1()->getId()) {
                    $nbEntities++;
                }

                if ($globalEntity->getSectorArea2())
                    if ($barreEntity->getId() == $globalEntity->getSectorArea2()->getId()) {
                        $nbEntities++;
                    }
                if ($globalEntity->getSectorArea3())
                    if ($barreEntity->getId() == $globalEntity->getSectorArea3()->getId()) {
                        $nbEntities++;
                    }
                if ($globalEntity->getSectorArea4())
                    if ($barreEntity->getId() == $globalEntity->getSectorArea4()->getId()) {
                        $nbEntities++;
                    }
                if ($globalEntity->getSectorArea5())
                    if ($barreEntity->getId() == $globalEntity->getSectorArea5()->getId()) {
                        $nbEntities++;
                    }
                if ($globalEntity->getSectorArea6())
                    if ($barreEntity->getId() == $globalEntity->getSectorArea6()->getId()) {
                        $nbEntities++;
                    }
            }
            $barreNbEntities[$index][0] = $barreEntity->getName();
            $barreNbEntities[$index][1] = $nbEntities;
            $index++;
        }

        // Ecriture du canvas graph
        $html = sprintf('<div class="row element-box element-box-reduct">');
        if ($objectBarreName == "Sector") {
            $html .= sprintf('<div class="col-sm-3 el-chart-w2">');
            $html .= sprintf('<canvas height="100px" id="%s%sGraph" ></canvas>', $entityName, $objectBarreName);
        } else {
            $html .= sprintf('<div class="col-sm-4 el-chart-w">');
            $html .= sprintf('<canvas height="100px" id="%s%sGraph" width="80px"></canvas>', $entityName, $objectBarreName);
        }

        $html .= sprintf('  <p style="text-align: center"><strong>%d</strong>', count($globalEntities));

        if ($entityName == "School") {
            $html .= sprintf(' Total Etablissements');
        }
        $html .= sprintf('</p>');

        $html .= sprintf('</div>'); //fin div el-chart-w

        /* Ecriture de la légende */
        /* ---------------------- */
        if ($objectBarreName == "SectorArea") {
            $html .= sprintf('<div class="col-sm-9 legend_activity">');
        }

        $html .= $this->getLegend($barreNbEntities, count($globalEntities), $multiColumn);
        $html .= sprintf('</div>'); //fin legend_activity
        $html .= sprintf('</div>'); //fin element-box

        /* Creation des select pour le JS (canvas) */
        /* --------------------------------------- */
        $html .= $this->getJsSelect("Graph", $entityName, $objectBarreName, $barreNbEntities, count($globalEntities));

        return $html;
    }

    public function EntityPortionCheese(
        int    $idCountry,
        int    $idRegion,
        string $entityName,
        string $portionEntityName,
        bool   $multiColumn,
        ?School $school,
        DateTime $beginDate,
        DateTime $endDate): string {
        $globalEntities = [];
        $portionEntities = [];

        $country = $this->countryRepository->find($idCountry);
        $region = $this->regionRepository->find($idRegion);

        if ($entityName == 'School') {
            $globalEntities = ($region) ?
                $this->schoolRepository->getByRegionBetweenCreatedDateAndEndDate($region, $beginDate, $endDate) :
                $this->schoolRepository->getByCountryBetweenCreatedDateAndEndDate($country, $beginDate, $endDate);
        } elseif ($entityName == 'Company') {
            $globalEntities = ($region) ?
                $this->companyRepository->getByRegionBetweenCreatedDateAndEndDate($region, $beginDate, $endDate) :
                $this->companyRepository->getByCountryBetweenCreatedDateAndEndDate($country, $beginDate, $endDate);
        } elseif ($entityName == 'PersonDegree') {
            if ($portionEntityName == 'Contract') {
                if (!$region) {
                    $globalEntities = ($school) ?
                        $this->personDegreeRepository->getByCountryAndTypeAndSchoolBetweenCreatedDateAndEndDate($country, 'TYPE_EMPLOYED', $school, $beginDate, $endDate) :
                        $this->personDegreeRepository->getByCountryAndTypeBetweenCreatedDateAndEndDate($country, 'TYPE_EMPLOYED', $beginDate, $endDate);
                } else {
                    $globalEntities = ($school) ?
                        $this->personDegreeRepository->getByRegionAndTypeAndSchoolBetweenCreatedDateAndEndDate($region, 'TYPE_EMPLOYED', $school, $beginDate, $endDate) :
                        $this->personDegreeRepository->getByRegionAndTypeBetweenCreatedDateAndEndDate($region, 'TYPE_EMPLOYED', $beginDate, $endDate);
                }
            } else {
                if (!$region) {
                    $globalEntities = ($school) ?
                        $this->personDegreeRepository->getByCountryAndSchoolBetweenCreatedDateAndEndDate($country, $school, $beginDate, $endDate):
                        $this->personDegreeRepository->getByCountryBetweenCreatedDateAndEndDate($country, $beginDate, $endDate) ;
                } else {
                    $globalEntities = ($school) ?
                        $this->personDegreeRepository->getByRegionAndSchoolBetweenCreatedDateAndEndDate($region, $school, $beginDate, $endDate) :
                        $this->personDegreeRepository->getByRegionBetweenCreatedDateAndEndDate($region, $beginDate, $endDate);
                }
            }
        }
        if ($portionEntityName == 'SectorArea') {
            $portionEntities = $this->sectorAreaRepository->findAll();
        } elseif ($portionEntityName == 'LegalStatus') {
            $portionEntities = $this->legalStatusRepository->findAll();
        } elseif ($portionEntityName == 'Contract') {
            $portionEntities = $this->contractRepository->findAll();
        } elseif ($portionEntityName == 'Degree') {
            $portionEntities = $this->degreeRepository->findAll();
        }

        /* Cas du Contract: Suppression des diplômés qui n'ont pas répondu à la satisfactionSearch */
        if ($portionEntityName == 'Contract') {
            for ($i = 0; $i < count($globalEntities); $i++) {
                $satisfaction = $this->satisfactionSalaryRepository->getLastSatisfaction($globalEntities[$i]);
                if (!$satisfaction) {
                    array_splice($globalEntities, $i, 1);
                    $i--;
                }
            }
        }

        /* création tableau pour stokage du nombre de chaque raison */
        $tablePortionEntities = [];
        foreach ($portionEntities as $portionEntity) {
            $portions = [];
            if ($entityName == 'School') {
                if ($portionEntityName == 'SectorArea') {
                    $portions = ($region) ?
                        $this->schoolRepository->getByRegionAndSectorAreaBetweenCreatedDateAndEndDate($region, $portionEntity, $beginDate, $endDate) :
                        $this->schoolRepository->getByCountryAndSectorAreaBetweenCreatedDateAndEndDate($country, $portionEntity, $beginDate, $endDate);
                }
            } elseif ($entityName == 'Company') {
                if ($portionEntityName == 'SectorArea') {
                    $portions = ($region) ?
                        $this->companyRepository->getByRegionAndSectorAreaBetweenCreatedDateAndEndDate($region, $portionEntity, $beginDate, $endDate) :
                        $this->companyRepository->getByCountryAndSectorAreaBetweenCreatedDateAndEndDate($country, $portionEntity, $beginDate, $endDate);
                } elseif ($portionEntityName == 'LegalStatus') {
                    $portions = ($region) ?
                        $this->companyRepository->getByRegionAndLegalStatusBetweenCreatedDateAndEndDate($region, $portionEntity, $beginDate, $endDate) :
                        $this->companyRepository->getByCountryAndLegalStatusBetweenCreatedDateAndEndDate($country, $portionEntity, $beginDate, $endDate);
                }
            } elseif ($entityName == 'PersonDegree') {
                if($portionEntityName == 'SectorArea') {
                    if (!$region) {
                        $portions = ($school) ?
                            $this->personDegreeRepository->getByCountryAndSectorAreaAndSchoolBetweenCreatedDateAndEndDate($country, $portionEntity, $school, $beginDate, $endDate) :
                            $this->personDegreeRepository->getByCountryAndSectorAreaBetweenCreatedDateAndEndDate($country, $portionEntity, $beginDate, $endDate);
                    } else {
                        $portions = ($school) ?
                            $this->personDegreeRepository->getByRegionAndSectorAreaAndSchoolBetweenCreatedDateAndEndDate($region, $portionEntity, $school, $beginDate, $endDate) :
                            $this->personDegreeRepository->getByRegionAndSectorAreaBetweenCreatedDateAndEndDate($region, $portionEntity, $beginDate, $endDate);
                    }
                } elseif ($portionEntityName == 'Degree') {
                    if (!$region) {
                        $portions = ($school) ?
                            $this->personDegreeRepository->getByCountryAndDegreeAndSchoolBetweenCreatedDateAndEndDate($country, $portionEntity, $school, $beginDate, $endDate) :
                            $this->personDegreeRepository->getByCountryAndDegreeBetweenCreatedDateAndEndDate($country, $portionEntity, $beginDate, $endDate);
                    } else {
                        $portions = ($school) ?
                            $this->personDegreeRepository->getByRegionAndDegreeAndSchoolBetweenCreatedDateAndEndDate($region, $portionEntity, $school, $beginDate, $endDate) :
                            $this->personDegreeRepository->getByRegionAndDegreeBetweenCreatedDateAndEndDate($region, $portionEntity, $beginDate, $endDate);
                    }

                } elseif ($portionEntityName == 'Contract') {
                    // recupération type de contrat du salarié dans le questionnaire de satisfaction
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
            // $totalGraduates = 'dashboard.total_graduates';
            if ($portionEntityName == "Contract") {
                $html .= sprintf('  <span>Total Diplômés<br>Salariés</span>');
                // $html .= sprintf('  <span>' . $totalGraduates. '<br>Salariés</span>');
            } else {
                $html .= sprintf('  <span>Total Diplômés</span>');
                // $html .= sprintf('  <span>' . $totalGraduates. '</span>');
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

            $html .= sprintf('  <div class="legend-value">
                                        <span >%s: (%s/%s) </span>
                                        <span>%s%%</span></div>',
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

    public function convertDateFromDuration(string $endDate, string $duration): array {
        $datas = explode(' ', $duration);
        $beginDuration = new \DateTime($endDate);
        $endDuration = new \DateTime($endDate);

        // set $endDuration to the end of the day to have the lasted changed
        $endDuration = $endDuration->modify('+23 hour')->modify('+59 minute');

        if (count($datas) == 2) {
            if ($datas[1] == 'mois') {
                $beginDuration = $beginDuration->sub(new DateInterval('P' . $datas[0] . 'M'));
            } elseif (strncmp($datas[1], 'an', 2) == 0) {
                $beginDuration = $beginDuration->sub(new DateInterval('P' . $datas[0] . 'Y'));
            }
        }
        return [$beginDuration, $endDuration];
    }

    public function nbActorEvolution(
        string   $type,
        int      $idCountry,
        int      $idRegion,
        string   $actor,
        string   $title,
        string   $duration,
        ?School   $school,
        \DateTime $beginDate,
        \DateTime $endDate): string
    {
        $country = $this->countryRepository->find($idCountry);
        $region = $this->regionRepository->find($idRegion);

        $dates = [];
        $legend = [];

        if( $actor == "PersonDegree") {
            $personDegrees = [];
            if ($region) {
                $personDegrees = ($school) ?
                    $this->personDegreeRepository->getByRegionAndSchool($region, $school) :
                    $this->personDegreeRepository->findByRegion($region);
            } else if ($country) {
                $personDegrees = ($school) ?
                    $this->personDegreeRepository->getByCountryAndSchool($country, $school) :
                    $this->personDegreeRepository->findByCountry($country);
            }

            // selection des dernières satisfactions de chaque apres la date $beginDuration
            if ($type == 'Profile') {
                foreach ($personDegrees as $personDegree) {
                    if (($personDegree->getCreatedDate() >= $beginDate) && ($personDegree->getCreatedDate() <= $endDate)) {
                        $dates[] = $personDegree->getCreatedDate();
                    }
                }

            } elseif ($type == 'Quizz') {
                foreach ($personDegrees as $personDegree) {
                    $satisfactionFound = false;
                    /** @var SatisfactionSearch $satisfactionSearch */
                    $satisfactionSearch = $this->satisfactionSearchRepository->getLastSatisfaction($personDegree);

                    if ($satisfactionSearch) {
                        if (($satisfactionSearch->getUpdatedDate() >= $beginDate) && ($satisfactionSearch->getUpdatedDate() <= $endDate)) {
                            $dates[] = $satisfactionSearch->getUpdatedDate();
                            $satisfactionFound = true;
                        }
                    }

                    if ($satisfactionFound) {
                        $satisfactionSalary = $this->satisfactionSalaryRepository->getLastSatisfaction($personDegree);
                        if ($satisfactionSalary) {
                            if (($satisfactionSalary->getUpdatedDate() >= $beginDate) && ($satisfactionSalary->getUpdatedDate() <= $endDate)) {
                                $dates[] = $satisfactionSearch->getUpdatedDate();
                                $satisfactionFound = true;
                            }
                        }
                    }

                    if ($satisfactionFound) {
                        $satisfactionCreator = $this->satisfactionCreatorRepository->getLastSatisfaction($personDegree);
                        if ($satisfactionCreator) {
                            if (($satisfactionCreator->getUpdatedDate() >= $beginDate) && ($satisfactionCreator->getUpdatedDate() <= $endDate)) {
                                $dates[] = $satisfactionSearch->getUpdatedDate();
                            }
                        }
                    }
                }
            }
        } else if ($actor == 'Company') {
            $Companies = [];
            if ($region) {
                $Companies = $this->companyRepository->findByRegion($region);
            } else if ($country) {
                $Companies = $this->companyRepository->findByCountry($country);
            }

            // selection des dernières satisfactions de chaque apres la date $beginDuration
            if ($type == 'Profile') {
                foreach ($Companies as $Company) {
                    if (($Company->getCreatedDate() >= $beginDate) && ($Company->getCreatedDate() <= $endDate))
                        $dates[] = $Company->getCreatedDate();
                }

            } elseif ($type == 'Quizz') {
                foreach ($Companies as $Company) {
                    $satisfactionCompany = $this->satisfactionCompanyRepository->getLastSatisfaction($Company);
                    if ($satisfactionCompany) {
                        if (($satisfactionCompany->getUpdatedDate() >= $beginDate) && ($satisfactionCompany->getUpdatedDate() <= $endDate)) {
                            $dates[] = $satisfactionCompany->getUpdatedDate();
                        }
                    }
                }
            }
        } else if ($actor == 'School') {
            $Schools = [];
            if ($region) {
                $Schools = $this->schoolRepository->findByRegion($region);
            } else if ($country) {
                $Schools = $this->schoolRepository->findByCountry($country);
            }

            // selection des dernières satisfactions de chaque apres la date $beginDuration
            if ($type == 'Profile') {
                foreach ($Schools as $School) {
                    if (($School->getCreatedDate() >= $beginDate) && ($School->getCreatedDate() <= $endDate))
                        $dates[] = $School->getCreatedDate();
                }
            }
        }

        // Initialisation des variables
        asort($dates);
        $suffixLegend = '';
        $intervalDuration = 0; //en jours
        $nbXInterval = 0; // nombre de données sur l'axe des X

        // Creation des tables pour l'affichage graphique du graphe
        if ($duration == '3 mois') { // Affichage 12 semaines (84 jours)
            $suffixLegend = 'S';  //semaine
            $intervalDuration = 7; //1 semaine
            $nbXInterval = 12;
        } elseif ($duration == '6 mois') {
            $suffixLegend = 'BS'; //Bi-semaine
            $intervalDuration = 14; //2 semaines
            $nbXInterval = 13;
        } elseif ($duration == '1 an') {
            $suffixLegend = 'M'; //mois
            $intervalDuration = 30; //1 mois
            $nbXInterval = 12;
        } elseif ($duration == '2 ans') {
            $suffixLegend = 'BM'; //mois
            $intervalDuration = 60; //1 mois
            $nbXInterval = 13;
        }

        $resultats = [];
        for ($i = 1; $i < ($nbXInterval + 1); $i++) {
            $resultats[] = 0;
            $legend[] = $suffixLegend . $i;
        }

        foreach ($dates as $date) {
            $startDate = clone $beginDate;
            $stopDate = (clone $beginDate)->add(new \DateInterval('P' . $intervalDuration . 'D'));
            for ($i = 0; $i < $nbXInterval; $i++) {
                if (($date >= $startDate) && ($date <= $stopDate)) {
                    $resultats[$i]++;
                }
                $startDate = $startDate->add(new \DateInterval('P' . $intervalDuration . 'D'));
                $stopDate = $stopDate->add(new \DateInterval('P' . $intervalDuration . 'D'));
            }
        }

        $timeSpace = 'Du ' . $beginDate->format(Utils::FORMAT_FR) . ' au ' . $endDate->format(Utils::FORMAT_FR);

        $html = sprintf('<div class="label">%s : %s </div>', $title, $timeSpace);

        // ecriture du nombre d'acteurs
        $nb_actors = (string)array_sum($resultats);
        $html .= sprintf('<div class="value">%s</div>', $nb_actors);

        // Ecriture du select
        $html .= sprintf('<select id="%s%sDataChart" style="visibility: hidden" >', $type, $actor);
        for ($i = 0; $i < $nbXInterval; $i++) {
            $html .= sprintf('<option value="%s">%s</option>', $legend[$i], $resultats[$i]);
        }
        $html .= sprintf('</select>');


        return $html;
    }

}
