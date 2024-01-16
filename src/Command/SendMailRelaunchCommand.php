<?php

namespace App\Command;

use App\Entity\Company;
use App\Entity\Country;
use App\Entity\Currency;
use App\Entity\PersonDegree;
use App\Entity\SatisfactionCompany;
use App\Entity\SatisfactionCreator;
use App\Entity\SatisfactionSalary;
use App\Entity\SatisfactionSearch;
use App\Model\PersonDegreeReceiverNotification;
use App\Repository\CompanyRepository;
use App\Repository\PersonDegreeRepository;
use App\Repository\SatisfactionCompanyRepository;
use App\Repository\SatisfactionCreatorRepository;
use App\Repository\SatisfactionSalaryRepository;
use App\Repository\SatisfactionSearchRepository;
use App\Services\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(
    name: 'app:send-mail-relaunch',
    description: 'Send mail to relaunch graduates and companies',
)]
class SendMailRelaunchCommand extends Command
{
	private EntityManagerInterface $entityManager;
    private PersonDegreeRepository $personDegreeRepository;
    private CompanyRepository $companyRepository;
    private EmailService $emailService;
    private TranslatorInterface $translator;
    private SatisfactionCompanyRepository $satisfactionCompanyRepository;
    private SatisfactionCreatorRepository $satisfactionCreatorRepository;
    private SatisfactionSearchRepository $satisfactionSearchRepository;
    private SatisfactionSalaryRepository $satisfactionSalaryRepository;

	public function __construct(
        EntityManagerInterface $entityManager,
        PersonDegreeRepository $personDegreeRepository,
        CompanyRepository $companyRepository,
        EmailService $emailService,
        TranslatorInterface $translator,
        SatisfactionCompanyRepository $satisfactionCompanyRepository,
        SatisfactionCreatorRepository $satisfactionCreatorRepository,
        SatisfactionSearchRepository $satisfactionSearchRepository,
        SatisfactionSalaryRepository $satisfactionSalaryRepository
    ) {
		$this->entityManager = $entityManager;
        $this->personDegreeRepository = $personDegreeRepository;
        $this->companyRepository = $companyRepository;
        $this->emailService = $emailService;
        $this->translator = $translator;
        $this->satisfactionCompanyRepository = $satisfactionCompanyRepository;
        $this->satisfactionCreatorRepository = $satisfactionCreatorRepository;
        $this->satisfactionSearchRepository = $satisfactionSearchRepository;
        $this->satisfactionSalaryRepository = $satisfactionSalaryRepository;

		parent::__construct();
	}

	protected function configure(): void
    {
        $this
            // ->addArgument('id', InputArgument::OPTIONAL, 'Country ID')
            ->addOption('log_dir', null, InputOption::VALUE_REQUIRED, 'log file directory')
        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $logDir = $input->getOption('log_dir');
        $logStr = "";

        // $graduates = [];
        $noSurveyEmployeds = [];
        $noSurveySearches = [];
        $noSurveyContractors = [];
        $noSurveyCompanies = [];
        $oldSurveyEmployeds = [];
        $oldSurveySearches = [];
        $oldSurveyContractors = [];
        $oldSurveyCompanies = [];

        $graduatesEmployed = $this->personDegreeRepository->findByType("TYPE_EMPLOYED");
        $io->success("find "  . count($graduatesEmployed) . " TYPE_EMPLOYED graduates");

        $graduatesContractor = $this->personDegreeRepository->findByType("TYPE_CONTRACTOR");
        $io->success("find "  . count($graduatesContractor) . " TYPE_CONTRACTOR graduates");

        $graduatesSearch = $this->personDegreeRepository->findByType("TYPE_SEARCH");
        $io->success("find "  . count($graduatesSearch) . " TYPE_SEARCH graduates");

        $graduatesUnemployed = $this->personDegreeRepository->findByType("TYPE_UNEMPLOYED");
        $io->success("find "  . count($graduatesUnemployed) . " TYPE_UNEMPLOYED graduates");
        $graduatesSearch = array_merge($graduatesSearch, $graduatesUnemployed);

        $graduatesStudy = $this->personDegreeRepository->findByType("TYPE_STUDY");
        $io->success("find "  . count($graduatesStudy) . " TYPE_STUDY graduates");
        $graduatesSearch = array_merge($graduatesSearch, $graduatesStudy);

        $io->success("find "  . count($graduatesSearch) . " ALL_SEARCH graduates");

        $companies = $this->companyRepository->findAll();
        $io->success("find "  . count($companies) . " companies");

        function checkDegreeDate(PersonDegree $pd): array  {
            $lastDegreeMonth = $pd->getLastDegreeMonth();
            $lastDegreeYear = $pd->getLastDegreeYear();
            // $lastSatisfaction =

            $degreeMonth = "1";
            if ($lastDegreeMonth > 0) {
                $degreeMonth = $lastDegreeMonth;
            }

            $currentDate = new \DateTime();
            $degreeDateStr = $lastDegreeYear . "-" . $degreeMonth . "-1"; // 2017-7-1
            $degreeDate = new \DateTime($degreeDateStr);
            $compareDate3Month = clone $degreeDate;
            $compareDate6Month = clone $degreeDate;
            $compareDate3Month = $compareDate3Month->add(new \DateInterval('P3M'));
            $compareDate6Month = $compareDate6Month->add(new \DateInterval('P6M'));

            //just graduate (white)
            if ($currentDate < $compareDate3Month) {
                return ["", $currentDate];

                //first relaunch (Orange)
            } elseif ($currentDate < $compareDate6Month) {
                return ["3M", $degreeDate];

                //second relaunch (Red)
            } else {
                return ["6M", $degreeDate];
            }
        }

        function checkPersonDegreeSatisfaction (
            PersonDegree $personDegree,
            SatisfactionSearch|SatisfactionSalary|SatisfactionCreator|null $satisfaction
        ):array {
            $degreeDuration = checkDegreeDate($personDegree)[0];
            $degreeDate = checkDegreeDate($personDegree)[1];
            $noSurvey = null;
            $oldSurvey = null;

            $currentDateLessOneYear = (new \DateTime())->sub(new \DateInterval('P1Y'));

            if($degreeDuration) {
                // if satisfaction does not exist
                if (!$satisfaction) {
                    if ($degreeDuration == "3M") {
                        $noSurvey = "3M;" . $personDegree->getId() . ";" . "null";
                    } elseif ($degreeDuration == "6M") {
                        $noSurvey = "6M;" . $personDegree->getId()  . ";" . "null";
                    }

                // if satisfaction date has more than one year
                } else {
                    if ($satisfaction->getUpdatedDate()) {
                        $lastSatisfactionDate = $satisfaction->getUpdatedDate();
                    } else {
                        $lastSatisfactionDate = $satisfaction->getCreatedDate();
                    }

                    if ($degreeDuration == "6M") {
                        if ($lastSatisfactionDate <= $currentDateLessOneYear) {
                            $oldSurvey = "Y;" . $personDegree->getId() . ";" . $lastSatisfactionDate->format("d-m-Y");
                        }
                    }
                }
            }
            return [$noSurvey, $oldSurvey];
        }

        function checkCompanySatisfaction (
            Company $company,
            SatisfactionCompany|null $satisfaction
        ):array {
            $currentDateLessOneYear = (new \DateTime())->sub(new \DateInterval('P1Y'));
            $noSurvey = null;
            $oldSurvey = null;

            // if satisfaction does not exist
            if (!$satisfaction) {
                $noSurvey = "NULL;" . $company->getId() . ";" . "null";

            // if satisfaction date has more than one year
            } else {
                // var_dump($satisfaction);die();
                if ($satisfaction->getUpdatedDate()) {
                    $lastSatisfactionDate = $satisfaction->getUpdatedDate();
                } else {
                    $lastSatisfactionDate = $satisfaction->getCreatedDate();
                }

                if ($lastSatisfactionDate <= $currentDateLessOneYear) {
                    if($lastSatisfactionDate) {
                        $oldSurvey = "Y;" . $company->getId() . ";" . $lastSatisfactionDate->format("d-m-Y");
                    } else {
                        $oldSurvey = "Y;" . $company->getId() . ";" . "NULL";
                    }
                }
            }
            return [$noSurvey, $oldSurvey];
        }

        function sendMailPersonDegree(string $data, PersonDegreeRepository $repos, $mail):string {
            $dataExplode = explode(';',$data);
            $graduate = $repos->find(intval($dataExplode[1]));
            if($graduate) {
                $mail->sendRelaunchPersonDegree($graduate, $dataExplode[0],$dataExplode[2]);
                return "  " . $graduate->getPhoneMobile1()  . "; ". $graduate->getEmail()  . " (" . $graduate->getName()  . " " . $graduate->getFirstName() . ")\n";
            }
            return "";
        }

        function sendMailCompany(string $data, CompanyRepository $repos, $mail):string {
            $dataExplode = explode(';',$data);
            $company = $repos->find(intval($dataExplode[1]));

            if($company) {
                $mail->sendRelaunchCompany($company, $dataExplode[0], $dataExplode[2]);
                return "  " . $company->getPhoneStandard()  . "; ". $company->getEmail() . " (" . $company->getName() . ")\n";
            }
            return "";
        }

        //check all actors satisfaction survey
        //------------------------------------
        foreach ($graduatesSearch as $graduate) {
            if(($graduate->getEmail() == "denispailler1@yopmail.com") || ($graduate->getEmail() == "denispailler2@yopmail.com") || ($graduate->getEmail() == "denispailler3@yopmail.com") || ($graduate->getEmail() == "denispailler4@yopmail.com") || ($graduate->getEmail() == "denispailler5@yopmail.com") || ($graduate->getEmail() == "denispailler6@yopmail.com")) {
                $satisfaction = $this->satisfactionSearchRepository->getLastSatisfaction($graduate);
                $res = checkPersonDegreeSatisfaction($graduate, $satisfaction);
                if ($res[0]) {
                    $noSurveySearches[] = $res[0];
                }
                if ($res[1]) {
                    $oldSurveySearches[] = $res[1];
                }
            }
        }

        foreach ($graduatesContractor as $graduate) {
            if(($graduate->getEmail() == "denispailler1@yopmail.com") || ($graduate->getEmail() == "denispailler2@yopmail.com") || ($graduate->getEmail() == "denispailler3@yopmail.com") || ($graduate->getEmail() == "denispailler4@yopmail.com") || ($graduate->getEmail() == "denispailler5@yopmail.com") || ($graduate->getEmail() == "denispailler6@yopmail.com")) {
                $satisfaction = $this->satisfactionCreatorRepository->getLastSatisfaction($graduate);
                $res = checkPersonDegreeSatisfaction($graduate, $satisfaction);
                if ($res[0]) {
                    $noSurveyContractors[] = $res[0];
                }
                if ($res[1]) {
                    $oldSurveyContractors[] = $res[1];
                }
            }
        }

        foreach ($graduatesEmployed as $graduate) {
            if(($graduate->getEmail() == "denispailler1@yopmail.com") || ($graduate->getEmail() == "denispailler2@yopmail.com") || ($graduate->getEmail() == "denispailler3@yopmail.com") || ($graduate->getEmail() == "denispailler4@yopmail.com") || ($graduate->getEmail() == "denispailler5@yopmail.com") || ($graduate->getEmail() == "denispailler6@yopmail.com")) {
                $satisfaction = $this->satisfactionSalaryRepository->getLastSatisfaction($graduate);
                $res = checkPersonDegreeSatisfaction($graduate, $satisfaction);
                if ($res[0]) {
                    $noSurveyEmployeds[] = $res[0];
                }
                if ($res[1]) {
                    $oldSurveyEmployeds[] = $res[1];
                }
            }
        }

        foreach ($companies as $company) {
            if(($company->getEmail() == "denispailler7@yopmail.com") || ($company->getEmail() == "denispailler8@yopmail.com") || ($company->getEmail() == "denispailler9@yopmail.com")) {
                $satisfaction = $this->satisfactionCompanyRepository->getLastSatisfaction($company);
                $res = checkCompanySatisfaction($company, $satisfaction);
                if ($res[0]) {
                    $noSurveyCompanies[] = $res[0];
                }
                if ($res[1]) {
                    $oldSurveyCompanies[] = $res[1];
                }
            }
        }

        //Send Emails By Categories
        $logStr .= "List of graduates and companies relaunch by mail on " . (new \DateTime())->format("Y-m-d") . "\n";
        $logStr .= "==============================================================" . "\n";
        $io->error(count($noSurveySearches) . " noSurveySearches");
        $logStr .= "\nList of graduates without survey Search: " . count($noSurveySearches)."\n";
        foreach ($noSurveySearches as $noSurveySearch) {
            $logStr .= sendMailPersonDegree($noSurveySearch, $this->personDegreeRepository, $this->emailService);
            // echo $noSurveySearch . "\n";
        }

        $logStr .= "\nList of graduates with old survey Search: " . count($oldSurveySearches)."\n";
        $io->error(count($oldSurveySearches) . " oldSurveySearches");
        foreach ($oldSurveySearches as $oldSurveySearch) {
            $logStr .= sendMailPersonDegree($oldSurveySearch, $this->personDegreeRepository, $this->emailService);
            // echo $oldSurveySearch . "\n";
        }

        $logStr .= "\n------------------------------------------------------" . "\n";
        $logStr .= "\nList of graduates without survey Contractor: " . count($noSurveyContractors)."\n";
        $io->error(count($noSurveyContractors) . " noSurveyContractors");
        foreach ($noSurveyContractors as $noSurveyContractor) {
            $logStr .= sendMailPersonDegree($noSurveyContractor, $this->personDegreeRepository, $this->emailService);
            // echo $noSurveyContractor . "\n";
        }

        $logStr .= "\nList of graduates with old survey Contractor: " . count($oldSurveyContractors)."\n";
        $io->error(count($oldSurveyContractors) . " oldSurveyContractors");
        foreach ($oldSurveyContractors as $oldSurveyContractor) {
            $logStr .= sendMailPersonDegree($oldSurveyContractor, $this->personDegreeRepository, $this->emailService);
            // echo $oldSurveyContractor . "\n";
        }

        $logStr .= "\n------------------------------------------------------" . "\n";
        $logStr .= "\nList of graduates without survey Employed: " . count($noSurveyEmployeds)."\n";
        $io->error(count($noSurveyEmployeds) . " noSurveyEmployeds");
        foreach ($noSurveyEmployeds as $noSurveyEmployed) {
            $logStr .= sendMailPersonDegree($noSurveyEmployed, $this->personDegreeRepository, $this->emailService);
            // echo $noSurveyEmployed . "\n";
        }

        $logStr .= "\nList of graduates with old survey Employed: " . count($oldSurveyEmployeds)."\n";
        $io->error(count($oldSurveyEmployeds) . " oldSurveyEmployeds");
        foreach ($oldSurveyEmployeds as $oldSurveyEmployed) {
            $logStr .= sendMailPersonDegree($oldSurveyEmployed, $this->personDegreeRepository, $this->emailService);
            // echo $oldSurveyEmployed . "\n";
        }

        $logStr .= "\n------------------------------------------------------" . "\n";
        $logStr .= "\nList of Company without survey: " . count($noSurveyCompanies)."\n";
        $io->error(count($noSurveyCompanies) . " noSurveyCompanies");
        foreach ($noSurveyCompanies as $noSurveyCompany) {
            $logStr .= sendMailCompany($noSurveyCompany, $this->companyRepository, $this->emailService);
            // echo $noSurveyCompany . "\n";
        }

        $logStr .= "\nList of Company with old survey : " . count($oldSurveyCompanies)."\n";
        $io->error(count($oldSurveyCompanies) . " oldSurveyCompanies");
        foreach ($oldSurveyCompanies as $oldSurveyCompany) {
            $logStr .= sendMailCompany($oldSurveyCompany, $this->companyRepository, $this->emailService);
            // echo $oldSurveyCompany . "\n";
        }

        //Write log file
        $handle = fopen($logDir .'/SendMailRelaunchCommand_' . (new \DateTime())->format("Ymd") . ".log", "w");
        fwrite($handle, $logStr);
        fclose($handle);

        // $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
