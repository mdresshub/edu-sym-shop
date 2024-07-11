<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240711114825 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE kategorie (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE gericht ADD kategorie_id INT NOT NULL, ADD preis DOUBLE PRECISION DEFAULT NULL, ADD bild VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE gericht ADD CONSTRAINT FK_FEA51929BAF991D3 FOREIGN KEY (kategorie_id) REFERENCES kategorie (id)');
        $this->addSql('CREATE INDEX IDX_FEA51929BAF991D3 ON gericht (kategorie_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gericht DROP FOREIGN KEY FK_FEA51929BAF991D3');
        $this->addSql('DROP TABLE kategorie');
        $this->addSql('DROP INDEX IDX_FEA51929BAF991D3 ON gericht');
        $this->addSql('ALTER TABLE gericht DROP kategorie_id, DROP preis, DROP bild');
    }
}
