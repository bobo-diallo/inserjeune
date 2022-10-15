<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221015074942 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidate_research ADD temporary_passwd VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE company ADD temporary_passwd VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE company_creator ADD temporary_passwd VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE person_degree ADD temporary_passwd VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidate_research DROP temporary_passwd');
        $this->addSql('ALTER TABLE company DROP temporary_passwd');
        $this->addSql('ALTER TABLE company_creator DROP temporary_passwd');
        $this->addSql('ALTER TABLE person_degree DROP temporary_passwd');
    }
}
