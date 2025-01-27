<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Tools\Utils;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250126191827 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

	public function up(Schema $schema): void
	{
        $this->addSql('ALTER TABLE country ADD sanitized_name VARCHAR(255) NULL');

        $countries = $this->connection->fetchAllAssociative('SELECT id, name FROM country');

        foreach ($countries as $country) {
	        $this->addSql('UPDATE country SET sanitized_name = ? WHERE id = ?', [
				Utils::sanitizeName($country['name']),
		        $country['id']]
	        );
        }
	}

	public function down(Schema $schema): void
	{
		$this->addSql('ALTER TABLE country DROP sanitized_name');
	}
}
