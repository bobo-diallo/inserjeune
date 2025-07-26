<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250719170538 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Correct des etablissements sans utilisateur';
    }

    public function up(Schema $schema): void
    {
        // UNIQUE temporary password for this migration
        $tempPassword = 'TEMP_MIGRATION_' . date('YmdHis') . '_' . uniqid();
        $finalPassword = '$2y$13$Kvhb8l5ViHYWcS74ovLLGO4VohK3C.C20nM5KOTC4CmvBfBqGpyZe';

        // Table to save new users
        $this->addSql('CREATE TEMPORARY TABLE temp_new_users (id INT PRIMARY KEY, phone_standard VARCHAR(255))');

        // 1. Création des utilisateurs avec mot de passe temporaire UNIQUE
        $this->addSql("
            INSERT INTO user (
                country_id,
                region_id,
                residence_country_id,
                residence_region_id,
                username,
                roles,
                password,
                phone,
                enabled,
                email,
                email_canonical,
                username_canonical,
                diaspora
            )
            SELECT 
                s.id_country AS country_id,
                s.id_region AS region_id,
                s.id_country AS residence_country_id,
                s.id_region AS residence_region_id,
                s.phone_standard AS username,
                'a:0:{}' AS roles,
                ? AS password,
                s.phone_standard AS phone,
                1 AS enabled,
                s.email AS email,
                s.email AS email_canonical,
                s.phone_standard AS username_canonical,
                0 AS diaspora
            FROM school s
            WHERE s.phone_standard NOT IN (
                SELECT DISTINCT u.username FROM user u WHERE u.username IS NOT NULL
            )
            AND s.email NOT IN (
                SELECT DISTINCT u.email FROM user u WHERE u.email IS NOT NULL
            )
            AND s.phone_standard IS NOT NULL
            AND s.phone_standard != ''
            AND s.email IS NOT NULL
            AND s.email != ''
        ", [$tempPassword]);

        // 2. Retrieving IDs of newly created users (with temporary password)
        $this->addSql("
            INSERT INTO temp_new_users (id, phone_standard)
            SELECT u.id, u.phone
            FROM user u
            WHERE u.password = ?
        ", [$tempPassword]);

        // 3. Update with real password
        $this->addSql("
            UPDATE user u
            JOIN temp_new_users t ON t.id = u.id
            SET u.password = ?
        ", [$finalPassword]);

        // 4. Assigning the ROLE_ESTABLISSEMENT role to new users
        $this->addSql("
            INSERT INTO user_role (user_id, role_id)
            SELECT 
                t.id AS user_id,
                (SELECT id FROM role WHERE role = 'ROLE_ETABLISSEMENT' LIMIT 1) AS role_id
            FROM temp_new_users t
            WHERE NOT EXISTS (
                SELECT 1 FROM user_role ur 
                WHERE ur.user_id = t.id 
                AND ur.role_id = (SELECT id FROM role WHERE role = 'ROLE_ETABLISSEMENT' LIMIT 1)
            )
        ");

        // 5. Updating schools with corresponding user IDs
        $this->addSql("
            UPDATE school s
            JOIN temp_new_users t ON t.phone_standard = s.phone_standard
            SET s.user_id = t.id
        ");

        // Cleaning the temporary table
        $this->addSql('DROP TEMPORARY TABLE temp_new_users');
    }

    public function down(Schema $schema): void
    {
        $finalPassword = '$2y$13$Kvhb8l5ViHYWcS74ovLLGO4VohK3C.C20nM5KOTC4CmvBfBqGpyZe';

        // Création de la table temporaire pour le rollback
        $this->addSql('CREATE TEMPORARY TABLE temp_users_to_delete (id INT PRIMARY KEY)');

        $this->addSql("
            INSERT INTO temp_users_to_delete (id)
            SELECT DISTINCT u.id 
            FROM user u
            JOIN school s ON u.phone = s.phone_standard
            WHERE u.password = ?
            AND EXISTS (
                SELECT 1 FROM user_role ur 
                JOIN role r ON ur.role_id = r.id 
                WHERE ur.user_id = u.id 
                AND r.role = 'ROLE_ETABLISSEMENT'
            )
            AND u.username = u.phone
            AND u.email_canonical = u.email
            AND s.user_id = u.id
        ", [$finalPassword]);

        $this->addSql("
            UPDATE school s
            SET s.user_id = NULL 
            WHERE s.user_id IN (SELECT id FROM temp_users_to_delete)
        ");

        $this->addSql("
            DELETE ur FROM user_role ur
            JOIN temp_users_to_delete t ON ur.user_id = t.id
        ");

        $this->addSql("
            DELETE u FROM user u
            JOIN temp_users_to_delete t ON u.id = t.id
        ");

        $this->addSql('DROP TEMPORARY TABLE temp_users_to_delete');
    }
}