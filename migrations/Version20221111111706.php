<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221111111706 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Correct data after deployment';
    }

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$duplicateNames = $this->connection->executeQuery(
			'SELECT COUNT(s.id) as nb, s.registration as school_registration
				FROM school s
				group by school_registration
				having nb > 1'
		)->fetchAllAssociative();

		foreach ($duplicateNames as $item) {
			$registration = $item['school_registration'];
			$ids = $this->connection->executeQuery('SELECT s2.id FROM school s2 WHERE s2.registration = ?', [$registration])
				->fetchAllAssociative();

			foreach ($ids as $id) {
				$this->addSql(
					'UPDATE school s1 SET s1.registration = ? WHERE s1.id = ?',
					[
						$registration . '-d-' . rand(0, 100),
						$id['id']
					]
				);
			}
		}
	}

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
