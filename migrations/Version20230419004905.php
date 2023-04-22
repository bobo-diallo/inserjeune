<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230419004905 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE person_degree DROP FOREIGN KEY FK_3B065DD5C32A47EE');
        $this->addSql('ALTER TABLE person_degree ADD CONSTRAINT FK_3B065DD5C32A47EE FOREIGN KEY (school_id) REFERENCES school (id) ON DELETE SET NULL');

    }

    public function down(Schema $schema): void
    {
	    $this->addSql('ALTER TABLE person_degree DROP FOREIGN KEY FK_3B065DD5C32A47EE');
	    $this->addSql('ALTER TABLE person_degree ADD CONSTRAINT FK_3B065DD5C32A47EE FOREIGN KEY (school_id) REFERENCES school (id)');
    }
}
