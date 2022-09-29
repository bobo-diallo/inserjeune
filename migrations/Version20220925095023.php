<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220925095023 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE activity_sector_area');
        $this->addSql('ALTER TABLE activity ADD id_sectorArea INT DEFAULT NULL');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT FK_AC74095A279B02DB FOREIGN KEY (id_sectorArea) REFERENCES sector_area (id)');
        $this->addSql('CREATE INDEX IDX_AC74095A279B02DB ON activity (id_sectorArea)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activity_sector_area (sector_area_id INT NOT NULL, id_sectorArea INT NOT NULL, INDEX IDX_F7428851782D3BB (sector_area_id), INDEX IDX_F742885279B02DB (id_sectorArea), PRIMARY KEY(id_sectorArea, sector_area_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE activity_sector_area ADD CONSTRAINT FK_F7428851782D3BB FOREIGN KEY (sector_area_id) REFERENCES sector_area (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE activity_sector_area ADD CONSTRAINT FK_F742885279B02DB FOREIGN KEY (id_sectorArea) REFERENCES activity (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE activity DROP FOREIGN KEY FK_AC74095A279B02DB');
        $this->addSql('DROP INDEX IDX_AC74095A279B02DB ON activity');
        $this->addSql('ALTER TABLE activity DROP id_sectorArea');
    }
}
