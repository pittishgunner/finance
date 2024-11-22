<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241122153637 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE account CHANGE created_at created_at DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime)\'');
        $this->addSql('ALTER TABLE captured_request CHANGE created_at created_at DATETIME(6) DEFAULT NULL COMMENT \'(DC2Type:datetime)\'');
        $this->addSql('ALTER TABLE command_result CHANGE date date DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime)\'');
        $this->addSql('ALTER TABLE imported_file CHANGE imported_at imported_at DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime)\', CHANGE file_created_at file_created_at DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime)\'');
        $this->addSql('ALTER TABLE record ADD notified_at DATETIME(6) DEFAULT NULL COMMENT \'(DC2Type:datetime)\', CHANGE created_at created_at DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime)\', CHANGE hash hash TINYTEXT NOT NULL, CHANGE updated_at updated_at DATETIME(6) DEFAULT NULL COMMENT \'(DC2Type:datetime)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE account CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE captured_request CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE command_result CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE imported_file CHANGE imported_at imported_at DATETIME NOT NULL, CHANGE file_created_at file_created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE record DROP notified_at, CHANGE created_at created_at DATETIME NOT NULL, CHANGE hash hash VARCHAR(255) DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
    }
}
