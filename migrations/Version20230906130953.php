<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230906130953 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_admin_regions (user_id INT NOT NULL, region_id INT NOT NULL, INDEX IDX_88D5562AA76ED395 (user_id), INDEX IDX_88D5562A98260155 (region_id), PRIMARY KEY(user_id, region_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_admin_cities (user_id INT NOT NULL, city_id INT NOT NULL, INDEX IDX_459FC90A76ED395 (user_id), INDEX IDX_459FC908BAC62AF (city_id), PRIMARY KEY(user_id, city_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_admin_regions ADD CONSTRAINT FK_88D5562AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_admin_regions ADD CONSTRAINT FK_88D5562A98260155 FOREIGN KEY (region_id) REFERENCES region (id)');
        $this->addSql('ALTER TABLE user_admin_cities ADD CONSTRAINT FK_459FC90A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_admin_cities ADD CONSTRAINT FK_459FC908BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('DROP INDEX country_iso_unique ON country');
        $this->addSql('ALTER TABLE country CHANGE iso_code iso_code VARCHAR(3) DEFAULT NULL');
        $this->addSql('ALTER TABLE person_degree DROP FOREIGN KEY FK_3B065DD5C32A47EE');
        $this->addSql('ALTER TABLE person_degree ADD residence_region_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE person_degree ADD CONSTRAINT FK_3B065DD57A018A27 FOREIGN KEY (residence_region_id) REFERENCES region (id)');
        $this->addSql('ALTER TABLE person_degree ADD CONSTRAINT FK_3B065DD5C32A47EE FOREIGN KEY (school_id) REFERENCES school (id)');
        $this->addSql('CREATE INDEX IDX_3B065DD57A018A27 ON person_degree (residence_region_id)');
        $this->addSql('ALTER TABLE region ADD id_currency INT DEFAULT NULL, ADD valid TINYINT(1) NOT NULL, ADD iso_code VARCHAR(3) DEFAULT NULL, ADD phone_code INT NOT NULL, ADD phone_digit INT NOT NULL');
        $this->addSql('ALTER TABLE region ADD CONSTRAINT FK_F62F176398D64AA FOREIGN KEY (id_currency) REFERENCES currency (id)');
        $this->addSql('CREATE INDEX IDX_F62F176398D64AA ON region (id_currency)');
        $this->addSql('ALTER TABLE role ADD pseudo VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD region_id INT DEFAULT NULL, ADD residence_region_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64998260155 FOREIGN KEY (region_id) REFERENCES region (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6497A018A27 FOREIGN KEY (residence_region_id) REFERENCES region (id)');
        $this->addSql('CREATE INDEX IDX_8D93D64998260155 ON user (region_id)');
        $this->addSql('CREATE INDEX IDX_8D93D6497A018A27 ON user (residence_region_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_admin_regions DROP FOREIGN KEY FK_88D5562AA76ED395');
        $this->addSql('ALTER TABLE user_admin_regions DROP FOREIGN KEY FK_88D5562A98260155');
        $this->addSql('ALTER TABLE user_admin_cities DROP FOREIGN KEY FK_459FC90A76ED395');
        $this->addSql('ALTER TABLE user_admin_cities DROP FOREIGN KEY FK_459FC908BAC62AF');
        $this->addSql('DROP TABLE user_admin_regions');
        $this->addSql('DROP TABLE user_admin_cities');
        $this->addSql('ALTER TABLE country CHANGE iso_code iso_code VARCHAR(3) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX country_iso_unique ON country (iso_code)');
        $this->addSql('ALTER TABLE person_degree DROP FOREIGN KEY FK_3B065DD57A018A27');
        $this->addSql('ALTER TABLE person_degree DROP FOREIGN KEY FK_3B065DD5C32A47EE');
        $this->addSql('DROP INDEX IDX_3B065DD57A018A27 ON person_degree');
        $this->addSql('ALTER TABLE person_degree DROP residence_region_id');
        $this->addSql('ALTER TABLE person_degree ADD CONSTRAINT FK_3B065DD5C32A47EE FOREIGN KEY (school_id) REFERENCES school (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('ALTER TABLE region DROP FOREIGN KEY FK_F62F176398D64AA');
        $this->addSql('DROP INDEX IDX_F62F176398D64AA ON region');
        $this->addSql('ALTER TABLE region DROP id_currency, DROP valid, DROP iso_code, DROP phone_code, DROP phone_digit');
        $this->addSql('ALTER TABLE role DROP pseudo');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64998260155');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6497A018A27');
        $this->addSql('DROP INDEX IDX_8D93D64998260155 ON user');
        $this->addSql('DROP INDEX IDX_8D93D6497A018A27 ON user');
        $this->addSql('ALTER TABLE user DROP region_id, DROP residence_region_id');
    }
}
