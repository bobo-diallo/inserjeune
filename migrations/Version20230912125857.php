<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230912125857 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE IF NOT EXISTS job_applied (id INT AUTO_INCREMENT NOT NULL, id_offer INT DEFAULT NULL, id_user INT DEFAULT NULL, applied_date DATETIME DEFAULT NULL, resumed_applied VARCHAR(766) DEFAULT NULL, is_sended TINYINT(1) NOT NULL, INDEX IDX_BC11400DC753C60E (id_offer), INDEX IDX_BC11400D6B3CA4B (id_user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE job_applied ADD CONSTRAINT FK_BC11400DC753C60E FOREIGN KEY (id_offer) REFERENCES job_offer (id)');
        $this->addSql('ALTER TABLE job_applied ADD CONSTRAINT FK_BC11400D6B3CA4B FOREIGN KEY (id_user) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE job_applied DROP FOREIGN KEY FK_BC11400DC753C60E');
        $this->addSql('ALTER TABLE job_applied DROP FOREIGN KEY FK_BC11400D6B3CA4B');
        $this->addSql('DROP TABLE job_applied');
    }
}
