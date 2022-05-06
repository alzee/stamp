<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220506135104 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fingerprint ADD device_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fingerprint ADD CONSTRAINT FK_FC0B754A94A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id)');
        $this->addSql('CREATE INDEX IDX_FC0B754A94A4C7D4 ON fingerprint (device_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fingerprint DROP FOREIGN KEY FK_FC0B754A94A4C7D4');
        $this->addSql('DROP INDEX IDX_FC0B754A94A4C7D4 ON fingerprint');
        $this->addSql('ALTER TABLE fingerprint DROP device_id');
    }
}
