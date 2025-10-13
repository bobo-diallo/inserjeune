<?php

namespace App\Command;

use App\Entity\City;
use App\Entity\Country;
use App\Entity\PersonDegree;
use App\Entity\Region;
use App\Entity\School;
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
    name: 'app:delete:data:countries',
    description: 'Delete all data of the given countries. Example: php bin/console app:delete:data:countries --countryIds=1,2,3',
)]
class DeleteDataForCountriesCommand extends Command
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
            ->addOption('countryIds', null, InputOption::VALUE_REQUIRED, 'country IDs to delete (comma-separated)')
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
            return Command::FAILURE;
        }

        $io = new SymfonyStyle($input, $output);
        $countryIdsInput = $input->getOption('countryIds');

        // Convertir la chaîne en tableau d'IDs
        $countryIds = array_map('intval', explode(',', $countryIdsInput));

        // Filtrer les IDs valides
        $countryIds = array_filter($countryIds, function($id) {
            return $id > 0;
        });

        if (empty($countryIds)) {
            $io->error('No valid country IDs provided');
            return Command::FAILURE;
        }

        $progressBar = new ProgressBar($output);
        $progressBar->start();

        $this->entityManager->beginTransaction();
        try {
            // Récupérer tous les pays à supprimer
            $countriesToDelete = $this->entityManager->getRepository(Country::class)
                ->findBy(['id' => $countryIds]);

            if (empty($countriesToDelete)) {
                $io->warning('No countries found with the provided IDs');
                return Command::FAILURE;
            }

            $io->text(sprintf('Found %d countries to delete', count($countriesToDelete)));

            // Trouver TOUTES les entités liées aux pays à supprimer (dans l'ordre inverse des dépendances)

            // 1. D'abord les PersonDegree (qui référencent les écoles et les pays)
            $personDegreesToDelete = $this->findPersonDegreesInCountryIds($countryIds);

            // 2. Les écoles (qui référencent les villes)
            $schoolsToDelete = $this->findSchoolsInCountryIds($countryIds);

            // 3. Les utilisateurs (qui référencent les pays)
            $usersToDelete = $this->findUsersInCountryIds($countryIds);

            // 4. Les villes
            $citiesToDelete = $this->findCitiesInCountryIds($countryIds);

            // 5. Les régions
            $regionsToDelete = $this->findRegionsInCountryIds($countryIds);

            $personDegreeCount = count($personDegreesToDelete);
            $schoolCount = count($schoolsToDelete);
            $userCount = count($usersToDelete);
            $cityCount = count($citiesToDelete);
            $regionCount = count($regionsToDelete);
            $countryCount = count($countriesToDelete);

            $totalProgress = $personDegreeCount + $schoolCount + $userCount + $cityCount + $regionCount + $countryCount;

            $i = 0;

            $io->text(sprintf('Deleting %d person degrees, %d schools, %d users, %d cities, %d regions and %d countries...',
                $personDegreeCount, $schoolCount, $userCount, $cityCount, $regionCount, $countryCount));

            // Supprimer dans l'ordre inverse des dépendances

            // 1. PersonDegree (les plus dépendants)
            foreach ($personDegreesToDelete as $personDegree) {
                $this->entityManager->remove($personDegree);
                $i++;
                $progressBar->setMessage(sprintf('Deleting person degrees %d of %d', $i, $totalProgress));
                $progressBar->advance();
            }

            // 2. Schools
            foreach ($schoolsToDelete as $school) {
                $this->entityManager->remove($school);
                $i++;
                $progressBar->setMessage(sprintf('Deleting schools %d of %d', $i, $totalProgress));
                $progressBar->advance();
            }

            // 3. Users
            foreach ($usersToDelete as $user) {
                $this->entityManager->remove($user);
                $i++;
                $progressBar->setMessage(sprintf('Deleting users %d of %d', $i, $totalProgress));
                $progressBar->advance();
            }

            // 4. Cities
            foreach ($citiesToDelete as $city) {
                $this->entityManager->remove($city);
                $i++;
                $progressBar->setMessage(sprintf('Deleting cities %d of %d', $i, $totalProgress));
                $progressBar->advance();
            }

            // 5. Regions
            foreach ($regionsToDelete as $region) {
                $this->entityManager->remove($region);
                $i++;
                $progressBar->setMessage(sprintf('Deleting regions %d of %d', $i, $totalProgress));
                $progressBar->advance();
            }

            // 6. Enfin les pays (les moins dépendants)
            foreach ($countriesToDelete as $country) {
                $this->entityManager->remove($country);
                $i++;
                $progressBar->setMessage(sprintf('Deleting countries %d of %d', $i, $totalProgress));
                $progressBar->advance();
            }

            $this->entityManager->flush();
            $this->entityManager->commit();

        } catch (\Exception $e) {
            $this->entityManager->rollback();
            $io->error('Error during deletion: ' . $e->getMessage());
            throw $e;
        }

        $progressBar->finish();
        $io->success(sprintf('Successfully deleted countries with IDs: %s and all related data', $countryIdsInput));
        return Command::SUCCESS;
    }

    /**
     * @param array $countryIds
     * @return PersonDegree[]
     */
    private function findPersonDegreesInCountryIds(array $countryIds): array
    {
        $qb = $this->entityManager->createQueryBuilder();

        // Trouver tous les PersonDegree qui ont une relation avec les pays à supprimer
        return $qb->select('pd')
            ->from(PersonDegree::class, 'pd')
            ->leftJoin('pd.school', 's')
            ->leftJoin('s.city', 'sc')
            ->leftJoin('sc.region', 'sr')
            ->leftJoin('sr.country', 'scountry')
            ->leftJoin('pd.residenceCountry', 'rc')
            ->leftJoin('pd.residenceRegion', 'rr')
            ->leftJoin('rr.country', 'rcountry')
            ->where('scountry.id IN (:countryIds)')
            ->orWhere('rc.id IN (:countryIds)')
            ->orWhere('rcountry.id IN (:countryIds)')
            ->setParameter('countryIds', $countryIds)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $countryIds
     * @return School[]
     */
    private function findSchoolsInCountryIds(array $countryIds): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('s')
            ->from(School::class, 's')
            ->join('s.city', 'c')
            ->join('c.region', 'r')
            ->join('r.country', 'co')
            ->where('co.id IN (:countryIds)')
            ->setParameter('countryIds', $countryIds)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $countryIds
     * @return User[]
     */
    private function findUsersInCountryIds(array $countryIds): array
    {
        // Utilisateurs par pays direct
        return $this->entityManager->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->leftJoin('u.country', 'c')
            ->leftJoin('u.residenceCountry', 'rc')
            ->where('c.id IN (:countryIds)')
            ->orWhere('rc.id IN (:countryIds)')
            ->setParameter('countryIds', $countryIds)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $countryIds
     * @return Region[]
     */
    private function findRegionsInCountryIds(array $countryIds): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('r')
            ->from(Region::class, 'r')
            ->join('r.country', 'c')
            ->where('c.id IN (:countryIds)')
            ->setParameter('countryIds', $countryIds)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $countryIds
     * @return City[]
     */
    private function findCitiesInCountryIds(array $countryIds): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('ct')
            ->from(City::class, 'ct')
            ->join('ct.region', 'r')
            ->join('r.country', 'c')
            ->where('c.id IN (:countryIds)')
            ->setParameter('countryIds', $countryIds)
            ->getQuery()
            ->getResult();
    }
}