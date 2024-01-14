<?php

namespace App\Command;

use App\Repository\ActivityRepository;
use App\Repository\ContractRepository;
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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

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
    private ContractRepository $contractRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params,
        SectorAreaRepository $sectorAreaRepository,
        ActivityRepository $activityRepository,
        LegalStatusRepository $legalStatusRepository,
        DegreeRepository $degreeRepository,
        JobNotFoundReasonRepository $jobNotFoundReasonRepository,
        ContractRepository $contractRepository

    ) {
        $this->entityManager = $entityManager;
        $this->sectorAreaRepository = $sectorAreaRepository;
        $this->activityRepository = $activityRepository;
        $this->legalStatusRepository = $legalStatusRepository;
        $this->degreeRepository = $degreeRepository;
        $this->jobNotFoundReasonRepository = $jobNotFoundReasonRepository;
        $this->contractRepository = $contractRepository;
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
        $em = $this->entityManager;
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
            $contracts = $this->contractRepository->findAll();

            // read input csv file
            $inputFile = fopen($csv, 'rt');

            // looking for field number for each country
            $firstRow = fgetcsv($inputFile);
            $fields = explode(";", $firstRow[0]);

            function remove_accent($str):string {
                $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð',
                    'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã',
                    'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ',
                    'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ',
                    'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę',
                    'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī',
                    'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ',
                    'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ',
                    'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť',
                    'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ',
                    'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ',
                    'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');

                $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O',
                    'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c',
                    'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u',
                    'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D',
                    'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g',
                    'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K',
                    'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o',
                    'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S',
                    's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W',
                    'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i',
                    'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
                return str_replace($a, $b, $str);
            }
            function changePropertyNameInArray ($sourceName, $langName, $table, $em, $io, $counter): int {
                for ($j=0; $j<count($table); $j++) {
                    // find if new name already exist in another row
                    $element_exist = false;
                    for ($h=0; $h<count($table); $h++) {
                        if($j != $h) {
                            if (utf8_encode($sourceName) == $table[$h]->getName()) {
                                $element_exist = true;
                                // $io->error( "fail " . $langName );
                                $h = count($table);
                                $j = count($table);
                            }
                        }
                    }
                    if($element_exist) {
                        // $io->error('Duplicate source ' . $sourceName .' in input file');
                    } else {
                        $langNameSimplified = strtolower(remove_accent(utf8_encode($langName)));
                        $tableData = strtolower(remove_accent($table[$j]->getName()));

                        if ($langNameSimplified == $tableData) {
                            // echo "|" . $table[$j]->getName() . "->" . utf8_encode($sourceName) . "|\n";
                            $table[$j]->setName(utf8_encode($sourceName));
                            $em->persist($table[$j]);
                            $counter++;
                        }
                    }
                }
                return $counter;
            }

            while (($row = fgetcsv($inputFile, 1000, ";")) !== FALSE) {
                for ($i = 1; $i < count($fields); $i++) {
                    if (str_starts_with($row[0], 'sectors.')) {
                        $counter = changePropertyNameInArray($row[0], $row[$i], $sectors, $em, $io, $counter);

                    } elseif (str_starts_with($row[0], 'sub_sectors.')) {
                        $counter = changePropertyNameInArray($row[0], $row[$i], $activities, $em, $io, $counter);

                    } elseif (str_starts_with($row[0], 'legal_status.')) {
                        $counter = changePropertyNameInArray($row[0], $row[$i], $legalStatus, $em, $io, $counter);

                    } elseif (str_starts_with($row[0], 'raisons_no_job.')) {
                        $counter = changePropertyNameInArray($row[0], $row[$i], $jobNotFoundRaisons, $em, $io, $counter);

                    } elseif (str_starts_with($row[0], 'diplomas.')) {
                        $counter = changePropertyNameInArray($row[0], $row[$i], $degrees, $em, $io, $counter);

                    } elseif (str_starts_with($row[0], 'contract.')) {
                        $counter = changePropertyNameInArray($row[0], $row[$i], $contracts, $em, $io, $counter);
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
