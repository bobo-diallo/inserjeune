<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221005014558 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company DROP INDEX IDX_4FBF094FA76ED395, ADD UNIQUE INDEX UNIQ_4FBF094FA76ED395 (user_id)');
        $this->addSql('ALTER TABLE school DROP INDEX IDX_F99EDABBA76ED395, ADD UNIQUE INDEX UNIQ_F99EDABBA76ED395 (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company DROP INDEX UNIQ_4FBF094FA76ED395, ADD INDEX IDX_4FBF094FA76ED395 (user_id)');
        $this->addSql('ALTER TABLE school DROP INDEX UNIQ_F99EDABBA76ED395, ADD INDEX IDX_F99EDABBA76ED395 (user_id)');
    }
}
