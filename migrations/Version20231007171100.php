<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231007171100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user CHANGE school_id school_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649A1BE284D FOREIGN KEY (school_id_id) REFERENCES school (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649A1BE284D ON user (school_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649A1BE284D');
        $this->addSql('DROP INDEX IDX_8D93D649A1BE284D ON user');
        $this->addSql('ALTER TABLE user CHANGE school_id_id school_id INT DEFAULT NULL');
    }
}
