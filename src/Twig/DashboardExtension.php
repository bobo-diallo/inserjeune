<?php

namespace App\Twig;

use App\Entity\PersonDegree;
use App\Entity\SatisfactionSalary;
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
use App\Repository\PrefectureRepository;
use App\Repository\SatisfactionCompanyRepository;
use App\Repository\SatisfactionCreatorRepository;
use App\Repository\SatisfactionSalaryRepository;
use App\Repository\SatisfactionSearchRepository;
use App\Repository\SectorAreaRepository;
use App\Tools\Utils;
use DateInterval;
use DateTime;
use phpDocumentor\Reflection\Type;
use Symfony\Component\Validator\Constraints\Date;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DashboardExtension extends AbstractExtension {
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    private RegionRepository $regionRepository;
    private PrefectureRepository $prefectureRepository;
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
	private TranslatorInterface $translator;

	public function __construct(
        EntityManagerInterface $entityManager,
        RegionRepository $regionRepository,
        PrefectureRepository $prefectureRepository,
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
        TranslatorInterface $translator
    ) {
        $this->entityManager = $entityManager;
        $this->regionRepository = $regionRepository;
        $this->prefectureRepository = $prefectureRepository;
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
		$this->translator = $translator;
	}

    public function getFunctions(): array {
        return [
            new TwigFunction('company_satisfaction_skill_rate', [$this, 'companySatisfactionSkillRate'], ['is_safe' => ['html']]),
            new TwigFunction('company_satisfaction_hire_rate', [$this, 'companySatisfactionHireRate'], ['is_safe' => ['html']]),
            new TwigFunction('person_degree_activities_rate', [$this, 'personDegreeActivitiesRate'], ['is_safe' => ['html']]),
            new TwigFunction('person_degree_situation_rate', [$this, 'personDegreeSituationRate'], ['is_safe' => ['html']]),
            new TwigFunction('person_degree_survey_rate', [$this, 'personDegreeSurveyRate'], ['is_safe' => ['html']]),
            new TwigFunction('graduate_global_integration_rate', [$this, 'graduateGlobalIntegrationRate'], ['is_safe' => ['html']]),
            new TwigFunction('employement_rate', [$this, 'employementRate'], ['is_safe' => ['html']]),
            new TwigFunction('person_degree_period_enrollment_rate', [$this, 'personDegreePeriodEnrollmentRate'], ['is_safe' => ['html']]),
            new TwigFunction('person_degree_type_in_types_rate', [$this, 'personDegreeTypeInTypesRate'], ['is_safe' => ['html']]),
            new TwigFunction('person_degree_continuing_their_studies', [$this, 'personDegreeContinuingTheirStudies'], ['is_safe' => ['html']]),
            new TwigFunction('person_degree_unemployed_pursuit_last_degree_rate', [$this, 'personDegreeUnemployedPursuitLastDegreeRate'], ['is_safe' => ['html']]),
            new TwigFunction('person_degree_reason_graph', [$this, 'personDegreeReasonGraph'], ['is_safe' => ['html']]),
            new TwigFunction('entity_barre_graph', [$this, 'entityBarreGraph'], ['is_safe' => ['html']]),
            new TwigFunction('entity_portion_cheese', [$this, 'EntityPortionCheese'], ['is_safe' => ['html']]),
            new TwigFunction('convert_date_from_duration', [$this, 'convertDateFromDuration'], ['is_safe' => ['html']]),
            new TwigFunction('nb_actor_evolution', [$this, 'nbActorEvolution'], ['is_safe' => ['html']]),
            new TwigFunction('diff_date_ymd', [$this, 'diffDateYmd'], ['is_safe' => ['html']]),
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
        ?int $idPrefecture,
        string $personType,
        array $skillLevels,
        DateTime $beginDate,
        DateTime $endDate
    ): string {
        $region = $this->regionRepository->find($idRegion);
        $country = $this->countryRepository->find($idCountry);
        $companies = null;
        if($_ENV['PREFECTURE_BETWEEN_REGION_CITY'] == 'true') {
            $prefecture = $this->prefectureRepository->find($idPrefecture);
            if($prefecture) {
                $companies = $this->companyRepository->getByPrefectureBetweenCreatedDateAndEndDate($prefecture, $beginDate, $endDate);
            }
        }

        if(!$companies) {
            if (!$region) {
                $companies = $this->companyRepository->getByCountryBetweenCreatedDateAndEndDate($country, $beginDate, $endDate);
            } else {
                $companies = $this->companyRepository->getByRegionBetweenCreatedDateAndEndDate($region, $beginDate, $endDate);
            }
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
        ?int $idPrefecture,
        string $skillName,
        array $skillLevels,
        ?DateTime $beginDate,
        ?DateTime $endDate): string {

        $region = $this->regionRepository->find($idRegion);
        $country = $this->countryRepository->find($idCountry);
        $prefecture = null;
        $companies = null;
        if($_ENV['PREFECTURE_BETWEEN_REGION_CITY'] == 'true') {
            $prefecture = $this->prefectureRepository->find($idPrefecture);
            if($prefecture) {
                $companies = $this->companyRepository->getByPrefectureBetweenCreatedDateAndEndDate($prefecture, $beginDate, $endDate);
            }
        }

        if(!$companies) {
            $companies = ($region) ?
                $this->companyRepository->getByRegionBetweenCreatedDateAndEndDate($region, $beginDate, $endDate) :
                $this->companyRepository->getByCountryBetweenCreatedDateAndEndDate($country, $beginDate, $endDate);
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

    public function personDegreeTypeInTypesRate(
        int       $idCountry,
        int       $idRegion,
        ?int      $idPrefecture,
        string    $baseSituation,
        array     $otherSituations,
        ?School   $school,
        DateTime  $beginDate,
        DateTime  $endDate): string {
        $region = $this->regionRepository->find($idRegion);
        $country = $this->countryRepository->find($idCountry);
        $prefecture = $this->prefectureRepository->find($idPrefecture);
        $personDegrees = null;

        //test if $situation is in array of $otherSituations
        if (!in_array($baseSituation, $otherSituations)) {
            return sprintf('<div><table><tr><td><span class="small">(%s)</span></td><td><span></span></td></tr></table></div>',
                "Erreur de programmation, premier paramètre doit exister dans la table du 2ème paramètre");
        }

        $situationsPersonDegrees = [];
        foreach ($otherSituations as $situation) {
            $situationPersonDegrees = ($school) ?
                $this->personDegreeRepository->getByTypeAndSchoolBetweenCreatedDateAndEndDate($situation, $school, $beginDate, $endDate) :
                $this->personDegreeRepository->getByTypeBetweenCreatedDateAndEndDate($situation, $beginDate, $endDate);

            // Stockage du diplômé
            // if($_ENV['PREFECTURE_BETWEEN_REGION_CITY'] == 'true') {
            if ($prefecture) {
                foreach ($situationPersonDegrees as $situationPersonDegree) {
                    if ($situationPersonDegree->getAddressCity()) {
                        if ($situationPersonDegree->getAddressCity()->getPrefecture()) {
                            if ($situationPersonDegree->getAddressCity()->getPrefecture()->getId() == $idPrefecture) {
                                $situationsPersonDegrees[] = $situationPersonDegree;
                            }
                        }
                    }
                }
                // }
            } else {
                if (!$region) {
                    foreach ($situationPersonDegrees as $situationPersonDegree) {
                        if ($situationPersonDegree->getCountry()) {
                            if ($situationPersonDegree->getCountry()->getId() == $idCountry) {
                                $situationsPersonDegrees[] = $situationPersonDegree;
                            }
                        }
                    }
                } else {
                    foreach ($situationPersonDegrees as $situationPersonDegree) {
                        if ($situationPersonDegree->getRegion()) {
                            if ($situationPersonDegree->getRegion()->getId() == $idRegion) {
                                $situationsPersonDegrees[] = $situationPersonDegree;
                            }
                        }
                    }
                }
            }
        }

        $nbPersonDegree = 0;
        foreach ($situationsPersonDegrees as $situationsPersonDegree) {
            if ($situationsPersonDegree->getType() == $baseSituation)
                $nbPersonDegree++;
        }

        $situationsPersonDegreesRate = 0;
        if (count($situationsPersonDegrees) > 0)
            $situationsPersonDegreesRate = $nbPersonDegree / count($situationsPersonDegrees) * 100;

        return sprintf('<div><table><tr><td><span class="small">(%s/%s)</span></td><td><span> %s%%</span></td></tr></table></div>',
            $nbPersonDegree,
            count($situationsPersonDegrees),
            number_format($situationsPersonDegreesRate, 2, ',', ' '));
    }

    public function personDegreePeriodEnrollmentRate(
        int       $idCountry,
        int       $idRegion,
        ?int      $idPrefecture,
        ?School    $school,
        DateTime $beginDate,
        DateTime $endDate): string {
        $region = $this->regionRepository->find($idRegion);
        $country = $this->countryRepository->find($idCountry);
        $prefecture = $this->prefectureRepository->find($idPrefecture);
        $personDegrees = null;
        if($_ENV['PREFECTURE_BETWEEN_REGION_CITY'] == 'true') {
            if($prefecture) {
                $personDegrees = ($school) ?
                    $this->personDegreeRepository->getByPrefectureAndSchoolBetweenCreatedDateAndEndDate($prefecture, $school, $beginDate, $endDate) :
                    $this->personDegreeRepository->getByPrefectureBetweenCreatedDateAndEndDate($prefecture, $beginDate, $endDate);
            }
        }

        if(!$personDegrees) {
            if (!$region) {
                $personDegrees = ($school) ?
                    $this->personDegreeRepository->getByCountryAndSchoolBetweenCreatedDateAndEndDate($country, $school, $beginDate, $endDate) :
                    $this->personDegreeRepository->getByCountryBetweenCreatedDateAndEndDate($country, $beginDate, $endDate);
            } else {
                $personDegrees = ($school) ?
                    $this->personDegreeRepository->getByRegionAndSchoolBetweenCreatedDateAndEndDate($region, $school, $beginDate, $endDate) :
                    $this->personDegreeRepository->getByRegionBetweenCreatedDateAndEndDate($region, $beginDate, $endDate);
            }
        }

        $nbTotalPersonDegree = count($this->personDegreeRepository->findAll());

        $situationsPersonDegreesRate = 0;
        if (count($personDegrees) > 0)
            $situationsPersonDegreesRate = count($personDegrees) / $nbTotalPersonDegree * 100;

        return sprintf('<div><table><tr><td><span class="small">(%s/%s)</span></td><td><span> %s%%</span></td></tr></table></div>',
            count($personDegrees),
            $nbTotalPersonDegree,
            number_format($situationsPersonDegreesRate, 2, ',', ' '));
    }

    public function employementRate(
        int       $idCountry,
        int       $idRegion,
        ?int      $idPrefecture,
        array     $situations,
        ?School    $school,
        DateTime $beginDate,
        DateTime $endDate): string {
        $region = $this->regionRepository->find($idRegion);
        $country = $this->countryRepository->find($idCountry);
        $prefecture = $this->prefectureRepository->find($idPrefecture);

        $situationsPersonDegrees = [];
        foreach ($situations as $situation) {
            $personDegrees = ($school) ?
                $this->personDegreeRepository->getByTypeAndSchoolBetweenCreatedDateAndEndDate($situation, $school, $beginDate, $endDate) :
                $this->personDegreeRepository->getByTypeBetweenCreatedDateAndEndDate($situation, $beginDate, $endDate);

            if ($prefecture) {
                foreach ($personDegrees as $personDegree) {
                    if ($personDegree->getAddressCity()) {
                        if ($personDegree->getAddressCity()->getPrefecture()) {
                            if ($personDegree->getAddressCity()->getPrefecture()->getId() == $idPrefecture) {
                                $situationsPersonDegrees[] = $personDegree;
                            }
                        }
                    }
                }
            } else {
                if (!$region) {
                    foreach ($personDegrees as $personDegree) {
                        if ($personDegree->getCountry()) {
                            if ($personDegree->getCountry()->getId() == $idCountry) {
                                $situationsPersonDegrees[] = $personDegree;
                            }
                        }
                    }
                } else {
                    foreach ($personDegrees as $personDegree) {
                        if ($personDegree->getRegion()) {
                            if ($personDegree->getRegion()->getId() == $idRegion) {
                                $situationsPersonDegrees[] = $personDegree;
                            }
                        }
                    }
                }
            }
        }

        $nbGraduateIntegrated = 0;
        foreach ($situationsPersonDegrees as $situationsPersonDegree) {
            if($situationsPersonDegree->getType() == "TYPE_EMPLOYED" || $situationsPersonDegree->getType() == "TYPE_CONTRACTOR") {
                $nbGraduateIntegrated++;
            }
        }

        $situationsPersonDegreesRate = 0;
        if (count($situationsPersonDegrees) > 0)
            $situationsPersonDegreesRate = $nbGraduateIntegrated / count($situationsPersonDegrees)  * 100;

        return sprintf('<div><table><tr><td><span class="small">(%s/%s)</span></td><td><span> %s%%</span></td></tr></table></div>',
            $nbGraduateIntegrated,
            count($situationsPersonDegrees),
            number_format($situationsPersonDegreesRate, 2, ',', ' '));
    }

    public function graduateGlobalIntegrationRate(
        int       $idCountry,
        int       $idRegion,
        ?int      $idPrefecture,
        array     $situations,
        ?School    $school,
        DateTime $beginDate,
        DateTime $endDate): string {
        $region = $this->regionRepository->find($idRegion);
        $country = $this->countryRepository->find($idCountry);
        $prefecture = $this->prefectureRepository->find($idPrefecture);

        $situationsPersonDegrees = [];
        foreach ($situations as $situation) {
            $personDegrees = ($school) ?
                $this->personDegreeRepository->getByTypeAndSchoolBetweenCreatedDateAndEndDate($situation, $school, $beginDate, $endDate) :
                $this->personDegreeRepository->getByTypeBetweenCreatedDateAndEndDate($situation, $beginDate, $endDate);

            if ($prefecture) {
                foreach ($personDegrees as $personDegree) {
                    if ($personDegree->getAddressCity()) {
                        if ($personDegree->getAddressCity()->getPrefecture()) {
                            if ($personDegree->getAddressCity()->getPrefecture()->getId() == $idPrefecture) {
                                $situationsPersonDegrees[] = $personDegree;
                            }
                        }
                    }
                }
            } else {
                if (!$region) {
                    foreach ($personDegrees as $personDegree) {
                        if ($personDegree->getCountry()) {
                            if ($personDegree->getCountry()->getId() == $idCountry) {
                                $situationsPersonDegrees[] = $personDegree;
                            }
                        }
                    }
                } else {
                    foreach ($personDegrees as $personDegree) {
                        if ($personDegree->getRegion()) {
                            if ($personDegree->getRegion()->getId() == $idRegion) {
                                $situationsPersonDegrees[] = $personDegree;
                            }
                        }
                    }
                }
            }
        }

        $nbGraduateIntegrated = 0;
        foreach ($situationsPersonDegrees as $situationsPersonDegree) {
            if($situationsPersonDegree->getType() == "TYPE_EMPLOYED" || $situationsPersonDegree->getType() == "TYPE_CONTRACTOR") {
                $nbGraduateIntegrated++;
            }
        }

        $situationsPersonDegreesRate = 0;
        if (count($situationsPersonDegrees) > 0)
            $situationsPersonDegreesRate = $nbGraduateIntegrated / count($situationsPersonDegrees)  * 100;

        return sprintf('<div><table><tr><td><span class="small">(%s/%s)</span></td><td><span> %s%%</span></td></tr></table></div>',
            $nbGraduateIntegrated,
            count($situationsPersonDegrees),
            number_format($situationsPersonDegreesRate, 2, ',', ' '));
    }

    public function personDegreeSituationRate(
        int       $idCountry,
        int       $idRegion,
        ?int      $idPrefecture,
        string    $type,
        array     $situations,
        ?School    $school,
        DateTime $beginDate,
        DateTime $endDate): string {
        $region = $this->regionRepository->find($idRegion);
        $country = $this->countryRepository->find($idCountry);
        $prefecture = $this->prefectureRepository->find($idPrefecture);
        $allPersonDegrees = null;
        $personDegrees = [];
        if($_ENV['PREFECTURE_BETWEEN_REGION_CITY'] == 'true') {
            if($prefecture) {
                $allPersonDegrees = ($school) ?
                    $this->personDegreeRepository->getByPrefectureAndSchoolBetweenCreatedDateAndEndDate($prefecture, $school, $beginDate, $endDate) :
                    $this->personDegreeRepository->getByPrefectureBetweenCreatedDateAndEndDate($prefecture, $beginDate, $endDate);
            }
        }

        if(!$allPersonDegrees) {
            if (!$region) {
                $allPersonDegrees = ($school) ?
                    $this->personDegreeRepository->getByCountryAndSchoolBetweenCreatedDateAndEndDate($country, $school, $beginDate, $endDate) :
                    $this->personDegreeRepository->getByCountryBetweenCreatedDateAndEndDate($country, $beginDate, $endDate);
            } else {
                $allPersonDegrees = ($school) ?
                    $this->personDegreeRepository->getByRegionAndSchoolBetweenCreatedDateAndEndDate($region, $school, $beginDate, $endDate) :
                    $this->personDegreeRepository->getByRegionBetweenCreatedDateAndEndDate($region, $beginDate, $endDate);
            }
        }

        //purge function type of students
        if($type == 'all') {
            $personDegrees = $allPersonDegrees;
        } else if($type == 'graduate') {
            foreach ($allPersonDegrees as $personDegree) {
                if(($personDegree->getType() != 'TYPE_TRAINING') && ($personDegree->getType() != 'TYPE_DROPOUT')) {
                    $personDegrees[] = $personDegree;
                }
            }
        } else if($type == 'auto_employment_in_employment') {
            foreach ($allPersonDegrees as $personDegree) {
                if(($personDegree->getType() == 'TYPE_EMPLOYED') ||
                    ($personDegree->getType() == 'TYPE_CONTRACTOR')
                ) {
                    $personDegrees[] = $personDegree;
                }
            }
        } else if($type == 'auto_employment') {
            foreach ($allPersonDegrees as $personDegree) {
                if (($personDegree->getType() == 'TYPE_EMPLOYED') ||
                    ($personDegree->getType() == 'TYPE_CONTRACTOR') ||
                    ($personDegree->getType() == 'TYPE_SEARCH')
                ) {
                    $personDegrees[] = $personDegree;
                }
            }
        }

        $situationsPersonDegrees = [];
        foreach ($situations as $situation) {
            $situationPersonDegrees = ($school) ?
                $this->personDegreeRepository->getByTypeAndSchoolBetweenCreatedDateAndEndDate($situation, $school, $beginDate, $endDate) :
                $this->personDegreeRepository->getByTypeBetweenCreatedDateAndEndDate($situation, $beginDate, $endDate);

            // Stockage du diplômé
            // if($_ENV['PREFECTURE_BETWEEN_REGION_CITY'] == 'true') {
            if ($prefecture) {
                foreach ($situationPersonDegrees as $situationPersonDegree) {
                    if ($situationPersonDegree->getAddressCity()) {
                        if ($situationPersonDegree->getAddressCity()->getPrefecture()) {
                            if ($situationPersonDegree->getAddressCity()->getPrefecture()->getId() == $idPrefecture) {
                                $situationsPersonDegrees[] = $situationPersonDegree;
                            }
                        }
                    }
                }
                // }
            } else {
                if (!$region) {
                    foreach ($situationPersonDegrees as $situationPersonDegree) {
                        if ($situationPersonDegree->getCountry()) {
                            if ($situationPersonDegree->getCountry()->getId() == $idCountry) {
                                $situationsPersonDegrees[] = $situationPersonDegree;
                            }
                        }
                    }
                } else {
                    foreach ($situationPersonDegrees as $situationPersonDegree) {
                        if ($situationPersonDegree->getRegion()) {
                            if ($situationPersonDegree->getRegion()->getId() == $idRegion) {
                                $situationsPersonDegrees[] = $situationPersonDegree;
                            }
                        }
                    }
                }
            }
        }

        if($type == 'number') {
            return (count($situationsPersonDegrees));
        }

        $situationsPersonDegreesRate = 0;
        if (count($personDegrees) > 0)
            $situationsPersonDegreesRate = count($situationsPersonDegrees) / count($personDegrees) * 100;

        return sprintf('<div><table><tr><td><span class="small">(%s/%s)</span></td><td><span> %s%%</span></td></tr></table></div>',
            count($situationsPersonDegrees),
            count($personDegrees),
            number_format($situationsPersonDegreesRate, 2, ',', ' '));
    }

    public function personDegreeSurveyRate(
        int       $idCountry,
        int       $idRegion,
        ?int      $idPrefecture,
        array     $situations,
        ?School    $school,
        DateTime $beginDate,
        DateTime $endDate): string {
        $region = $this->regionRepository->find($idRegion);
        $country = $this->countryRepository->find($idCountry);
        $prefecture = $this->prefectureRepository->find($idPrefecture);
        $allPersonDegrees = null;
        $personDegrees = [];
        if($_ENV['PREFECTURE_BETWEEN_REGION_CITY'] == 'true') {
            if($prefecture) {
                $allPersonDegrees = ($school) ?
                    $this->personDegreeRepository->getByPrefectureAndSchoolBetweenCreatedDateAndEndDate($prefecture, $school, $beginDate, $endDate) :
                    $this->personDegreeRepository->getByPrefectureBetweenCreatedDateAndEndDate($prefecture, $beginDate, $endDate);
            }
        }

        if(!$allPersonDegrees) {
            if (!$region) {
                $allPersonDegrees = ($school) ?
                    $this->personDegreeRepository->getByCountryAndSchoolBetweenCreatedDateAndEndDate($country, $school, $beginDate, $endDate) :
                    $this->personDegreeRepository->getByCountryBetweenCreatedDateAndEndDate($country, $beginDate, $endDate);
            } else {
                $allPersonDegrees = ($school) ?
                    $this->personDegreeRepository->getByRegionAndSchoolBetweenCreatedDateAndEndDate($region, $school, $beginDate, $endDate) :
                    $this->personDegreeRepository->getByRegionBetweenCreatedDateAndEndDate($region, $beginDate, $endDate);
            }
        }

        $typePersonDegrees = [];
        foreach ($allPersonDegrees as $personDegree) {
            if (in_array($personDegree->getType(), $situations)) {
                $typePersonDegrees[] = $personDegree;
            }
        }

        // looking for surveys from persondegree
        foreach ($typePersonDegrees as $personSurvey) {
            if($personSurvey->getType() == "TYPE_EMPLOYED") {
                $satisfaction = $this->satisfactionSalaryRepository->getLastSatisfaction($personSurvey);
                if($satisfaction) {
                    $updateDate = $satisfaction->getUpdatedDate();

                    $dateStr = '01/' . $personSurvey->getLastDegreeMonth() . '/' .  $personSurvey->getLastDegreeYear();
                    $dateDegree = (new DateTime())->createFromFormat('d/m/Y', $dateStr);
                    $limitDate = $dateDegree->add(new DateInterval("P12M"));

                    //return "TYPE_EMPLOYED " . $updateDate->format('Y-m-d')   . " | " . $limitDate->format('Y-m-d');
                    if ($updateDate > $limitDate) {
                        $personDegrees[] = $personSurvey;
                    }
                }
            } else if($personSurvey->getType() == "TYPE_CONTRACTOR") {
                $satisfaction = $this->satisfactionCreatorRepository->getLastSatisfaction($personSurvey);
                if($satisfaction) {
                    $updateDate = $satisfaction->getUpdatedDate();

                    $dateStr = '01/' . $personSurvey->getLastDegreeMonth() . '/' .  $personSurvey->getLastDegreeYear();
                    $dateDegree = (new DateTime())->createFromFormat('d/m/Y', $dateStr);
                    $limitDate = $dateDegree->add(new DateInterval("P12M"));

                    //return "TYPE_CONTRACTOR " . $updateDate->format('Y-m-d') . " | " . $limitDate->format('Y-m-d');
                    if ($updateDate > $limitDate) {
                        $personDegrees[] = $personSurvey;
                    }
                }
            } else if(($personSurvey->getType() == "TYPE_SEARCH") || ($personSurvey->getType() == "TYPE_STUDY") || ($personSurvey->getType() == "TYPE_UNEMPLOYED")) {
                $satisfaction = $this->satisfactionSearchRepository->getLastSatisfaction($personSurvey);
                if($satisfaction) {
                    $updateDate = $satisfaction->getUpdatedDate();

                    $dateStr = '01/' . $personSurvey->getLastDegreeMonth() . '/' .  $personSurvey->getLastDegreeYear();
                    $dateDegree = (new DateTime())->createFromFormat('d/m/Y', $dateStr);
                    $limitDate = $dateDegree;
                    if(gettype($dateDegree) == "object") {
                        $limitDate = clone($dateDegree);
                    }
                    if($dateDegree)
                        $limitDate = $dateDegree->add(new DateInterval("P12M"));

                    // return "TYPE_SEARCH " . $personSurvey->getId() . " " . $updateDate->format('Y-m-d')  . " | " . $limitDate->format('Y-m-d');
                    if ($updateDate > $limitDate) {
                        $personDegrees[] = $personSurvey;
                    }
                }
            }
        }

        $situationsPersonDegreesRate = 0;
        if (count($personDegrees) > 0)
            $situationsPersonDegreesRate = count($personDegrees) / count($typePersonDegrees) * 100;

        return sprintf('<div><table><tr><td><span class="small">(%s/%s)</span></td><td><span> %s%%</span></td></tr></table></div>',
            count($personDegrees),
            count($typePersonDegrees),
            number_format($situationsPersonDegreesRate, 2, ',', ' '));
    }

    public function personDegreeContinuingTheirStudies(
        int $idCountry,
        int $idRegion,
        ?int $idPrefecture,
        ?School $school,
        DateTime $beginDate,
        DateTime $endDate
    ): string {
        $country = $this->countryRepository->find($idCountry);
        $region = $this->regionRepository->find($idRegion);
        $allPersonDegrees = null;
        $studyPersonDegrees = null;
        if($_ENV['PREFECTURE_BETWEEN_REGION_CITY'] == 'true') {
            $prefecture = $this->prefectureRepository->find($idPrefecture);
            if($prefecture) {
                $allPersonDegrees = ($school) ?
                    $this->personDegreeRepository->getByPrefectureAndSchoolBetweenCreatedDateAndEndDate($prefecture,  $school, $beginDate, $endDate):
                    $this->personDegreeRepository->getByPrefectureBetweenCreatedDateAndEndDate($prefecture,  $beginDate, $endDate);
                $studyPersonDegrees = ($school) ?
                    $this->personDegreeRepository->getByPrefectureAndTypeAndSchoolBetweenCreatedDateAndEndDate($prefecture, 'TYPE_STUDY', $school, $beginDate, $endDate):
                    $this->personDegreeRepository->getByPrefectureAndTypeBetweenCreatedDateAndEndDate($prefecture, 'TYPE_STUDY', $beginDate, $endDate);
            }
        }

        if(!$studyPersonDegrees) {
            if (!$region) {
                $allPersonDegrees = ($school) ?
                    $this->personDegreeRepository->getByCountryAndSchoolBetweenCreatedDateAndEndDate($country,  $school, $beginDate, $endDate):
                    $this->personDegreeRepository->getByCountryBetweenCreatedDateAndEndDate($country,  $beginDate, $endDate);
                $studyPersonDegrees = ($school) ?
                    $this->personDegreeRepository->getByCountryAndTypeAndSchoolBetweenCreatedDateAndEndDate($country, 'TYPE_STUDY', $school, $beginDate, $endDate) :
                    $this->personDegreeRepository->getByCountryAndTypeBetweenCreatedDateAndEndDate($country, 'TYPE_STUDY', $beginDate, $endDate);
            } else {
                $allPersonDegrees = ($school) ?
                    $this->personDegreeRepository->getByRegionAndSchoolBetweenCreatedDateAndEndDate($region,  $school, $beginDate, $endDate):
                    $this->personDegreeRepository->getByRegionBetweenCreatedDateAndEndDate($region,  $beginDate, $endDate);
                $studyPersonDegrees = ($school) ?
                    $this->personDegreeRepository->getByRegionAndTypeAndSchoolBetweenCreatedDateAndEndDate($region, 'TYPE_STUDY', $school, $beginDate, $endDate) :
                    $this->personDegreeRepository->getByRegionAndTypeBetweenCreatedDateAndEndDate($region, 'TYPE_STUDY', $beginDate, $endDate);
            }
        }
        //
        // $SatisfactionPersonDegrees = [];
        // $situationsPersonDegrees = [];
        //
        // // Recherche si la poursuite d'étude est en lien avec le premier diplôme dans le questionnaire de satisfaction
        // foreach ($StudyPersonDegrees as $StudyPersonDegree) {
        //     $satisfaction = $this->satisfactionSearchRepository->getLastSatisfaction($StudyPersonDegree);
        //     if ($satisfaction) {
        //         $SatisfactionPersonDegrees[] = $StudyPersonDegree;
        //         // if ($satisfaction->getFormationPursuitLastDegree() == $lien) {
        //         //     $situationsPersonDegrees[] = $StudyPersonDegree;
        //         // }
        //     }
        // }

        $situationsPersonDegreesRate = 0;
        if (count($allPersonDegrees) > 0)
            $situationsPersonDegreesRate = count($studyPersonDegrees) / count($allPersonDegrees) * 100;

        return sprintf('<div><table><tr><td><span class="small">(%s/%s)</span></td><td><span> %s%%</span></td></tr></table></div>',
            count($studyPersonDegrees),
            count($allPersonDegrees),
            number_format($situationsPersonDegreesRate, 2, ',', ' '));
    }


    public function personDegreeUnemployedPursuitLastDegreeRate(
        int $idCountry,
        int $idRegion,
        ?int $idPrefecture,
        bool $lien,
        ?School $school,
        DateTime $beginDate,
        DateTime $endDate
    ): string {
        $country = $this->countryRepository->find($idCountry);
        $region = $this->regionRepository->find($idRegion);
        $StudyPersonDegrees = null;
        if($_ENV['PREFECTURE_BETWEEN_REGION_CITY'] == 'true') {
            $prefecture = $this->prefectureRepository->find($idPrefecture);
            if($prefecture) {
                $StudyPersonDegrees = ($school) ?
                    $this->personDegreeRepository->getByPrefectureAndTypeAndSchoolBetweenCreatedDateAndEndDate($prefecture, 'TYPE_STUDY', $school, $beginDate, $endDate):
                    $this->personDegreeRepository->getByPrefectureAndTypeBetweenCreatedDateAndEndDate($prefecture, 'TYPE_STUDY', $beginDate, $endDate);
            }
        }

        if(!$StudyPersonDegrees) {
            if (!$region) {
                $StudyPersonDegrees = ($school) ?
                    $this->personDegreeRepository->getByCountryAndTypeAndSchoolBetweenCreatedDateAndEndDate($country, 'TYPE_STUDY', $school, $beginDate, $endDate) :
                    $this->personDegreeRepository->getByCountryAndTypeBetweenCreatedDateAndEndDate($country, 'TYPE_STUDY', $beginDate, $endDate);
            } else {
                $StudyPersonDegrees = ($school) ?
                    $this->personDegreeRepository->getByRegionAndTypeAndSchoolBetweenCreatedDateAndEndDate($region, 'TYPE_STUDY', $school, $beginDate, $endDate) :
                    $this->personDegreeRepository->getByRegionAndTypeBetweenCreatedDateAndEndDate($region, 'TYPE_STUDY', $beginDate, $endDate);
            }
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
        ?int $idPrefecture,
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
            $situationPersonDegrees = null;
            if($_ENV['PREFECTURE_BETWEEN_REGION_CITY'] == 'true') {
                $prefecture = $this->prefectureRepository->find($idPrefecture);
                if($prefecture) {
                    $situationPersonDegrees = ($school) ?
                        $this->personDegreeRepository->getByPrefectureAndTypeAndSchoolBetweenCreatedDateAndEndDate($prefecture, $situation, $school, $beginDate, $endDate):
                        $this->personDegreeRepository->getByPrefectureAndTypeBetweenCreatedDateAndEndDate($prefecture, $situation, $beginDate, $endDate);
                }
            }
            if(!$situationPersonDegrees) {
                if (!$region) {
                    $situationPersonDegrees = ($school) ?
                        $this->personDegreeRepository->getByCountryAndTypeAndSchoolBetweenCreatedDateAndEndDate($country, $situation, $school, $beginDate, $endDate) :
                        $this->personDegreeRepository->getByCountryAndTypeBetweenCreatedDateAndEndDate($country, $situation, $beginDate, $endDate);
                } else {
                    $situationPersonDegrees = ($school) ?
                        $this->personDegreeRepository->getByRegionAndTypeAndSchoolBetweenCreatedDateAndEndDate($region, $situation, $school, $beginDate, $endDate) :
                        $this->personDegreeRepository->getByRegionAndTypeBetweenCreatedDateAndEndDate($region, $situation, $beginDate, $endDate);
                }
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
            $typePersonDegree = $this->translator->trans('dashboard.unemployed');
        } elseif ($personTypeName == "PersonDegreeEmployed") {
            $typePersonDegree = $this->translator->trans('dashboard.employed');
        } elseif ($personTypeName == "PersonDegreeContractor") {
            $typePersonDegree = $this->translator->trans('dashboard.contractor');
        }

        if ($objectRateName == "Activity") {
            $html .= sprintf(' %s %s', $this->translator->trans('dashboard.total_graduates'), $typePersonDegree);
        } else {
            $html .= sprintf(' %s<br>%s', $this->translator->trans('dashboard.total_graduates'), $typePersonDegree);
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
        ?int $idPrefecture,
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
            $globalEntities = null;
            if($_ENV['PREFECTURE_BETWEEN_REGION_CITY'] == 'true') {
                $prefecture = $this->prefectureRepository->find($idPrefecture);
                if($prefecture) {
                    $globalEntities = $this->schoolRepository->getByPrefectureBetweenCreatedDateAndEndDate($prefecture, $beginDate, $endDate);
                }
            }
            if(!$globalEntities) {
                $globalEntities = ($region) ?
                    $this->schoolRepository->getByRegionBetweenCreatedDateAndEndDate($region, $beginDate, $endDate) :
                    $this->schoolRepository->getByCountryBetweenCreatedDateAndEndDate($country, $beginDate, $endDate);
            }
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
            $html .= sprintf(' %s', $this->translator->trans('dashboard.total_schools'));
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
        ?int $idPrefecture,
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
        $prefecture = null;
        $globalEntities = null;
        if($_ENV['PREFECTURE_BETWEEN_REGION_CITY'] == 'true') {
            $prefecture = $this->prefectureRepository->find($idPrefecture);
        }

        if ($entityName == 'School') {
            if($prefecture) {
                $globalEntities = $this->schoolRepository->getByPrefectureBetweenCreatedDateAndEndDate($prefecture, $beginDate, $endDate);
            }
            if(!$globalEntities) {
                $globalEntities = ($region) ?
                    $this->schoolRepository->getByRegionBetweenCreatedDateAndEndDate($region, $beginDate, $endDate) :
                    $this->schoolRepository->getByCountryBetweenCreatedDateAndEndDate($country, $beginDate, $endDate);
            }
        } elseif ($entityName == 'Company') {
            if($prefecture) {
                $globalEntities = $this->companyRepository->getByPrefectureBetweenCreatedDateAndEndDate($prefecture, $beginDate, $endDate);
            }
            if(!$globalEntities) {
                $globalEntities = ($region) ?
                    $this->companyRepository->getByRegionBetweenCreatedDateAndEndDate($region, $beginDate, $endDate) :
                    $this->companyRepository->getByCountryBetweenCreatedDateAndEndDate($country, $beginDate, $endDate);
            }
        } elseif ($entityName == 'PersonDegree') {
            if ($portionEntityName == 'Contract') {
                if($prefecture) {
                    $globalEntities = ($school) ?
                        $this->personDegreeRepository->getByPrefectureAndTypeAndSchoolBetweenCreatedDateAndEndDate($prefecture, 'TYPE_EMPLOYED', $school, $beginDate, $endDate) :
                        $this->personDegreeRepository->getByPrefectureAndTypeBetweenCreatedDateAndEndDate($prefecture, 'TYPE_EMPLOYED', $beginDate, $endDate);
                }
                if(!$globalEntities) {
                    if (!$region) {
                        $globalEntities = ($school) ?
                            $this->personDegreeRepository->getByCountryAndTypeAndSchoolBetweenCreatedDateAndEndDate($country, 'TYPE_EMPLOYED', $school, $beginDate, $endDate) :
                            $this->personDegreeRepository->getByCountryAndTypeBetweenCreatedDateAndEndDate($country, 'TYPE_EMPLOYED', $beginDate, $endDate);
                    } else {
                        $globalEntities = ($school) ?
                            $this->personDegreeRepository->getByRegionAndTypeAndSchoolBetweenCreatedDateAndEndDate($region, 'TYPE_EMPLOYED', $school, $beginDate, $endDate) :
                            $this->personDegreeRepository->getByRegionAndTypeBetweenCreatedDateAndEndDate($region, 'TYPE_EMPLOYED', $beginDate, $endDate);
                    }
                }
            } else {
                if($prefecture) {
                    $globalEntities = ($school) ?
                        $this->personDegreeRepository->getByPrefectureAndSchoolBetweenCreatedDateAndEndDate($prefecture, $school, $beginDate, $endDate):
                        $this->personDegreeRepository->getByPrefectureBetweenCreatedDateAndEndDate($prefecture, $beginDate, $endDate) ;
                }
                if(!$globalEntities) {
                    if (!$region) {
                        $globalEntities = ($school) ?
                            $this->personDegreeRepository->getByCountryAndSchoolBetweenCreatedDateAndEndDate($country, $school, $beginDate, $endDate) :
                            $this->personDegreeRepository->getByCountryBetweenCreatedDateAndEndDate($country, $beginDate, $endDate);
                    } else {
                        $globalEntities = ($school) ?
                            $this->personDegreeRepository->getByRegionAndSchoolBetweenCreatedDateAndEndDate($region, $school, $beginDate, $endDate) :
                            $this->personDegreeRepository->getByRegionBetweenCreatedDateAndEndDate($region, $beginDate, $endDate);
                    }
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
            $html .= sprintf("  <span>%s</span>", $this->translator->trans('dashboard.total_companies'));
        } elseif ($entityName == "PersonDegree") {
            // $totalGraduates = 'dashboard.total_graduates';
            if ($portionEntityName == "Contract") {
                $html .= sprintf('  <span>%s<br>%s</span>', $this->translator->trans('dashboard.total_graduates'), $this->translator->trans('dashboard.employed'));
            } else {
                $html .= sprintf('  <span>%s</span>', $this->translator->trans('dashboard.total_graduates'));
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
        $html = sprintf('<select hidden id="%s%s%sData">', $entityName, $portionEntityName, $name);
        for ($i = 0; $i < count($tablePortionEntities); $i++) {
            // $tablePortionEntitiesName = strtoupper($tablePortionEntities[$i][0]);
            $tablePortionEntitiesName = $tablePortionEntities[$i][0];
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
        $html .= sprintf('<select hidden id="%s%s%sColor">', $entityName, $portionEntityName, $name);
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
                $this->translator->trans($name),
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

    public function convertDateFromDuration(string $beginDate, string $endDate, string $duration): array {
        $datas = explode(' ', $duration);
        // $beginDuration = new \DateTime($endDate);

        $beginDuration = new \DateTime($beginDate);
        $endDuration = new \DateTime($endDate);

        // set $endDuration to the end of the day to have the lasted changed
        $endDuration = $endDuration->modify('+23 hour')->modify('+59 minute');


        // if (count($datas) == 2) {
        //     if ($datas[1] == 'mois') {
        //         $beginDuration = $beginDuration->sub(new DateInterval('P' . $datas[0] . 'M'));
        //     } elseif (strncmp($datas[1], 'an', 2) == 0) {
        //         $beginDuration = $beginDuration->sub(new DateInterval('P' . $datas[0] . 'Y'));
        //     }
        // }

        // var_dump($beginDuration, $endDuration);die();
        return [$beginDuration, $endDuration];
    }

    /**
     * @param string $dateBegin
     * @param string $dateEnd
     * @return array
     */
    public function diffDateYmd (string $dateBegin, string $dateEnd): array {
        $WNbJours = (strtotime($dateEnd) - strtotime($dateBegin)) / 86400;
        $nbTotalMonths = round($WNbJours/30.43);
        $nbYear = floor($nbTotalMonths/12);
        $nbMonth = floor($nbTotalMonths - ($nbYear*12));
        $nbDay = round($WNbJours - ((($nbYear*12) + $nbMonth) * 30.43));

        return [$nbYear, $nbMonth, $nbDay, $WNbJours, $nbTotalMonths];
    }

    public function nbActorEvolution(
        string   $type,
        int      $idCountry,
        int      $idRegion,
        ?int     $idPrefecture,
        string   $actor,
        string   $title,
        string   $duration,
        ?School   $school,
        \DateTime $beginDate,
        \DateTime $endDate): string
    {
        // var_dump($type, $idCountry, $idRegion, $actor, $title, $duration, $school, $beginDate, $endDate); die();
        $country = $this->countryRepository->find($idCountry);
        $region = $this->regionRepository->find($idRegion);
        $prefecture = null;
        if($_ENV['PREFECTURE_BETWEEN_REGION_CITY'] == 'true') {
            $prefecture = $this->prefectureRepository->find($idPrefecture);
        }

        $dates = [];
        $legend = [];

        if( $actor == "PersonDegree") {
            $personDegrees = [];
            if($prefecture) {
                $personDegrees = ($school) ?
                    $this->personDegreeRepository->getByPrefectureAndSchool($prefecture, $school) :
                    $this->personDegreeRepository->getByPrefecture($prefecture);
            }
            if(!$personDegrees) {
                if ($region) {
                    $personDegrees = ($school) ?
                        $this->personDegreeRepository->getByRegionAndSchool($region, $school) :
                        $this->personDegreeRepository->findByRegion($region);
                } else if ($country) {
                    $personDegrees = ($school) ?
                        $this->personDegreeRepository->getByCountryAndSchool($country, $school) :
                        $this->personDegreeRepository->findByCountry($country);
                }
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

        //Creation des tables pour l'affichage graphique du graphe
        // if ($duration == '3 mois') { // Affichage 12 semaines (84 jours)
        //     $suffixLegend = 'S';  //semaine
        //     $intervalDuration = 7; //1 semaine
        //     $nbXInterval = 12;
        // } elseif ($duration == '6 mois') {
        //     $suffixLegend = 'BS'; //Bi-semaine
        //     $intervalDuration = 14; //2 semaines
        //     $nbXInterval = 13;
        // } elseif ($duration == '1 an') {
        //     $suffixLegend = 'M'; //mois
        //     $intervalDuration = 30; //1 mois
        //     $nbXInterval = 12;
        // } elseif ($duration == '2 ans') {
        //     $suffixLegend = 'BM';   //mois
        //     $intervalDuration = 60; //2 mois
        //     $nbXInterval = 13;
        // }

        //calcul des duration et de la légende
        $diffDate = $this->diffDateYmd($beginDate->format('Y-m-d'), $endDate->format('Y-m-d'));
        $nbYear = $diffDate[0];
        $nbMonth = $diffDate[1];
        $nbDay = $diffDate[2];
        $nbTotalDay = $diffDate[3];
        $nbTotalMonths = $diffDate[4];
        $nbXInterval = 12;
        if($nbYear < 1) {
            if($nbMonth <= 3) {
                $suffixLegend = 'S';  //semaine
                $intervalDuration = 7; //1 semaine
            } else if($nbMonth <= 6){
                $suffixLegend = 'BS'; //Bi-semaine
                $intervalDuration = 14; //2 semaines
            } else {
                $suffixLegend = 'M'; //mois
                $intervalDuration = 30; //1 mois
            }
        } else if($nbYear <= 12) {
            $suffixLegend = strval(round($nbTotalMonths/12)) . 'M'; //mois
            $intervalDuration = round($nbTotalDay/12);
        } else {
            $suffixLegend = strval(round($nbYear/12)) . 'A'; //année
            $intervalDuration = round($nbTotalDay/12);
        }
        // ajoute 1 interval si $nbXInterval X $intervalDuration < $endDate
        if($intervalDuration*$nbXInterval < $nbTotalDay) {
            $nbXInterval++;
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

        $timeSpace = $this->translator->trans('dashboard.from_ref_date') . ' ' . $beginDate->format(Utils::FORMAT_FR) . ' ' . $this->translator->trans('dashboard.to_ref_date') . ' ' . $endDate->format(Utils::FORMAT_FR);

        $html = sprintf('<div class="label">%s : %s </div>', $title, $timeSpace);

        // ecriture du nombre d'acteurs
        $nb_actors = (string)array_sum($resultats);
        $html .= sprintf('<div class="value">%s</div>', $nb_actors);

        // Ecriture du select
        $html .= sprintf('<select id="%s%sDataChart" hidden >', $type, $actor);
        for ($i = 0; $i < $nbXInterval; $i++) {
            $html .= sprintf('<option value="%s">%s</option>', $legend[$i], $resultats[$i]);
        }
        $html .= sprintf('</select>');

        return $html;
    }
}
