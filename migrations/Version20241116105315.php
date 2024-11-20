<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241116105315 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category_rule (id INT AUTO_INCREMENT NOT NULL, account_id INT DEFAULT NULL, category_id INT DEFAULT NULL, sub_category_id INT DEFAULT NULL, matches LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', debit VARCHAR(255) DEFAULT NULL, credit VARCHAR(255) DEFAULT NULL, INDEX IDX_CD43D68B9B6B5FBA (account_id), INDEX IDX_CD43D68B12469DE2 (category_id), INDEX IDX_CD43D68BF7BFE87C (sub_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE category_rule ADD CONSTRAINT FK_CD43D68B9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE category_rule ADD CONSTRAINT FK_CD43D68B12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE category_rule ADD CONSTRAINT FK_CD43D68BF7BFE87C FOREIGN KEY (sub_category_id) REFERENCES sub_category (id)');
        $this->addSql('ALTER TABLE account CHANGE created_at created_at DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime)\'');
        $this->addSql('ALTER TABLE command_result CHANGE date date DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime)\'');
        $this->addSql('ALTER TABLE imported_file CHANGE imported_at imported_at DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime)\', CHANGE file_created_at file_created_at DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime)\'');
        $this->addSql('ALTER TABLE record CHANGE created_at created_at DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime)\', CHANGE updated_at updated_at DATETIME(6) DEFAULT NULL COMMENT \'(DC2Type:datetime)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category_rule DROP FOREIGN KEY FK_CD43D68B9B6B5FBA');
        $this->addSql('ALTER TABLE category_rule DROP FOREIGN KEY FK_CD43D68B12469DE2');
        $this->addSql('ALTER TABLE category_rule DROP FOREIGN KEY FK_CD43D68BF7BFE87C');
        $this->addSql('DROP TABLE category_rule');
        $this->addSql('ALTER TABLE account CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE record CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE command_result CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE imported_file CHANGE imported_at imported_at DATETIME NOT NULL, CHANGE file_created_at file_created_at DATETIME NOT NULL');
    }
}
