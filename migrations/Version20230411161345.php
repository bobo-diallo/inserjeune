<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230411161345 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidate_research ADD diaspora TINYINT(1) NOT NULL, ADD address_diaspora VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE company_creator ADD diaspora TINYINT(1) NOT NULL, ADD address_diaspora VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE geo_location ADD show_other_person_degrees TINYINT(1) NOT NULL, CHANGE show_person_degrees show_search_person_degrees TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE person_degree ADD residence_country_id INT DEFAULT NULL, ADD diaspora TINYINT(1) NOT NULL, ADD address_diaspora VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE person_degree ADD CONSTRAINT FK_3B065DD547C609EB FOREIGN KEY (residence_country_id) REFERENCES country (id)');
        $this->addSql('CREATE INDEX IDX_3B065DD547C609EB ON person_degree (residence_country_id)');
        $this->addSql('ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A3A76ED395');
        $this->addSql('ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A3D60322AC');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3D60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidate_research DROP diaspora, DROP address_diaspora');
        $this->addSql('ALTER TABLE company_creator DROP diaspora, DROP address_diaspora');
        $this->addSql('ALTER TABLE geo_location ADD show_person_degrees TINYINT(1) NOT NULL, DROP show_search_person_degrees, DROP show_other_person_degrees');
        $this->addSql('ALTER TABLE person_degree DROP FOREIGN KEY FK_3B065DD547C609EB');
        $this->addSql('DROP INDEX IDX_3B065DD547C609EB ON person_degree');
        $this->addSql('ALTER TABLE person_degree DROP residence_country_id, DROP diaspora, DROP address_diaspora');
        $this->addSql('ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A3A76ED395');
        $this->addSql('ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A3D60322AC');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON UPDATE CASCADE ON DELETE CASCADE');
    }
}
