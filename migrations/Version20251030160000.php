<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251030160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add token earning feature tables and settings';
    }

    public function up(Schema $schema): void
    {
        // Create token_earning_log table
        $this->addSql('CREATE TABLE token_earning_log (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            method VARCHAR(50) NOT NULL,
            amount NUMERIC(10, 2) NOT NULL,
            ip_address VARCHAR(255) NOT NULL,
            details LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL,
            INDEX IDX_TOKEN_EARNING_USER (user_id),
            INDEX IDX_TOKEN_EARNING_METHOD (method),
            INDEX IDX_TOKEN_EARNING_CREATED (created_at),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        $this->addSql('ALTER TABLE token_earning_log ADD CONSTRAINT FK_TOKEN_EARNING_USER_ID FOREIGN KEY (user_id) REFERENCES user (id)');

        // Add settings for token earning feature
        $this->addSql("INSERT INTO setting (name, value) VALUES ('token_earning_enabled', '0')");
        $this->addSql("INSERT INTO setting (name, value) VALUES ('token_earning_ad_amount', '1.00')");
        $this->addSql("INSERT INTO setting (name, value) VALUES ('token_earning_ad_cooldown_minutes', '60')");
        $this->addSql("INSERT INTO setting (name, value) VALUES ('token_earning_discord_amount', '5.00')");
        $this->addSql("INSERT INTO setting (name, value) VALUES ('token_earning_discord_server_id', '')");
        $this->addSql("INSERT INTO setting (name, value) VALUES ('token_earning_task_amount', '2.00')");
        $this->addSql("INSERT INTO setting (name, value) VALUES ('discord_client_id', '')");
        $this->addSql("INSERT INTO setting (name, value) VALUES ('discord_client_secret', '')");
        $this->addSql("INSERT INTO setting (name, value) VALUES ('discord_bot_token', '')");
    }

    public function down(Schema $schema): void
    {
        // Drop foreign key and table
        $this->addSql('ALTER TABLE token_earning_log DROP FOREIGN KEY FK_TOKEN_EARNING_USER_ID');
        $this->addSql('DROP TABLE token_earning_log');

        // Remove settings
        $this->addSql("DELETE FROM setting WHERE name = 'token_earning_enabled'");
        $this->addSql("DELETE FROM setting WHERE name = 'token_earning_ad_amount'");
        $this->addSql("DELETE FROM setting WHERE name = 'token_earning_ad_cooldown_minutes'");
        $this->addSql("DELETE FROM setting WHERE name = 'token_earning_discord_amount'");
        $this->addSql("DELETE FROM setting WHERE name = 'token_earning_discord_server_id'");
        $this->addSql("DELETE FROM setting WHERE name = 'token_earning_task_amount'");
        $this->addSql("DELETE FROM setting WHERE name = 'discord_client_id'");
        $this->addSql("DELETE FROM setting WHERE name = 'discord_client_secret'");
        $this->addSql("DELETE FROM setting WHERE name = 'discord_bot_token'");
    }
}
