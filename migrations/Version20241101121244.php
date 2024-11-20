<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241101121244 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE account (id INT AUTO_INCREMENT NOT NULL, bank VARCHAR(255) NOT NULL, currency VARCHAR(255) NOT NULL, iban VARCHAR(255) NOT NULL, alias VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, enabled TINYINT(1) NOT NULL, created_at DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE command_result (id INT AUTO_INCREMENT NOT NULL, date DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime)\', command VARCHAR(255) NOT NULL, result VARCHAR(255) NOT NULL, output LONGTEXT DEFAULT NULL, duration DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE imported_file (id INT AUTO_INCREMENT NOT NULL, account_id INT NOT NULL, folder VARCHAR(255) NOT NULL, file_name VARCHAR(255) NOT NULL, imported_at DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime)\', force_re_import TINYINT(1) NOT NULL, file_created_at DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime)\', parsed_records INT DEFAULT NULL, INDEX IDX_451D1DFD9B6B5FBA (account_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE record (id INT AUTO_INCREMENT NOT NULL, account_id INT NOT NULL, created_at DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime)\', date DATE NOT NULL, debit DOUBLE PRECISION NOT NULL, credit DOUBLE PRECISION NOT NULL, balance DOUBLE PRECISION NOT NULL, description LONGTEXT DEFAULT NULL, reference VARCHAR(255) DEFAULT NULL, details JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', hash VARCHAR(255) NOT NULL, updated_at DATETIME(6) DEFAULT NULL COMMENT \'(DC2Type:datetime)\', INDEX IDX_9B349F919B6B5FBA (account_id), INDEX date_idx (date), INDEX debit_idx (debit), INDEX credit_idx (credit), INDEX hash_idx (hash), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_389B7835E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tagging (resource_type VARCHAR(255) NOT NULL, resource_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_A4AED123BAD26311 (tag_id), PRIMARY KEY(tag_id, resource_type, resource_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, avatar VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE imported_file ADD CONSTRAINT FK_451D1DFD9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE record ADD CONSTRAINT FK_9B349F919B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE tagging ADD CONSTRAINT FK_A4AED123BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE imported_file DROP FOREIGN KEY FK_451D1DFD9B6B5FBA');
        $this->addSql('ALTER TABLE record DROP FOREIGN KEY FK_9B349F919B6B5FBA');
        $this->addSql('ALTER TABLE tagging DROP FOREIGN KEY FK_A4AED123BAD26311');
        $this->addSql('DROP TABLE account');
        $this->addSql('DROP TABLE command_result');
        $this->addSql('DROP TABLE imported_file');
        $this->addSql('DROP TABLE record');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE tagging');
        $this->addSql('DROP TABLE `user`');
    }
}
