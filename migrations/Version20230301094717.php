<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Role;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230301094717 extends AbstractMigration {
	public function getDescription(): string {
		return '';
	}

	public function up(Schema $schema): void {

		// Delete ON DELETE CASCADE
		$this->addSql("ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A3D60322AC");
		$this->addSql("
			ALTER TABLE user_role
		    ADD CONSTRAINT FK_2DE8C6A3D60322AC
		        FOREIGN KEY (role_id) REFERENCES role (id)
		            ON UPDATE CASCADE");

		// Add ROLE_DIPLOME if not exist and get ID
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
		} else {
			$roleId = $roleId;
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
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A3D60322AC');
		$this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3D60322AC FOREIGN KEY (role_id) REFERENCES role (id)');

	}
}
