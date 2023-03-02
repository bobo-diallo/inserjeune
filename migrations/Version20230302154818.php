<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Role;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230302154818 extends AbstractMigration {
	public function getDescription(): string {
		return '';
	}

	public function up(Schema $schema): void {
		$this->addRoleDiplome();
		$this->addRoleEntreprise();
		$this->addRoleEtablissement();
	}

	/**
	 * @throws Exception
	 */
	private function addRoleEntreprise(): void {
		$roleId = $this->connection->executeQuery(
			'SELECT r.id as id
			    FROM role r
			    WHERE r.role = ?',
			[Role::ROLE_ENTREPRISE])
			->fetchOne();

		if (!$roleId) {
			$addRole = $this->connection->prepare(
				'INSERT INTO role (role) VALUES (:roleName)'
			);
			$addRole->bindValue('roleName', Role::ROLE_ENTREPRISE);
			$addRole->executeStatement();

			$roleId = $this->connection->lastInsertId();
		}

		// Add ROLE_DIPLOME to person_degree not having this role
		$user_ids = $this->connection->executeQuery(
			'SELECT u.id as id
				FROM company p, user u
				WHERE p.user_id = u.id'
		)->fetchAllAssociative();

		foreach ($user_ids as $user_id) {
			$this->addSql('INSERT INTO user_role (user_id, role_id)
		            SELECT ?, ?
		            WHERE NOT EXISTS (
		                SELECT 1 FROM user_role ur
		                WHERE ur.user_id = ? AND ur.role_id = ?
		            )', [$user_id['id'], $roleId, $user_id['id'], $roleId]);

		}
	}

	/**
	 * @throws Exception
	 */
	private function addRoleEtablissement(): void {
		/// Add ROLE_ETABLISSEMENT if not exist and get ID
		$roleId = $this->connection->executeQuery(
			'SELECT r.id as id
			    FROM role r
			    WHERE r.role = ?',
			[Role::ROLE_ETABLISSEMENT])
			->fetchOne();

		if (!$roleId) {
			$addRole = $this->connection->prepare(
				'INSERT INTO role (role) VALUES (:roleName)'
			);
			$addRole->bindValue('roleName', Role::ROLE_ETABLISSEMENT);
			$addRole->executeStatement();

			$roleId = $this->connection->lastInsertId();
		}

		// Add ROLE_ETABLISSEMENT to person_degree not having this role
		$user_ids = $this->connection->executeQuery(
			'SELECT u.id as id
				FROM school p, user u
				WHERE p.user_id = u.id'
		)->fetchAllAssociative();

		foreach ($user_ids as $user_id) {
			$this->addSql('INSERT INTO user_role (user_id, role_id)
		            SELECT ?, ?
		            WHERE NOT EXISTS (
		                SELECT 1 FROM user_role ur
		                WHERE ur.user_id = ? AND ur.role_id = ?
		            )', [$user_id['id'], $roleId, $user_id['id'], $roleId]);

		}
	}

	private function addRoleDiplome(): void {
		/// Add ROLE_DIPLOME if not exist and get ID
		$roleId = $this->connection->executeQuery(
			'SELECT r.id as id
			    FROM role r
			    WHERE r.role = ?',
			[Role::ROLE_DIPLOME])
			->fetchOne();

		if (!$roleId) {
			$addRole = $this->connection->prepare(
				'INSERT INTO role (role) VALUES (:roleName)'
			);
			$addRole->bindValue('roleName', Role::ROLE_DIPLOME);
			$addRole->executeStatement();

			$roleId = $this->connection->lastInsertId();
		}

		// Add ROLE_DIPLOME to person_degree not having this role
		$user_ids = $this->connection->executeQuery(
			'SELECT u.id as id
				FROM person_degree p, user u
				WHERE p.user_id = u.id'
		)->fetchAllAssociative();

		foreach ($user_ids as $user_id) {
			$this->addSql('INSERT INTO user_role (user_id, role_id)
		            SELECT ?, ?
		            WHERE NOT EXISTS (
		                SELECT 1 FROM user_role ur
		                WHERE ur.user_id = ? AND ur.role_id = ?
		            )', [$user_id['id'], $roleId, $user_id['id'], $roleId]);

		}
	}

	public function down(Schema $schema): void {
	}
}
