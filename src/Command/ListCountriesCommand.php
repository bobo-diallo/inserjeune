<?php

namespace App\Command;

use App\Entity\Country;
use App\Entity\Currency;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:list-countries',
    description: 'List all countries',
)]
class ListCountriesCommand extends Command
{
	private EntityManagerInterface $entityManager;

	public function __construct(EntityManagerInterface $entityManager) {
		$this->entityManager = $entityManager;

		parent::__construct();
	}

	protected function configure(): void
    {
        $this
            // ->addArgument('id', InputArgument::OPTIONAL, 'Country ID')
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'Country ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $countryId = $input->getOption('id');

        if ($countryId) {
	        /** @var Country $county */
	        $county = $this->entityManager->getrepository(Country::class)
		        ->find($countryId);

	        if ($county) {
		        $io->table(
			        ['Id', 'Name', 'isoCode', 'phoneCode', 'phoneDigit', 'Currency'],
			        [
				        [$county->getId(),
					        $county->getName(),
					        $county->getIsoCode(),
					        $county->getPhoneCode(),
					        $county->getPhoneDigit(),
					        $county->getCurrency()->getIsoSymbol()]
			        ]
		        );

		        $io->text(sprintf("%s's regions", $county->getName()));
		        $regions = [];
		        foreach ($county->getRegions() as $region) {
			        $regions[] = [$region->getId(), $region->getName()];
		        }
		        $io->table(['Id', 'Name'], $regions);
	        }

        } else {
			$counties = $this->entityManager->createQueryBuilder()
				->select('c.id, c.name, c.isoCode, c.phoneCode, c.phoneDigit')
				->from(Country::class, 'c')
				->getQuery()
				->getResult();

			$io->table(['Id', 'Name', 'isoCode', 'phoneCode', 'phoneDigit'], $counties);
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }


}
