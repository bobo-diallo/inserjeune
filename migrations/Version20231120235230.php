<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231120235230 extends AbstractMigration
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
        $this->addSql('DROP INDEX FK_BC11400D6B3CA4B ON job_applied');
        $this->addSql('DROP INDEX FK_BC11400DC753C60E ON job_applied');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649A1BE284D');
        $this->addSql('DROP INDEX IDX_8D93D649A1BE284D ON user');
        $this->addSql('ALTER TABLE user CHANGE school_id_id principal_school INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE job_applied ADD CONSTRAINT FK_BC11400D6B3CA4B FOREIGN KEY (id_user) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE job_applied ADD CONSTRAINT FK_BC11400DC753C60E FOREIGN KEY (id_offer) REFERENCES job_offer (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX FK_BC11400D6B3CA4B ON job_applied (id_user)');
        $this->addSql('CREATE INDEX FK_BC11400DC753C60E ON job_applied (id_offer)');
        $this->addSql('ALTER TABLE user CHANGE principal_school school_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649A1BE284D FOREIGN KEY (school_id_id) REFERENCES school (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_8D93D649A1BE284D ON user (school_id_id)');
    }
}
