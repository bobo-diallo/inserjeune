<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230920205132 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE job_applied DROP FOREIGN KEY FK_BC11400D6B3CA4B');
        $this->addSql('ALTER TABLE job_applied DROP FOREIGN KEY FK_BC11400DC753C60E');
        $this->addSql('DROP INDEX IDX_BC11400D6B3CA4B ON job_applied');
        $this->addSql('DROP INDEX IDX_BC11400DC753C60E ON job_applied');
        $this->addSql('ALTER TABLE job_applied ADD id_city INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE job_applied DROP id_city');
        $this->addSql('ALTER TABLE job_applied ADD CONSTRAINT FK_BC11400D6B3CA4B FOREIGN KEY (id_user) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE job_applied ADD CONSTRAINT FK_BC11400DC753C60E FOREIGN KEY (id_offer) REFERENCES job_offer (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_BC11400D6B3CA4B ON job_applied (id_user)');
        $this->addSql('CREATE INDEX IDX_BC11400DC753C60E ON job_applied (id_offer)');
    }
}
