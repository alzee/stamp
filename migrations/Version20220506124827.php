<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220506124827 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE device DROP FOREIGN KEY FK_92FB68E81096D35');
        $this->addSql('DROP INDEX IDX_92FB68E81096D35 ON device');
        $this->addSql('ALTER TABLE device CHANGE org_id_id org_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE device ADD CONSTRAINT FK_92FB68EF4837C1B FOREIGN KEY (org_id) REFERENCES organization (id)');
        $this->addSql('CREATE INDEX IDX_92FB68EF4837C1B ON device (org_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE device DROP FOREIGN KEY FK_92FB68EF4837C1B');
        $this->addSql('DROP INDEX IDX_92FB68EF4837C1B ON device');
        $this->addSql('ALTER TABLE device CHANGE org_id org_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE device ADD CONSTRAINT FK_92FB68E81096D35 FOREIGN KEY (org_id_id) REFERENCES organization (id)');
        $this->addSql('CREATE INDEX IDX_92FB68E81096D35 ON device (org_id_id)');
    }
}
