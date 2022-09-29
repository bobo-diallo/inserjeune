<?php

namespace App\DataFixtures;

use App\Entity\Role;
use Doctrine\Persistence\ObjectManager;

final class RoleFixtures extends AbstractFixtures
{
	public const ROLE_USER = 'ROLE_USER';
	public const ROLE_ADMIN = 'ROLE_ADMIN';
	public const ROLE_DIPLOME = 'ROLE_DIPLOME';
	public const ROLE_ENQUETEUR = 'ROLE_ENQUETEUR';
	public const ROLE_ENTREPRISE = 'ROLE_ENTREPRISE';
	public const ROLE_ETABLISSEMENT = 'ROLE_ETABLISSEMENT';
	public const ROLE_LEGISLATEUR = 'ROLE_LEGISLATEUR';

	public function load(ObjectManager $manager): void
	{
		$roles = [
			RoleFixtures::ROLE_ADMIN,
			RoleFixtures::ROLE_USER,
			RoleFixtures::ROLE_DIPLOME,
			RoleFixtures::ROLE_ENQUETEUR,
			RoleFixtures::ROLE_ENTREPRISE,
			RoleFixtures::ROLE_ETABLISSEMENT,
			RoleFixtures::ROLE_LEGISLATEUR,
		];

		foreach ($roles as $newRole) {
			$role = new Role();
			$role->setRole($newRole);
			$manager->persist($role);

			$manager->flush();

			$this->setRoleReference($role);
		}
	}
}
