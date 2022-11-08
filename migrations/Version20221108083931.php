<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221108083931 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE job_offer ADD id_school INT DEFAULT NULL');
        $this->addSql('ALTER TABLE job_offer ADD CONSTRAINT FK_288A3A4EDFA96F56 FOREIGN KEY (id_school) REFERENCES school (id)');
        $this->addSql('CREATE INDEX IDX_288A3A4EDFA96F56 ON job_offer (id_school)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE job_offer DROP FOREIGN KEY FK_288A3A4EDFA96F56');
        $this->addSql('DROP INDEX IDX_288A3A4EDFA96F56 ON job_offer');
        $this->addSql('ALTER TABLE job_offer DROP id_school');
    }
}
