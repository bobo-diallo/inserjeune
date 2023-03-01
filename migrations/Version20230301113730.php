<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230301113730 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
	    $this->addSql("alter table user_role drop foreign key FK_2DE8C6A3D60322AC");
	    $this->addSql("
			alter table user_role
			    add constraint FK_2DE8C6A3D60322AC
			        foreign key (role_id) references role (id)
			            on update cascade on delete cascade
		");

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
