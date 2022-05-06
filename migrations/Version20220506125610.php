<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220506125610 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE wecom (id INT AUTO_INCREMENT NOT NULL, org_id INT DEFAULT NULL, corp_id VARCHAR(255) NOT NULL, contacts_secret VARCHAR(255) NOT NULL, approval_secret VARCHAR(255) NOT NULL, callback_token VARCHAR(255) NOT NULL, callback_aeskey VARCHAR(255) NOT NULL, stamping_template_id VARCHAR(255) NOT NULL, adding_fpr_template_id VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_1ADFD307F4837C1B (org_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE wecom ADD CONSTRAINT FK_1ADFD307F4837C1B FOREIGN KEY (org_id) REFERENCES organization (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE wecom');
    }
}
