<?php

namespace App\Command;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:remove-user',
    description: 'Remove a user',
)]
class RemoveUserCommand extends Command
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
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'user id')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $idUser = $input->getOption('id');

	    /** @var UserRepository $userRepository */
	    $userRepository = $this->entityManager->getRepository(User::class);

		$user = $userRepository->find($idUser);
	    if (!$user) {
		    $io->warning('user does not exist');
		    return Command::FAILURE;
	    } else {
		    foreach ($user->getProfils() as $profil) {
			    $user->removeProfil($profil);
			}
			$this->entityManager->remove($user);
			$this->entityManager->flush();
	    }

		$io->info(sprintf('User with id %s is removed successfully', $idUser));
        return Command::SUCCESS;
    }

}
