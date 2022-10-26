<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221026111006 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE job_offer DROP FOREIGN KEY FK_288A3A4E2BB8456F');
        $this->addSql('DROP INDEX IDX_288A3A4E2BB8456F ON job_offer');
        $this->addSql('ALTER TABLE job_offer DROP id_image');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE job_offer ADD id_image INT DEFAULT NULL');
        $this->addSql('ALTER TABLE job_offer ADD CONSTRAINT FK_288A3A4E2BB8456F FOREIGN KEY (id_image) REFERENCES image (id)');
        $this->addSql('CREATE INDEX IDX_288A3A4E2BB8456F ON job_offer (id_image)');
    }
}
