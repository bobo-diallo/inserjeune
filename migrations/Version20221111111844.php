<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221111111844 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
	    $this->addSql('ALTER TABLE job_offer DROP id_image');
	    $this->addSql('ALTER TABLE school DROP location_fixed');
	    $this->addSql('CREATE UNIQUE INDEX UNIQ_F99EDABB62A8A7A7 ON school (registration)');
    }

    public function down(Schema $schema): void
    {
	    $this->addSql('ALTER TABLE job_offer ADD id_image INT DEFAULT NULL');
	    $this->addSql('ALTER TABLE school ADD location_fixed INT DEFAULT NULL');
	    $this->addSql('DROP INDEX UNIQ_F99EDABB62A8A7A7 ON school');

    }
}
