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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Create a user admin: Example: php bin/console app:create-admin --phone=+221771029929 --username=admin --email=bb@ddd.com --password=pass',
)]
class CreateAdminUserCommand extends Command
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
            ->addOption('phone', null, InputOption::VALUE_REQUIRED, 'phone of user')
            ->addOption('username', null, InputOption::VALUE_REQUIRED, 'username')
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'Email')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Password')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $phone = $input->getOption('phone');
        $email = $input->getOption('email');
        $username = $input->getOption('username');
        $password = $input->getOption('password');

	    if ($this->_userIsExist($username)) {
		    $io->warning('user is already exist');
	    } else {
			if (!$this->_adminRoleExist()) {
				$this->entityManager->persist(new Role(Role::ROLE_ADMIN));
				$this->entityManager->flush();
			}

		    if (strlen($password) < 6 ) {
			    $io->error('The password must have at least 6 characters');
				return Command::FAILURE;
		    }

			$user = new User();
			$user = $user
				->setUsername($username)
				->setPassword($this->hasher->hashPassword($user, $password))
				->setPhone($phone)
				->setEmail($email)
				->setEnabled(true)
				->addProfil($this->entityManager->getRepository(Role::class)->findOneByRole(Role::ROLE_ADMIN));

			$this->entityManager->persist($user);
			$this->entityManager->flush();
	    }

		$io->info('User is created successfully');
        return Command::SUCCESS;
    }

	private function _userIsExist(string $username): bool {
		$result = $this->entityManager->createQueryBuilder()
			->select('u.id')
			->from(User::class, 'u')
			->where('u.username = :username')
			->setParameter('username', $username)
			->getQuery()
			->getResult();

		return count($result) > 0;
	}

	private function _adminRoleExist(): bool {
		$result = $this->entityManager->createQueryBuilder()
			->select('r.id')
			->from(Role::class, 'r')
			->where('r.role = :role')
			->setParameter('role', Role::ROLE_ADMIN)
			->getQuery()
			->getResult();

		return count($result) > 0;
	}

}
