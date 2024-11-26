<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241126170848 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE account (id INT AUTO_INCREMENT NOT NULL, bank VARCHAR(255) NOT NULL, currency VARCHAR(255) NOT NULL, iban VARCHAR(255) NOT NULL, alias VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, enabled TINYINT(1) NOT NULL, created_at DATETIME(4) NOT NULL COMMENT \'(DC2Type:datetime_microseconds)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category_rule (id INT AUTO_INCREMENT NOT NULL, account_id INT DEFAULT NULL, category_id INT DEFAULT NULL, sub_category_id INT DEFAULT NULL, matches LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', debit VARCHAR(255) DEFAULT NULL, credit VARCHAR(255) DEFAULT NULL, enabled TINYINT(1) NOT NULL, position INT NOT NULL, name VARCHAR(255) DEFAULT NULL, INDEX IDX_CD43D68B9B6B5FBA (account_id), INDEX IDX_CD43D68B12469DE2 (category_id), INDEX IDX_CD43D68BF7BFE87C (sub_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE command_result (id INT AUTO_INCREMENT NOT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', command VARCHAR(255) NOT NULL, result VARCHAR(255) NOT NULL, output LONGTEXT DEFAULT NULL, duration DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE imported_file (id INT AUTO_INCREMENT NOT NULL, account_id INT NOT NULL, folder VARCHAR(255) NOT NULL, file_name VARCHAR(255) NOT NULL, imported_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', force_re_import TINYINT(1) NOT NULL, file_created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', parsed_records INT DEFAULT NULL, INDEX IDX_451D1DFD9B6B5FBA (account_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME(4) NOT NULL COMMENT \'(DC2Type:datetime_microseconds)\', updated_at DATETIME(4) DEFAULT NULL COMMENT \'(DC2Type:datetime_microseconds)\', original_time DATETIME(4) DEFAULT NULL COMMENT \'(DC2Type:datetime_microseconds)\', sent_at DATETIME(4) DEFAULT NULL COMMENT \'(DC2Type:datetime_microseconds)\', source VARCHAR(255) DEFAULT NULL, message VARCHAR(2048) DEFAULT NULL, content LONGTEXT DEFAULT NULL, result LONGTEXT DEFAULT NULL, details LONGTEXT DEFAULT NULL, ip VARCHAR(255) DEFAULT NULL, headers LONGTEXT DEFAULT NULL, INDEX created_idx (created_at), INDEX sent_idx (sent_at), INDEX source_idx (source), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE record (id INT AUTO_INCREMENT NOT NULL, account_id INT NOT NULL, category_id INT DEFAULT NULL, sub_category_id INT DEFAULT NULL, created_at DATETIME(4) NOT NULL COMMENT \'(DC2Type:datetime_microseconds)\', date DATE NOT NULL, debit DOUBLE PRECISION NOT NULL, credit DOUBLE PRECISION NOT NULL, balance DOUBLE PRECISION NOT NULL, description LONGTEXT DEFAULT NULL, details LONGTEXT DEFAULT NULL, hash VARCHAR(255) DEFAULT NULL, updated_at DATETIME(4) DEFAULT NULL COMMENT \'(DC2Type:datetime_microseconds)\', notified_at DATETIME(4) DEFAULT NULL COMMENT \'(DC2Type:datetime_microseconds)\', reconciled TINYINT(1) NOT NULL, INDEX IDX_9B349F919B6B5FBA (account_id), INDEX IDX_9B349F9112469DE2 (category_id), INDEX IDX_9B349F91F7BFE87C (sub_category_id), INDEX date_idx (date), INDEX debit_idx (debit), INDEX credit_idx (credit), INDEX balance_idx (balance), INDEX hash_idx (hash), INDEX notified_idx (notified_at), INDEX created_idx (created_at), INDEX reconciled_idx (reconciled), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sub_category (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_BCE3F79812469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE subscriptions (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, INDEX IDX_4778A01A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_389B7835E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tagging (resource_type VARCHAR(255) NOT NULL, resource_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_A4AED123BAD26311 (tag_id), PRIMARY KEY(tag_id, resource_type, resource_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, avatar VARCHAR(255) DEFAULT NULL, created_at DATETIME(4) NOT NULL COMMENT \'(DC2Type:datetime_microseconds)\', updated_at DATETIME(4) DEFAULT NULL COMMENT \'(DC2Type:datetime_microseconds)\', UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE category_rule ADD CONSTRAINT FK_CD43D68B9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE category_rule ADD CONSTRAINT FK_CD43D68B12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE category_rule ADD CONSTRAINT FK_CD43D68BF7BFE87C FOREIGN KEY (sub_category_id) REFERENCES sub_category (id)');
        $this->addSql('ALTER TABLE imported_file ADD CONSTRAINT FK_451D1DFD9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE record ADD CONSTRAINT FK_9B349F919B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE record ADD CONSTRAINT FK_9B349F9112469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE record ADD CONSTRAINT FK_9B349F91F7BFE87C FOREIGN KEY (sub_category_id) REFERENCES sub_category (id)');
        $this->addSql('ALTER TABLE sub_category ADD CONSTRAINT FK_BCE3F79812469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE subscriptions ADD CONSTRAINT FK_4778A01A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE tagging ADD CONSTRAINT FK_A4AED123BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category_rule DROP FOREIGN KEY FK_CD43D68B9B6B5FBA');
        $this->addSql('ALTER TABLE category_rule DROP FOREIGN KEY FK_CD43D68B12469DE2');
        $this->addSql('ALTER TABLE category_rule DROP FOREIGN KEY FK_CD43D68BF7BFE87C');
        $this->addSql('ALTER TABLE imported_file DROP FOREIGN KEY FK_451D1DFD9B6B5FBA');
        $this->addSql('ALTER TABLE record DROP FOREIGN KEY FK_9B349F919B6B5FBA');
        $this->addSql('ALTER TABLE record DROP FOREIGN KEY FK_9B349F9112469DE2');
        $this->addSql('ALTER TABLE record DROP FOREIGN KEY FK_9B349F91F7BFE87C');
        $this->addSql('ALTER TABLE sub_category DROP FOREIGN KEY FK_BCE3F79812469DE2');
        $this->addSql('ALTER TABLE subscriptions DROP FOREIGN KEY FK_4778A01A76ED395');
        $this->addSql('ALTER TABLE tagging DROP FOREIGN KEY FK_A4AED123BAD26311');
        $this->addSql('DROP TABLE account');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE category_rule');
        $this->addSql('DROP TABLE command_result');
        $this->addSql('DROP TABLE imported_file');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE record');
        $this->addSql('DROP TABLE sub_category');
        $this->addSql('DROP TABLE subscriptions');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE tagging');
        $this->addSql('DROP TABLE `user`');
    }
}
