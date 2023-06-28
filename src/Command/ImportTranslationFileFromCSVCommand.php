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
    description: 'Import translation files from csv: Example: php bin/console app:import-translation-from-csv --csv=c:/temp/file.csv --ouputdir=c:/temp',
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
            ->addOption('ouputdir', null, InputOption::VALUE_REQUIRED, 'output directory')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $csv = $input->getOption('csv');
        $ouputdir = $input->getOption('ouputdir');
        $filesystem = new Filesystem();
        $error = false;

        //test if input file exist
        if (!$filesystem->exists($csv)) {
            $io->error('csv file does not exist : ' . $csv);
            $error = true;
        }
        //test if output dir exist
        if (!$filesystem->exists($ouputdir)) {
            $io->error('output directory does not exist : ' . $ouputdir);
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

                // looking for field number for each country
                $firstRow = fgetcsv($inputFile);
                $fields = explode(";", $firstRow[0]);

                $localeField = -1;

                for ($i = 0; $i < count($fields); $i++) {
                    if ( $fields[$i] == $locale) {
                        $localeField = $i;
                    }
                 }
                // echo ("---->>>" . $locale . " " . $localeField);

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
                                $transunit->appendChild($target);
                            }
                        }
                    }

                    $strxml = $doc->saveXML();
                    $handle = fopen($ouputdir . '/messages.' . $locale . '.xlf', "w");
                    fwrite($handle, $strxml);
                    fclose($handle);
                    fclose($inputFile);
                }
            }
        }

        $io->info('Translation files are created successfully');
        return Command::SUCCESS;
    }

}
