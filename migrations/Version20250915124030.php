<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250915124030 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE school_degrees1 DROP FOREIGN KEY FK_97808E96C32A47EE');
        $this->addSql('ALTER TABLE school_degrees1 ADD CONSTRAINT FK_97808E96C32A47EE FOREIGN KEY (school_id) REFERENCES school (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE school_degrees1 DROP FOREIGN KEY FK_97808E96C32A47EE');
        $this->addSql('ALTER TABLE school_degrees1 ADD CONSTRAINT FK_97808E96C32A47EE FOREIGN KEY (school_id) REFERENCES school (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
