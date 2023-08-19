<?php

namespace App\Command;

use App\Entity\Role;
use App\Entity\User;
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
    name: 'app:import-translation-from-csv',
    description: 'Import translation files from csv: Example: php bin/console app:import-translation-from-csv --csv=c:/temp/file.csv --ouputdir_xml=c:/temp --outputdir_json=c:/temp',
)]
class ImportTranslationFileFromCSVCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private ParameterBagInterface $params;

    public function __construct(
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params,
    ) {
        $this->entityManager = $entityManager;
        $this->params = $params;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('csv', null, InputOption::VALUE_REQUIRED, 'csv file')
            ->addOption('outputdir_xml', null, InputOption::VALUE_REQUIRED, 'xml output directory')
            ->addOption('outputdir_json', null, InputOption::VALUE_OPTIONAL,  'json output directory')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $csv = $input->getOption('csv');
        $ouputdirXml = $input->getOption('outputdir_xml');
        $ouputdirJson = $input->getOption('outputdir_json');
        $filesystem = new Filesystem();
        $error = false;

        //test if input file exist
        if (!$filesystem->exists($csv)) {
            $io->error('csv file does not exist : ' . $csv);
            $error = true;
        }
        //test if output dir exist
        if (!$filesystem->exists($ouputdirXml)) {
            $io->error('output directory does not exist : ' . $ouputdirXml);
            $error = true;
        }

        //test if dossier translations exist
        $supported_locales = $this->params->get('app.supported_locales');
        $io->warning("supported_locales=" . $supported_locales);
        $locales = explode('|', $supported_locales);

        if ($error) {
            $io->error('command aborted');
        } else {
            //create files with headers
            foreach ($locales as $locale) {
                // read input csv file
                $inputFile = fopen($csv, 'rt');

                // select env field name for translation
                $env_locale_name = $locale;
                if(isset($_ENV[strtoupper($locale) . "_FIELD_TRANSLATION"])) {
                    $env_locale_name = $_ENV[strtoupper($locale) . "_FIELD_TRANSLATION"];
                }

                // looking for field number for each country
                $firstRow = fgetcsv($inputFile);
                $fields = explode(";", $firstRow[0]);

                $localeField = -1;

                for ($i = 0; $i < count($fields); $i++) {
                    if ( $fields[$i] == $env_locale_name) {
                        $localeField = $i;
                    }
                 }
                // echo ("---->>>" . $locale . " " . $localeField . " " . $env_locale_name) . "\n";

                if($localeField >= 1) {
                    // Create a new dom document with pretty formatting
                    $doc = new DOMDocument('1.0', 'utf-8');
                    $doc->formatOutput = true;
                    $xliff = $doc->createElement('xliff');
                    $xliff->setAttribute('version', '1.2');
                    $xliff->setAttribute('xmlns', 'urn:oasis:names:tc:xliff:document:1.2');
                    $doc->appendChild($xliff);

                    $file = $doc->createElement('file');
                    $file->setAttribute('source-language', 'fr');
                    $file->setAttribute('target-language', 'fr');
                    $file->setAttribute('datatype', 'plaintext');
                    $file->setAttribute('original', 'file.ext');
                    $xliff->appendChild($file);

                    $fileheader = $doc->createElement('header');
                    $file->appendChild($fileheader);

                    $tool = $doc->createElement('tool');
                    $tool->setAttribute('tool-id', 'symfony');
                    $tool->setAttribute('tool-name', 'Symfony');
                    $fileheader->appendChild($tool);

                    $filebody = $doc->createElement('body');
                    $file->appendChild($filebody);

                    // create object for json datatable
                    $datatable = new \stdClass();
                    $datatable->paginate = new \stdClass();
                    $datatable->aria = new \stdClass();

                    // Add a root node to the document
                    while (($row = fgetcsv($inputFile, 1000, ";")) !== FALSE) {
                        if(count($row) >= $localeField+1) {
                            if(strlen($row[0])>0) {
                                $transunit = $doc->createElement('trans-unit');
                                $transunit->setAttribute('id', utf8_encode($row[0]));
                                $filebody->appendChild($transunit);

                                $source = $doc->createElement('source', utf8_encode($row[0]));
                                $transunit->appendChild($source);
                                $target = $doc->createElement('target', utf8_encode($row[$localeField]));

                                //set default language fr if no translation
                                if (strlen(trim($target->nodeValue, " ")) == 0) {
                                    if(count($row)>1) {
                                        $target = $doc->createElement('target', utf8_encode($row[1]));
                                    }
                                }
                                $transunit->appendChild($target);

                                //set datas for json datatable
                                $jsonData = utf8_encode($row[$localeField]);
                                if (strlen($jsonData) == 0) {
                                    $jsonData = utf8_encode($row[1]);
                                }
                                if(str_starts_with($row[0], "datatable.")) {
                                    $sources = explode('.', $row[0]);
                                    if (count($sources) > 2) {
                                        $src = $sources[2];
                                        if ($sources[1] == "paginate") {
                                            $datatable->paginate->$src = $jsonData;
                                        } elseif ($sources[1] == "aria") {
                                            $datatable->aria->$src = $jsonData;
                                        }
                                    } elseif (count($sources) == 2) {
                                        $src = $sources[1];
                                        $datatable->$src = $jsonData;
                                    }
                                }
                            }
                        }
                    }

                    $strxml = $doc->saveXML();
                    $handleXml = fopen($ouputdirXml . '/messages.' . $locale . '.xlf', "w");
                    fwrite($handleXml, $strxml);
                    fclose($handleXml);

                    $handleJson = fopen($ouputdirJson . '/'. strtolower($locale) . '_' . strtoUpper($locale) . '.json', "w");
                    fwrite($handleJson, json_encode($datatable));
                    fclose($handleJson);

                    fclose($inputFile);
                }
            }
        }

        $io->info('Translation files are created successfully');
        return Command::SUCCESS;
    }

}
