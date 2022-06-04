<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
	private UserPasswordHasherInterface $hasher;

	public function __construct(UserPasswordHasherInterface $hasher)
	{
		$this->hasher = $hasher;
	}

	public function load(ObjectManager $manager): void
	{
		$user = new User();
		$user->setUsername('bobo');
		$user->setPassword($this->hasher->hashPassword($user, 'bdiallo'));

		$manager->persist($user);
		$manager->flush();
	}
}
