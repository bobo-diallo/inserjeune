<?php

namespace App\Command;

use App\Entity\City;
use App\Entity\Country;
use App\Entity\Currency;
use App\Entity\Region;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:clean:database:country',
    description: 'Delete all data except that of the given country. Example: php bin/console app:clean:database:country --countryId=1',
)]
class CleanDatabaseForCountryCommand extends Command
{
	private EntityManagerInterface $entityManager;
	private UserPasswordHasherInterface $hasher;

	public function __construct(
		EntityManagerInterface $entityManager,
		UserPasswordHasherInterface $hasher
	) {
		$this->entityManager = $entityManager;
		$this->hasher = $hasher;

		parent::__construct();
	}

	protected function configure(): void
    {
        $this
            ->addOption('countryId', null, InputOption::VALUE_REQUIRED, 'country id')
        ;
    }

	/**
	 * @throws \Exception
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
    {
	    $questionHelper = new QuestionHelper();
	    $question = new ConfirmationQuestion('Are you sure you want to run this command ? Note that the command will delete data in the database (y/n) ', false);

	    if (!$questionHelper->ask($input, $output, $question)) {
		    $output->writeln('The command has been canceled.');
		    return 1;
	    }

        $io = new SymfonyStyle($input, $output);
        $countryId = $input->getOption('countryId');

	    $progressBar = new ProgressBar($output);
	    $progressBar->start();

	    $this->entityManager->beginTransaction();
		try {
			/** @var ?Country $country */
			$country = $this->entityManager->getRepository(Country::class)->find($countryId);

			if ($country) {
				$country->setValid(true);
				$this->entityManager->persist($country);

				$phoneCodePattern = sprintf('+%s%%', $country->getPhoneCode());
				$users = $this->findUsersWithPhoneNotLike($phoneCodePattern);
				$countries = $this->findCountriesNotLike($countryId);

				$otherCountries = $this->entityManager
					->createQueryBuilder()
					->select('c')
					->from(Country::class, 'c')
					->where('c.id != :countryId')
					->setParameter('countryId', $countryId)
					->getQuery()
					->getResult();
				$currencies = $this->entityManager->getRepository(Currency::class)->findAll();
				$regions = $this->entityManager
					->createQueryBuilder()
					->select('r')
					->from(Region::class, 'r')
					->join('r.country', 'c')
					->where('c.id != :countryId')
					->setParameter('countryId', $countryId)
					->getQuery()
					->getResult();
				$cities = $this->entityManager
					->createQueryBuilder()
					->select('ct')
					->from(City::class, 'ct')
					->join('ct.region', 'r')
					->join('r.country', 'c')
					->where('c.id != :countryId')
					->setParameter('countryId', $countryId)
					->getQuery()
					->getResult();

				$userCount = count($users);
				$countryCount = count($countries);
				$totalProgress = $userCount + $countryCount;

				$i = 0;

				$io->text(sprintf('Deleting all users whose phone does not start with %s ...', $phoneCodePattern));
				$io->text(sprintf('%s users and %s countries to delete', $userCount, $countryCount));

				foreach ($users as $user) {
					$this->entityManager->remove($user);
					$i++;

					$progressBar->setMessage(sprintf('Processing %d of %d', $i, $totalProgress));
					$progressBar->advance();
				}

				foreach ($countries as $country) {
					$this->entityManager->remove($country);
					$i++;
					$progressBar->setMessage(sprintf('Processing %d of %d', $i, $totalProgress));
					$progressBar->advance();
				}

				$i = 0;

				$io->text('Add currencies, countries, regions and cities %s ...');
				$$totalProgress = count($currencies) + count($otherCountries) + count($regions) + count($cities);
				foreach ($currencies as $currency) {
					$this->entityManager->persist($currency);
					$i++;

					$progressBar->setMessage(sprintf('Processing %d of %d', $i, $totalProgress));
					$progressBar->advance();
				}

				/** @var Country $otherCountry */
				foreach ($otherCountries as $otherCountry) {
					$otherCountry->setValid(false);
					$this->entityManager->persist($otherCountry);
					$i++;

					$progressBar->setMessage(sprintf('Processing %d of %d', $i, $totalProgress));
					$progressBar->advance();
				}

				foreach ($regions as $region) {
					$this->entityManager->persist($region);
					$i++;

					$progressBar->setMessage(sprintf('Processing %d of %d', $i, $totalProgress));
					$progressBar->advance();
				}

				foreach ($cities as $city) {
					$this->entityManager->persist($city);
					$i++;

					$progressBar->setMessage(sprintf('Processing %d of %d', $i, $totalProgress));
					$progressBar->advance();
				}

				$this->entityManager->flush();
				$this->entityManager->commit();
			} else {
				$io->warning('country does not exist');
				return Command::FAILURE;
			}

		} catch (\Exception $e) {
			$this->entityManager->rollback();
			throw $e;
		}

	    $progressBar->finish();
	    $io->info(sprintf('Yes!! Only data of country ID %s is available', $countryId));
	    return Command::SUCCESS;
    }

	/**
	 * @param string $pattern
	 * @return User[]
	 */
	private function findUsersWithPhoneNotLike(string $pattern): array {
		return $this->entityManager->createQueryBuilder()
			->select('u')
			->from(User::class, 'u')
			->where('u.phone NOT LIKE :pattern')
			->setParameter('pattern', $pattern)
			->getQuery()
			->getResult();
	}

	/**
	 * @param int $countryId
	 * @return Country[]
	 */
	private function findCountriesNotLike(int $countryId): array {
		return $this->entityManager->createQueryBuilder()
			->select('c')
			->from(Country::class, 'c')
			->where('c.id NOT LIKE :countryId')
			->setParameter('countryId', $countryId)
			->getQuery()
			->getResult();
	}

}
