<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RoleFixtures extends Fixture
{
	public const ROLE_USER = 'ROLE_USER';
	public const ROLE_ADMIN = 'ROLE_ADMIN';

	public function load(ObjectManager $manager): void
	{
		$roles = [
			RoleFixtures::ROLE_ADMIN,
			RoleFixtures::ROLE_USER,
		];

		foreach ($roles as $newRole) {
			$role = new Role();
			$role->setRole($newRole);
			$manager->persist($role);

			$manager->flush();
		}
	}
}
