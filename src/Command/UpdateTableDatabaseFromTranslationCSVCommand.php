<?php

namespace App\Command;

use App\Entity\LegalStatus;
use App\Entity\Role;
use App\Entity\User;
use App\Repository\ActivityRepository;
use App\Repository\DegreeRepository;
use App\Repository\JobNotFoundReasonRepository;
use App\Repository\LegalStatusRepository;
use App\Repository\SectorAreaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use \DOMDocument;

#[AsCommand(
    name: 'app:update_database_from_translation_file',
    description: 'update database from translation file: Example: php bin/console app:update_database_from_translation_file --csv=c:/temp/file.csv',
)]
class UpdateTableDatabaseFromTranslationCSVCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private ParameterBagInterface $params;
    private SectorAreaRepository $sectorAreaRepository;
    private ActivityRepository $activityRepository;
    private LegalStatusRepository $legalStatusRepository;
    private DegreeRepository $degreeRepository;
    private JobNotFoundReasonRepository $jobNotFoundReasonRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params,
        SectorAreaRepository $sectorAreaRepository,
        ActivityRepository $activityRepository,
        LegalStatusRepository $legalStatusRepository,
        DegreeRepository $degreeRepository,
        JobNotFoundReasonRepository $jobNotFoundReasonRepository

    ) {
        $this->entityManager = $entityManager;
        $this->sectorAreaRepository = $sectorAreaRepository;
        $this->activityRepository = $activityRepository;
        $this->legalStatusRepository = $legalStatusRepository;
        $this->degreeRepository = $degreeRepository;
        $this->jobNotFoundReasonRepository = $jobNotFoundReasonRepository;
        $this->params = $params;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('csv', null, InputOption::VALUE_REQUIRED, 'csv file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $csv = $input->getOption('csv');
        $filesystem = new Filesystem();
        $error = false;
        $counter = 0;

        //test if input file exist
        if (!$filesystem->exists($csv)) {
            $io->error('csv file does not exist : ' . $csv);
            $error = true;
        }

        if ($error) {
            $io->error('command aborted');
        } else {
            $sectors = $this->sectorAreaRepository->findAll();
            $activities = $this->activityRepository->findAll();
            $legalStatus = $this->legalStatusRepository->findAll();
            $jobNotFoundRaisons = $this->jobNotFoundReasonRepository->findAll();
            $degrees = $this->degreeRepository->findAll();

            // read input csv file
            $inputFile = fopen($csv, 'rt');

            // looking for field number for each country
            $firstRow = fgetcsv($inputFile);
            $fields = explode(";", $firstRow[0]);

            while (($row = fgetcsv($inputFile, 1000, ";")) !== FALSE) {
                for ($i = 1; $i < count($fields); $i++) {
                    if (str_starts_with($row[0], 'sectors.')) {
                        $element_exist = false;
                        foreach ($sectors as $sector) {
                            if(utf8_encode($row[0]) == $sector->getName()) {
                                $element_exist = true;
                            }
                        }
                        if(!$element_exist) {
                            foreach ($sectors as $sector) {
                                if (utf8_encode($row[$i]) == $sector->getName()) {
                                    // echo "|" . $sector->getName() . "->" . utf8_encode($row[0]) . "|\n";
                                    $sector->setName(utf8_encode($row[0]));
                                    $this->entityManager->persist($sector);
                                    $counter++;
                                }
                            }
                        }
                    } elseif (str_starts_with($row[0], 'sub_sectors.')) {
                        $element_exist = false;
                        foreach ($activities as $activity) {
                            if(utf8_encode($row[0]) == $activity->getName()) {
                                $element_exist = true;
                            }
                        }
                        if(!$element_exist) {
                            foreach ($activities as $activity) {
                                if (utf8_encode($row[$i]) == $activity->getName()) {
                                    //echo "|" . $activity->getName() . "->" . utf8_encode($row[0]) . "|\n";
                                    $activity->setName(utf8_encode($row[0]));
                                    $this->entityManager->persist($activity);
                                    $counter++;
                                }
                            }
                        }
                    } elseif (str_starts_with($row[0], 'legal_status.')) {
                        $element_exist = false;
                        foreach ($legalStatus as $status) {
                            if(utf8_encode($row[0]) == $status->getName()) {
                                $element_exist = true;
                            }
                        }
                        if(!$element_exist) {
                            foreach ($legalStatus as $status) {
                                if (utf8_encode($row[$i]) == $status->getName()) {
                                    // echo "|" . $status->getName() . "->" . utf8_encode($row[0]) . "|\n";
                                    $status->setName(utf8_encode($row[0]));
                                    $this->entityManager->persist($status);
                                    $counter++;
                                }
                            }
                        }
                    } elseif (str_starts_with($row[0], 'raisons_no_job.')) {
                        $element_exist = false;
                        foreach ($jobNotFoundRaisons as $jobNotFoundRaison) {
                            if(utf8_encode($row[0]) == $jobNotFoundRaison->getName()) {
                                $element_exist = true;
                            }
                        }
                        if(!$element_exist) {
                            foreach ($jobNotFoundRaisons as $jobNotFoundRaison) {
                                if (utf8_encode($row[$i]) == $jobNotFoundRaison->getName()) {
                                    // echo "|" . $jobNotFoundRaison->getName() . "->" . utf8_encode($row[0]) . "|\n";
                                    $jobNotFoundRaison->setName(utf8_encode($row[0]));
                                    $this->entityManager->persist($jobNotFoundRaison);
                                    $counter++;
                                }
                            }
                        }
                    } elseif (str_starts_with($row[0], 'diplomas.')) {
                        $element_exist = false;
                        foreach ($degrees as $degree) {
                            if(utf8_encode($row[0]) == $degree->getName()) {
                                $element_exist = true;
                            }
                        }
                        if(!$element_exist) {
                            foreach ($degrees as $degree) {
                                if (utf8_encode($row[$i]) == $degree->getName()) {
                                    // echo "|" . $degree->getName() . "->" . utf8_encode($row[0]) . "|\n";
                                    $degree->setName(utf8_encode($row[0]));
                                    $this->entityManager->persist($degree);
                                    $counter++;
                                }
                            }
                        }
                    }
                }
            }
            $this->entityManager->flush();
            fclose($inputFile);
        }

        $io->info('Update Database done with ' . $counter .' modifications');
        return Command::SUCCESS;
    }

}
