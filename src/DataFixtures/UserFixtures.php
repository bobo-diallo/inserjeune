<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Repository\RoleRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    private RoleRepository $roleRepository;

    public function __construct(
		UserPasswordHasherInterface $passwordHasher,
		RoleRepository $roleRepository
    ) {
        $this->passwordHasher = $passwordHasher;
        $this->roleRepository = $roleRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $users = [
            [
                'username' => 'bobo',
                'email' => 'bobo@gmail.com',
                'password' => 'bdiallo',
                'phone' => '771709810',
                'role' => $this->roleRepository->findOneBy(['role' => RoleFixtures::ROLE_USER]),
            ],
            [
                'username' => 'admin',
                'email' => 'admin@gmail.com',
                'password' => 'admin',
                'phone' => '767707660',
                'role' => $this->roleRepository->findOneBy(['role' => RoleFixtures::ROLE_ADMIN]),
            ],
        ];

        foreach ($users as $newUser) {
            $user = new User();

            $user->setUsername($newUser['username']);
            $user->setEmail($newUser['email']);
            $user->setPassword($this->passwordHasher->hashPassword($user, $newUser['password']));
            $user->addProfil($newUser['role']);
            $user->setPhone($newUser['phone']);

            $manager->persist($user);
            $manager->flush();
        }
    }
}
