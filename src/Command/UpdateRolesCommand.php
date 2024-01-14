<?php

namespace App\Command;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'app:update_roles',
    description: 'update roles in Database: Example: php bin/console app:update_roles',
)]
class UpdateRolesCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private ParameterBagInterface $params;
    private UserRepository $userRepository;
    private RoleRepository $roleRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params,
        RoleRepository $roleRepository,
        UserRepository $userRepository
    ) {
        $this->entityManager = $entityManager;
        $this->roleRepository = $roleRepository;
        $this->userRepository = $userRepository;
        $this->params = $params;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            // ->addOption('csv', null, InputOption::VALUE_REQUIRED, 'csv file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $counterRemove = 0;
        $counterAdded = 0;
        $counterUpdated = 0;

        $dbRoles = $this->roleRepository->findAll();
        $allRoles = [
            "ROLE_ADMIN"=>"SUPER ADMIN",
            "ROLE_ADMIN_PAYS"=>"ADMIN PAYS",
            "ROLE_ADMIN_REGIONS"=>"ADMIN N-JSO",
            "ROLE_ADMIN_VILLES"=>"ADMIN VILLES",
            "ROLE_DIRECTEUR"=>"DIRECTOR",
            "ROLE_LEGISLATEUR"=>"LEGISLATEUR",
            "ROLE_PRINCIPAL"=>"PRINCIPAL",
            "ROLE_ETABLISSEMENT"=>"ETABLISSEMENT",
            "ROLE_ENTREPRISE"=>"ENTREPRISE",
            "ROLE_DIPLOME"=>"DIPLOME",
            ];

        // Suppress obsoletes roles in Database
        // foreach ($dbRoles as $dbRole) {
        //     echo "---> " . $dbRole->getRole() ."\n";
        //     $roleExist = false;
        //     foreach ($allRoles as $key=>$value) {
        //         echo "      ( " . $dbRole->getRole() .")\n";
        //         if($dbRole->getRole() == $key) {
        //             $roleExist = true;
        //         }
        //     }
        //
        //     if(!$roleExist) {
        //         // find users with this role and suppress this role
        //         $dbUsers = $this->userRepository->getByRole($dbRole);
        //         foreach ($dbUsers as $user) {
        //             $user->removeRole($dbRole);
        //         }
        //         $this->entityManager->remove($dbRole);
        //         $counterRemove++;
        //     }
        // }

        //Add new roles in Database
        foreach ($allRoles as $key=>$value) {
            $roleExist = null;
            for ($i=0; $i<count($dbRoles); $i++) {
                if($dbRoles[$i]->getRole() == $key) {
                    $roleExist = $dbRoles[$i];

                    //update Pseudo for existing roles
                    $roleExist->setPseudo($value);
                    $counterUpdated++;

                    $i = count($dbRoles);
                }
            }
            if(!$roleExist) {
                echo $key . " added to Database\n";
                $newRole = new Role();
                $newRole->setRole($key);
                $newRole->setPseudo($value);
                $this->entityManager->persist($newRole);
                $counterAdded++;
            }
        }

        $this->entityManager->flush();
        $io->info('Delete ' . $counterRemove .' role(s) from Database' ."\n        Added " . $counterAdded . " role(s) to Database" . "\n        Updated "  . $counterUpdated . " role(s) to Database");
        return Command::SUCCESS;
    }
}
