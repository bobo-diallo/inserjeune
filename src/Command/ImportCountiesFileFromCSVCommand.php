<?php

namespace App\Command;

use App\Entity\Currency;
use App\Entity\Region;
use App\Entity\Country;
use App\Entity\City;
use App\Repository\CurrencyRepository;
use App\Repository\CountryRepository;
use App\Repository\RegionRepository;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;

#[AsCommand(
    name: 'app:import-countries-from-csv',
    description: 'Import countries files from csv: Example: php bin/console app:import-countries-from-csv --csv=c:/temp/file.csv --f=true',
)]
class ImportCountiesFileFromCSVCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private ParameterBagInterface $params;
    private CountryRepository $countryRepository;
    private CurrencyRepository $currencyRepository;
    private RegionRepository $regionRepository;
    private CityRepository $cityRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params,
        CountryRepository $countryRepository,
        RegionRepository $regionRepository,
        CityRepository $cityRepository,
        CurrencyRepository $currencyRepository,
    ) {
        $this->entityManager = $entityManager;
        $this->params = $params;
        $this->countryRepository = $countryRepository;
        $this->regionRepository = $regionRepository;
        $this->cityRepository = $cityRepository;
        $this->currencyRepository = $currencyRepository;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('csv', null, InputOption::VALUE_REQUIRED, 'csv file')
            ->addOption('f', null, InputOption::VALUE_OPTIONAL, 'force import')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $csv = $input->getOption('csv');
        $forceImport = $input->getOption('f');
        $filesystem = new Filesystem();
        $error = false;
        $logStr = "";

        $missingCountries = [];
        $missingRegions = [];
        $missingCurrencies = [];
        $missingCities = [];
        $persistedCurrencies = [];
        $persistedCountries = [];
        $persistedRegions = [];
        $persistedCities = [];
        $dbNullCountry = null;

        //test if input file exist
        if (!$filesystem->exists($csv)) {
            $io->error('csv file does not exist : ' . $csv);
            $error = true;
        }

        if ($error) {
            $io->error('command aborted');
        } else {
            // propose to do database sql backup
            $message = "Before updating your database, do you want to make a backup file [y/n]?" ;
            print $message;
            $confirmation  =  trim( fgets( STDIN ) );
            if ( $confirmation !== 'y' ) {
                $io->warning( "Please note your database will not be saved, type enter to continue or Ctrl + c");
                fgets( STDIN );
            } else {
                $dbName = "";
                $dbPasswd = "";

                if($_ENV['DATABASE_URL']) {
                    $connectParams = explode(':', $_ENV['DATABASE_URL']);
                    if(count($connectParams) == 4) {
                        $dbUser = str_replace('/', '', $connectParams[1]);
                        $dbPasswdExplode = explode('@', $connectParams[2]);
                        if (count($dbPasswdExplode)>0) {
                            $dbPasswd = $dbPasswdExplode[0];
                        }
                        $dbNameExplode = explode('/',$connectParams[3]);
                        if (count($dbNameExplode)>1) {
                            $dbName = $dbNameExplode[1];
                        }

                        if($dbUser && $dbPasswd && $dbName) {
                            $date = (new \DateTime())->format('YmdHis');
                            $commandBackup = 'mysqldump -u' . $dbUser . ' -p' . $dbPasswd . ' ' . $dbName . " > " . $dbName . "_" . $date . '.sql';
                            exec($commandBackup);
                        }
                    }
                }
            }

            // read input csv file
            $inputFile = fopen($csv, 'rt');

            $countries = $this->countryRepository->findAll();
            $regions = $this->regionRepository->findAll();
            if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY']=="true") {
                $countries = $this->regionRepository->findAll();
            }
            $cities = $this->cityRepository->findAll();
            $currencies = $this->currencyRepository->findAll();

            if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY']=="true") {
                $dbNullCountry = $this->countryRepository->findOneBy(['name'=> "NULL"]);
                if(!$dbNullCountry) {
                    $nullCountry = new Country();
                    $nullCountry->setName("NULL");
                    $this->entityManager->persist($nullCountry);
                    $this->entityManager->flush();
                    $dbNullCountry = $this->countryRepository->findOneBy(['name'=> "NULL"]);
                }
            }

            $index = 0;
            $titles = fgetcsv($inputFile, 1000, ";");
            $numPays = -1;
            $numTransPays = -1;
            $numCapitales = -1;
            $numRegionCapitales = -1;
            $numIndTel = -1;
            $numNbDigits = -1;
            $numIsoPays = -1;
            $numIsoNameCurrency = -1;
            $numIsoNumCurrency = -1;
            $numIsoSymCurrency = -1;
            $numCurrency = -1;

            foreach ($titles as $key => $value){
                // echo $key . ": " . utf8_encode($value);
                if (utf8_encode($value) == "Pays") $numPays = $key;
                if (utf8_encode($value) == "Trans Pays") $numTransPays = $key;
                if (utf8_encode($value) == "Capitale") $numCapitales = $key;
                if (utf8_encode($value) == "Région capitale") $numRegionCapitales = $key;
                if (utf8_encode($value) == "Indicatif téléphonique") $numIndTel = $key;
                if (utf8_encode($value) == "Nb Digits") $numNbDigits = $key;
                if (utf8_encode($value) == "ISO Pays") $numIsoPays = $key;
                if (utf8_encode($value) == "Nom ISO Devise") $numIsoNameCurrency = $key;
                if (utf8_encode($value) == "Numéro ISO Devise") $numIsoNumCurrency = $key;
                if (utf8_encode($value) == "Symbole ISO Devise") $numIsoSymCurrency = $key;
                if (utf8_encode($value) == "Devise") $numCurrency = $key;
            }

            if(($numPays == -1) ||
                ($numTransPays == -1) ||
                ($numCapitales == -1) ||
                ($numIndTel == -1) ||
                ($numNbDigits == -1) ||
                ($numIsoPays == -1) ||
                ($numIsoNameCurrency == -1) ||
                ($numIsoNumCurrency == -1) ||
                ($numIsoSymCurrency == -1) ||
                ($numCurrency == -1))  {
                echo "fields error in csv file; must contain:\n";
                echo "Pays;  Capitale; Région Capitale; Indicatif téléphonique; Nb Digit; ISO Pays; Nom ISO Devise; Numéro ISO Devise; Symbole ISO Devise; Devise\n";
                return Command::FAILURE;
            } else {
                $csvCountries = [];
                $csvCountryCodes = [];
                $csvRegions = [];
                $csvCurrencies = [];
                $csvCities = [];
                $correspondingCountries = [];
                $correspondingRegions = [];
                $correspondingCities = [];
                $correspondingCurrencies = [];
                $supCsvCountries = [];
                $supCsvRegions = [];
                $supCsvCities = [];
                $supCsvCurrencies = [];

                echo "Readind CSV Datas and checking currencies in your database :\n\n";
                while (($row = fgetcsv($inputFile, 1000, ";")) !== FALSE) {
                    if (strlen(trim(utf8_encode($row[$numPays])) > 0)) {
                        // foreach ($countries as $country) {
                        /* Création de la devise si inexistante */
                        $currencyExist = false;

                        $newCurrency = new Currency();
                        $newCurrency->setName(utf8_encode($row[$numCurrency]));
                        $newCurrency->setIsoName(utf8_encode($row[$numIsoNameCurrency]));
                        $newCurrency->setIsoNum(utf8_encode($row[$numIsoNumCurrency]));
                        $newCurrency->setIsoSymbol(utf8_encode($row[$numIsoSymCurrency]));
                        $csvCurrencies[] = $newCurrency;

                        // analyse currencies
                        foreach ($currencies as $currency) {
                            $currencyName = $currency->getName();
                            if (strtolower($currencyName) == strtolower(utf8_encode($row[$numCurrency]))) {
                                $correspondingCurrencies[] = $currency;
                                $currencyExist = true;
                            }
                        }
                        if (!$currencyExist) {
                            // avoid duplicates
                            $existInArray = false;
                            foreach ($supCsvCurrencies as $supCsvCurrency) {
                                if ($newCurrency->getName() == $supCsvCurrency->getName()) {
                                    $existInArray = true;
                                }
                            }
                            if (!$existInArray) {
                                $supCsvCurrencies[] = $newCurrency;
                            }
                        }
                    }
                }


                echo "Readind CSV Datas and checking countries, regions and cities in your database :\n\n";
                // go back to 2nd line of inputFile
                fseek($inputFile, 0); fgetcsv($inputFile, 1000, ";");

                while (($row = fgetcsv($inputFile, 1000, ";")) !== FALSE) {
                    if(strlen(trim(utf8_encode($row[$numPays])) >0 )) {
                        // foreach ($countries as $country) {
                        $countryExist = false;
                        $regionExist = false;
                        $cityExist = false;

                        if ($_ENV['STRUCT_PROVINCE_COUNTRY_CITY']=="true") {
                            $newCountry = new Region();
                            $newRegion = null;
                        } else {
                            $newCountry = new Country();
                            $newRegion = new Region();
                        }
                        $newCity = new City();

                        // acquisition of countries
                        $newCountry->setName(utf8_encode($row[$numPays]));
                        if($row[$numTransPays]) {
                            $newName = $newCountry->getName() . ':' . utf8_encode($row[$numTransPays]);
                            $newCountry->setName($newName);
                        }

                        $newCountry->setPhoneCode(intval($row[$numIndTel]));
                        $newCountry->setPhoneDigit(intval($row[$numNbDigits]));
                        $newCountry->setIsoCode(utf8_encode($row[$numIsoPays]));
                        $newCurrency = new Currency();
                        $newCurrency->setName(utf8_encode($row[$numCurrency]));
                        $newCountry->setCurrency($newCurrency);
                        if ($_ENV['STRUCT_PROVINCE_COUNTRY_CITY']=="true") {
                            $newCountry->setCountry($dbNullCountry);
                        }
                        $csvCountries[] = $newCountry;

                        // acquisition of regions
                        if ($_ENV['STRUCT_PROVINCE_COUNTRY_CITY']!="true") {
                            $newRegion->setName(utf8_encode($row[$numCapitales]));
                            if(utf8_encode($row[$numRegionCapitales])) {
                                $newRegion->setName(utf8_encode($row[$numRegionCapitales]));
                            }
                            $newRegion->setCountry($newCountry);
                            $csvRegions[] = $newRegion;
                        }

                        // acquisition of cities
                        if(utf8_encode($row[$numCapitales])) {
                            $newCity->setName(utf8_encode($row[$numCapitales]));
                            $newCity->setIsCapital(true);
                            if ($_ENV['STRUCT_PROVINCE_COUNTRY_CITY']=="true") {
                                $newCity->setRegion($newCountry);
                            } else {
                                $newCity->setRegion($newRegion);
                                $newRegion->setCountry($newCountry);
                            }
                            $csvCities[] = $newCity;
                            // echo "--> " . $newCity->getName() . " | " . $newCity->getRegion()->getName() . " | " . $newCity->getRegion()->getCountry()->getName() . "\n";
                        }

                        // analyse countries
                        foreach ($countries as $country) {
                            //si le nom de traduction du pays existe dans le champ Trans Pays
                            if (strtolower($country->getName()) == strtolower(utf8_encode($row[$numTransPays]))) {
                                // echo utf8_encode($row[$numPays]) . " trouvé (". $country->getName() .")\n";
                                // $country->setName($country->getName() . ':' . utf8_encode($row[$numTransPays]));
                                $correspondingCountries[] = $country;
                                $countryExist = true;
                            }
                        }
                        if (!$countryExist) {
                            // sinon recherche avec le champ Pays
                            foreach ($countries as $country) {
                                if (strtolower($country->getName()) == strtolower(utf8_encode($row[$numPays]))) {
                                    // echo utf8_encode($row[$numPays]) . " trouvé (". $country->getName() .")\n";
                                    // if($row[$numTransPays]) {
                                    //     $country->setName($country->getName() . ':' . utf8_encode($row[$numTransPays]));
                                    // }
                                    $correspondingCountries[] = $country;
                                    $countryExist = true;
                                }
                            }
                        }
                        if (!$countryExist) {
                            $supCsvCountries[] = $newCountry;
                        }

                        //analyse regions
                        if ($_ENV['STRUCT_PROVINCE_COUNTRY_CITY']!="true") {
                            foreach ($regions as $region) {
                                $regionName = $region->getName();
                                if (strtolower($regionName) == strtolower($newRegion->getName())) {
                                    $correspondingRegions[] = $region;
                                    $regionExist = true;
                                }
                            }
                            if (!$regionExist) {
                                $supCsvRegions[] = $newRegion;
                            }
                        }

                        //analyse cities
                        foreach ($cities as $city) {
                            if (strtolower(str_replace('-',' ', $city->getName())) == strtolower(str_replace('-',' ', $newCity->getName()))) {
                                $correspondingCities[] = $city;
                                $cityExist = true;
                            }
                        }
                        if (!$cityExist) {
                            $supCsvCities[] = $newCity;
                        }
                    }
                }
                fclose($inputFile);
echo "test fin de lecture csv" . "\n";
                /*******************************************************************************/
                // recherche des devises de la bdd non existants dans le csv et des différences
                /*******************************************************************************/
                $logStr .= "***\nModification of parameters currencies :\n***\n";
                foreach ($currencies as $currency) {
                    $currencyExist =false;
                    foreach ($csvCurrencies as $csvCurrency) {
                        if (strtolower($currency->getName()) == strtolower($csvCurrency->getName())) {
                            $currencyExist = true;
                            if(($csvCurrency->getIsoName() != null) && ($currency->getIsoName() != $csvCurrency->getIsoName())) {
                                $logStr .= "\tCurrency " . $currency->getName() . " modify iso_name [" . $currency->getIsoName() . "] by [" . $csvCurrency->getIsoName() . "]\n";
                                $currency->setIsoName($csvCurrency->getIsoName());
                            }
                            if(($csvCurrency->getIsoNum() != null) && ($currency->getIsoNum() != $csvCurrency->getIsoNum())) {
                                $logStr .= "\tCurrency " . $currency->getName() . " modify iso_num [" . $currency->getIsoNum() . "] by [" . $csvCurrency->getIsoNum() . "]\n";
                                $currency->setIsoNum($csvCurrency->getIsoNum());
                            }
                            if(($csvCurrency->getIsoSymbol() != null) && ($currency->getIsoSymbol() != $csvCurrency->getIsoSymbol())) {
                                $logStr .= "\tCurrency " . $currency->getName() . " modify iso_symbol [" . $currency->getIsoSymbol() . "] by [" . $csvCurrency->getIsoSymbol() . "]\n";
                                $currency->setIsoSymbol($csvCurrency->getIsoSymbol());
                            }
                        }
                    }
                    if (!$currencyExist) {
                        $missingCurrencies[] = $currency;
                    } else {
                        // prepare to save to bdd
                        if(!$currency->getIsoNum()) $currency->setIsoNum("NULL");
                        if(!$currency->getIsoSymbol()) $currency->setIsoSymbol("NULL");

                        $persistedCurrencies[] = $currency;
                    }
                }
                // echo "Nombre de devises conformes: " . count($correspondingCurrencies) . "\n";
                $logStr .= "\n***\nList of compliant currencies in CSV file :(" . count($correspondingCurrencies) . ")\n***\n";
                foreach ($correspondingCurrencies as $correspondingCurrency) {
                    $logStr .= "\t" . $correspondingCurrency->getName() . "\n";
                }

                // echo "Nombre de devises inexistants dans le fichier CSV: " . count($missingCurrencies) . "\n";
                $logStr .= "\n***\nList of currencies that do not exist in the CSV file (" . count($missingCurrencies) . ")\n***\n";
                foreach ($missingCurrencies as $missingCurrency) {
                    $logStr .= "\t" . $missingCurrency->getName() . "\n";
                }

                // echo "Nombre de devises supplémentaires du fichier CSV: " . count($supCsvCountries) . "\n";
                $logStr .= "\n***\nList of additional currencies in CSV files : (" . count($supCsvCurrencies) . ")\n***\n";
                foreach ($supCsvCurrencies as $supCsvCurrency) {
                    $logStr .= "\t" . $supCsvCurrency->getName() . "\n";

                    if(!$supCsvCurrency->getIsoNum()) $supCsvCurrency->setIsoNum("NULL");
                    if(!$supCsvCurrency->getIsoSymbol()) $supCsvCurrency->setIsoSymbol("NULL");

                    // prepare to save to bdd
                    $persistedCurrencies[] = $supCsvCurrency;
                }

                /*******************************************************************************/
                // recherche des pays de la bdd non existants dans le csv et des différences
                /*******************************************************************************/
                $logStr .= "***\nModification of parameters countries :\n***\n";
                foreach ($countries as $country) {
                    $countryExist = false;
                    foreach ($csvCountries as $csvCountry) {
                        $countryLowerName = strtolower($country->getName());
                        $csvCountryNames = [];
                        if($csvCountry->getName()) {
                            $csvCountryPaysName = "";
                            $csvCountryTransName = "";
                            if(str_contains($csvCountry->getName(), ":")) {
                                $csvCountryPaysName = strtolower(strstr($csvCountry->getName(), ':', true));
                                $csvCountryTransName = strtolower(str_replace(":","",strstr($csvCountry->getName(), ':')));
                            }
                            if(($countryLowerName == $csvCountryPaysName) || ($countryLowerName == $csvCountryTransName)) {

                                if (($countryLowerName == $csvCountryPaysName)  && ($csvCountryTransName != ""))  {
                                    $logStr .= "\tCountry " . $country->getName() . " modify name [" . $country->getName() . "] by [" . $csvCountryTransName . "]\n";
                                    $country->setName($csvCountryTransName);
                                }
                                if (($country->getIsoCode() == null) || ($country->getIsoCode() != $csvCountry->getIsoCode())) {
                                    $logStr .= "\tCountry " . $country->getName() . " modify iso_code [" . $country->getIsoCode() . "] by [" . $csvCountry->getIsoCode() . "]\n";
                                    $country->setIsoCode($csvCountry->getIsoCode());
                                }
                                if (($country->getPhoneCode() == 0) || ($country->getPhoneCode() != $csvCountry->getPhoneCode())) {
                                    $logStr .= "\tCountry " . $country->getName() . " modify phone_code [" . $country->getPhoneCode() . "] by [" . $csvCountry->getPhoneCode() . "]\n";
                                    $country->setPhoneCode($csvCountry->getPhoneCode());
                                }
                                if (($country->getPhoneDigit() == 0) || ($country->getPhoneDigit() != $csvCountry->getPhoneDigit())) {
                                    $logStr .= "\tCountry " . $country->getName() . " modify phone_digit [" . $country->getPhoneDigit() . "] by [" . $csvCountry->getPhoneDigit() . "]\n";
                                    $country->setPhoneDigit($csvCountry->getPhoneDigit());
                                }

                                $dbCurrency = $this->currencyRepository->findOneBy(['name' => $csvCountry->getCurrency()->getName()]);
                                if ($dbCurrency) {
                                    $country->setCurrency($dbCurrency);
                                }
                                // prepare to save to bdd
                                $countryExist=true;

                                $persistedCountries[] = $country;
                            }
                        }
                    }
                    if(!$countryExist) {
                        $missingCountries[] = $country;
                    }
                }
                // echo "Nombre de pays conformes: " . count($correspondingCountries) . "\n";
                $logStr .= "\n***\nList of compliant countries in CSV file :(" . count($correspondingCountries) . ")\n***\n";
                foreach ($correspondingCountries as $correspondingCountry) {
                    $logStr .= "\t" . $correspondingCountry->getName() . "\n";
                }

                // echo "Nombre de pays inexistants dans le fichier CSV: " . count($missingCountries) . "\n";
                $logStr .= "\n***\nList of countries that do not exist in the CSV file (" . count($missingCountries) . ")\n***\n";
                foreach ($missingCountries as $missingCountry) {
                    $logStr .= "\t" . $missingCountry->getName() . "\n";
                }

                // echo "Nombre de pays supplémentaires du fichier CSV: " . count($supCsvCountries) . "\n";
                $logStr .= "\n***\nList of additional countries in CSV files : (" . count($supCsvCountries) . ")\n***\n";
                foreach ($supCsvCountries as $supCsvCountry) {
                    $logStr .= "\t" . $supCsvCountry->getName() . "\n";
                    $supCsvCountry->setValid(false);

                    // creation of the province NULL if non-existent
                    if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY']=="true") {
                        $supCsvCountry->setCountry($dbNullCountry);
                    }

                    // prepare to save to bdd
                    $persistedCountries[] = $supCsvCountry;
                }
            }

            /*******************************************************************************/
            // recherche des regions de la bdd non existants dans le csv et des différences
            /*******************************************************************************/
            //$logStr .= "***\nModification of parameters regions :\n***\n";
            // foreach ($regions as $region) {
            //     $regionExist = false;
            //     foreach ($csvRegions as $csvRegion) {
            //         if ($region->getName() == $csvRegion->getName()) {
            //             if ($region->getCountry->getName() == $csvRegion->getCountry->getName()){
            //                 $regionExist = true;
            //             }
            //         }
            //     }
            //     if(!$regionExist) {
            //         $missingRegions[] = $region;
            //     }
            // }
            foreach ($regions as $region) {
                $regionExist = false;
                foreach ($csvRegions as $csvRegion) {
                    $regionLowerName = strtolower($region->getName());
                    $csvRegionNames = [];
                    if($csvRegion->getName()) {
                        $csvRegionPaysName = "";
                        $csvRegionTransName = "";
                        if(str_contains($csvRegion->getName(), ":")) {
                            $csvRegionPaysName = strtolower(strstr($csvRegion->getName(), ':', true));
                            $csvRegionTransName = strtolower(str_replace(":","",strstr($csvRegion->getName(), ':')));
                        }

                        if(($regionLowerName == $csvRegionPaysName) || ($regionLowerName == $csvRegionTransName)) {
                            if (($region->getName() != $csvRegionTransName)  && (!$csvRegionTransName))  {
                                $logStr .= "\tRegion " . $region->getName() . " modify name [" . $region->getName() . "] by [" . $csvRegionTransName . "]\n";
                                $region->setName($csvRegionTransName);
                            }
                            if (($region->getIsoCode() == null) || ($region->getIsoCode() != $csvRegion->getIsoCode())) {
                                $logStr .= "\tRegion " . $region->getName() . " modify iso_code [" . $region->getIsoCode() . "] by [" . $csvRegion->getIsoCode() . "]\n";
                                $region->setIsoCode($csvRegion->getIsoCode());
                            }
                            if (($region->getPhoneCode() == 0) || ($region->getPhoneCode() != $csvRegion->getPhoneCode())) {
                                $logStr .= "\tRegion " . $region->getName() . " modify phone_code [" . $region->getPhoneCode() . "] by [" . $csvRegion->getPhoneCode() . "]\n";
                                $region->setPhoneCode($csvRegion->getPhoneCode());
                            }
                            if (($region->getPhoneDigit() == 0) || ($region->getPhoneDigit() != $csvRegion->getPhoneDigit())) {
                                $logStr .= "\tRegion " . $region->getName() . " modify phone_digit [" . $region->getPhoneDigit() . "] by [" . $csvRegion->getPhoneDigit() . "]\n";
                                $region->setPhoneDigit($csvRegion->getPhoneDigit());
                            }

                            $dbCurrency = $this->currencyRepository->findOneBy(['name' => $csvRegion->getCurrency()->getName()]);
                            if ($dbCurrency) {
                                $region->setCurrency($dbCurrency);
                            }
                            // prepare to save to bdd
                            $regionExist=true;
                            $persistedRegions[] = $region;
                        }
                    }
                }
                if(!$regionExist) {
                    $missingRegions[] = $region;
                }
            }

            // echo "Nombre de regions conformes: " . count($correspondingRegions) . "\n";
            $logStr .= "\n***\nList of compliant regions in CSV file :(" . count($correspondingRegions) . ")\n***\n";
            foreach ($correspondingRegions as $correspondingRegion) {
                $logStr .= "\t" . $correspondingRegion->getName() . "\n";
            }

            // echo "Nombre de regions inexistants dans le fichier CSV: " . count($missingRegions) . "\n";
            $logStr .= "\n***\nList of regions that do not exist in the CSV file (" . count($missingRegions) . ")\n***\n";
            foreach ($missingRegions as $missingRegion) {
                $logStr .= "\t" . $missingRegion->getName() . "\n";
            }

            // echo "Nombre de regions supplémentaires du fichier CSV: " . count($supCsvRegions) . "\n";
            $logStr .= "\n***\nList of additional regions in CSV files : (" . count($supCsvRegions) . ")\n***\n";
            foreach ($supCsvRegions as $supCsvRegion) {
                $logStr .= "\t" . $supCsvRegion->getName() . "\n";
                $supCsvRegion->setValid(false);

                // prepare to save to bdd
                $persistedRegions[] = $supCsvRegion;
            }

            /*******************************************************************************/
            // recherche des cities de la bdd non existants dans le csv et des différences
            /*******************************************************************************/
            //$logStr .= "***\nModification of parameters cities :\n***\n";
            foreach ($cities as $city) {
                $cityExist = false;
                foreach ($csvCities as $csvCity) {
                    if (strtolower(str_replace('-',' ', $city->getName())) == strtolower(str_replace('-',' ', $csvCity->getName()))) {
                        if ($city->getRegion()->getName() == $csvCity->getRegion()->getName()){
                            $cityExist = true;

                            // echo $city->getName() . ' | ' . $csvCity->getName() . "\n";
                        }
                    }
                }
                if(!$cityExist) {
                    $missingCities[] = $city;
                }
            }
            // echo "Nombre de cities conformes: " . count($correspondingCities) . "\n";
            $logStr .= "\n***\nList of compliant cities in CSV file :(" . count($correspondingCities) . ")\n***\n";
            foreach ($correspondingCities as $correspondingCity) {
                $logStr .= "\t" . $correspondingCity->getName() . "\n";
            }

            echo "Nombre de cities inexistants dans le fichier CSV: " . count($missingCities) . "\n";
            $logStr .= "\n***\nList of cities that do not exist in the CSV file (" . count($missingCities) . ")\n***\n";
            foreach ($missingCities as $missingCity) {
                $logStr .= "\t" . $missingCity->getName() . "\n";
            }

            // echo "Nombre de cities supplémentaires du fichier CSV: " . count($supCsvCities) . "\n";
            $logStr .= "\n***\nList of additional cities in CSV files : (" . count($supCsvCities) . ")\n***\n";
            foreach ($supCsvCities as $supCsvCity) {
                $logStr .= "\t" . $supCsvCity->getName() . "\n";

                // prepare to save to bdd
                $persistedCities[] = $supCsvCity;
            }

            $handle = fopen('import-countries-from-csv.log', "w");
            fwrite($handle, $logStr);
            fclose($handle);

            if($forceImport != "true" && count($missingCountries) >0 ) {
                echo count($missingCountries) ." countries are non-existent in the CSV file: " . "\n";
                $io->error("Please correct the country names in your database so that they match the CSV file, for more details, see import-countries-from-csv.log file or run again with option --f=true");
                return Command::FAILURE;
            }

        }

        // save Datas to Bdd
        if (count($missingCountries)>0 || count($missingCurrencies)>0 ) {
            if (count($missingCurrencies)>0) {
                $io->error('Import currencies are not created, please correct the names of currencies in Inserjeune application' .
                    ' for more details, see "List of currencies that do not exist in the CSV file" in import-countries-from-csv.log file');
            }
            if (count($missingCountries)>0) {
                $io->error('Import countries are not created, please correct the names of countries in Inserjeune application' .
                    ' for more details, see "List of countries that do not exist in the CSV file" in import-countries-from-csv.log file');
            }
            if (count($missingCities)>0) {
                $io->error('Import cities are not created, please correct the names of cities in Inserjeune application' .
                    ' for more details, see "List of cities that do not exist in the CSV file" in import-countries-from-csv.log file');
            }
            return Command::INVALID;

        } else {
            echo "Update " . count($persistedCurrencies) . " currencies in currency table \n";
            foreach ($persistedCurrencies as $persistedCurrency) {
                $this->entityManager->persist($persistedCurrency);
                $this->entityManager->flush();

            }
            echo "Update " . count($persistedCountries) . " countries in country table \n";
            foreach ($persistedCountries as $persistedCountry) {
                $dbCurrency = $this->currencyRepository->findOneBy(['name'=> $persistedCountry->getCurrency()->getName()]);
                if ($dbCurrency) {
                    $persistedCountry->setCurrency($dbCurrency);
                }
                $persistedCountryNames = explode(':', $persistedCountry->getName());
                if(count($persistedCountryNames) >1) {
                    $persistedCountry->setName($persistedCountryNames[1]);
                }
                $this->entityManager->persist($persistedCountry);
                // echo "countries: " . $persistedCountry->getName() . "\n";
                $this->entityManager->flush();
            }
            // die();
            if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY']!="true") {
                echo "Update " . count($persistedRegions) . " countries in region table \n";
                foreach ($persistedRegions as $persistedRegion) {
                    $dbCountry = $this->countryRepository->findOneBy(['name' => $persistedRegion->getCountry()->getName()]);

                    if(!$persistedRegion->getCountry())
                        $persistedRegion->setCountry($dbCountry);
                    $persistedRegionNames = explode(':', $persistedRegion->getName());
                    if(count($persistedRegionNames) >1) {
                        $persistedRegion->setName($persistedRegionNames[1]);
                    }

                    $this->entityManager->persist($persistedRegion);
                    $this->entityManager->flush();
                }
            }
            die();
            echo "Update " . count($persistedCities) . " cities in city table \n";
            foreach ($persistedCities as $persistedCity) {
                if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY']=="true") {
                    $dbCountry = $this->regionRepository->findOneBy(['name' => $persistedCity->getRegion()->getName()]);
                } else {
                    $dbCountry = $this->countryRepository->findOneBy(['name' => $persistedCity->getRegion()->getName()]);
                }
                if($dbCountry) {
                    $persistedCity->setRegion($dbCountry);
                    $this->entityManager->persist($persistedCity);
                    $this->entityManager->flush();
                } else {
                    $io->warning("City " . $persistedCity->getName() . " not imported in database, please correct the name and run again");
                }
            }
        }

        $io->info('Import countries are created successfully, for more details, see import-countries-from-csv.log file');
        return Command::SUCCESS;
    }
}
