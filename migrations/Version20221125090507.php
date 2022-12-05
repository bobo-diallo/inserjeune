<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221125090507 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity CHANGE description description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE contract CHANGE description description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE degree CHANGE description description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE info_creator CHANGE description description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE job_offer CHANGE description description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE legal_status CHANGE description description LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE profession CHANGE description description LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE sector_area CHANGE description description LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE contract CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE degree CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE info_creator CHANGE description description VARCHAR(510) NOT NULL');
        $this->addSql('ALTER TABLE job_offer CHANGE description description TEXT NOT NULL');
        $this->addSql('ALTER TABLE legal_status CHANGE description description VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE profession CHANGE description description VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE sector_area CHANGE description description VARCHAR(255) NOT NULL');
    }
}
