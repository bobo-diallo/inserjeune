<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221002211810 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE apprentice ADD location_mode TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE candidate_research ADD unlocked TINYINT(1) DEFAULT NULL, ADD location_mode TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE company ADD id_image INT DEFAULT NULL, ADD location_mode TINYINT(1) DEFAULT NULL, ADD unlocked TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094F2BB8456F FOREIGN KEY (id_image) REFERENCES image (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4FBF094F2BB8456F ON company (id_image)');
        $this->addSql('ALTER TABLE company_creator ADD unlocked TINYINT(1) DEFAULT NULL, ADD location_mode TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE contact_company ADD location_mode TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE job_offer ADD updated_date DATETIME DEFAULT NULL, ADD candidate_profile LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE person_degree ADD unlocked TINYINT(1) DEFAULT NULL, ADD location_mode TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE school ADD location_mode TINYINT(1) DEFAULT NULL, DROP location_fixed');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE apprentice DROP location_mode');
        $this->addSql('ALTER TABLE candidate_research DROP unlocked, DROP location_mode');
        $this->addSql('ALTER TABLE company DROP FOREIGN KEY FK_4FBF094F2BB8456F');
        $this->addSql('DROP INDEX UNIQ_4FBF094F2BB8456F ON company');
        $this->addSql('ALTER TABLE company DROP id_image, DROP location_mode, DROP unlocked');
        $this->addSql('ALTER TABLE company_creator DROP unlocked, DROP location_mode');
        $this->addSql('ALTER TABLE contact_company DROP location_mode');
        $this->addSql('ALTER TABLE job_offer DROP updated_date, DROP candidate_profile');
        $this->addSql('ALTER TABLE person_degree DROP unlocked, DROP location_mode');
        $this->addSql('ALTER TABLE school ADD location_fixed TINYINT(1) NOT NULL, DROP location_mode');
    }
}
