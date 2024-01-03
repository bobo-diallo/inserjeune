<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230926135325 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE prefecture (id INT AUTO_INCREMENT NOT NULL, id_region INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_ABE6511A2955449B (id_region), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE prefecture ADD CONSTRAINT FK_ABE6511A2955449B FOREIGN KEY (id_region) REFERENCES region (id)');
        $this->addSql('ALTER TABLE city ADD prefecture_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE city ADD CONSTRAINT FK_2D5B02349D39C865 FOREIGN KEY (prefecture_id) REFERENCES prefecture (id)');
        $this->addSql('CREATE INDEX IDX_2D5B02349D39C865 ON city (prefecture_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE city DROP FOREIGN KEY FK_2D5B02349D39C865');
        $this->addSql('ALTER TABLE prefecture DROP FOREIGN KEY FK_ABE6511A2955449B');
        $this->addSql('DROP TABLE prefecture');
        $this->addSql('DROP INDEX IDX_2D5B02349D39C865 ON city');
        $this->addSql('ALTER TABLE city DROP prefecture_id');
    }
}
