<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Tools\Utils;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250209185625 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
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
    }
}
