<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241116080726 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sub_category (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_BCE3F79812469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sub_category ADD CONSTRAINT FK_BCE3F79812469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE categories DROP FOREIGN KEY FK_3AF34668727ACA70');
        $this->addSql('ALTER TABLE categories DROP FOREIGN KEY FK_3AF34668A977936C');
        $this->addSql('DROP TABLE categories');
        $this->addSql('ALTER TABLE account CHANGE created_at created_at DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime)\'');
        $this->addSql('ALTER TABLE command_result CHANGE date date DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime)\'');
        $this->addSql('ALTER TABLE imported_file CHANGE imported_at imported_at DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime)\', CHANGE file_created_at file_created_at DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime)\'');
        $this->addSql('ALTER TABLE record ADD category_id INT DEFAULT NULL, ADD sub_category_id INT DEFAULT NULL, CHANGE created_at created_at DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime)\', CHANGE updated_at updated_at DATETIME(6) DEFAULT NULL COMMENT \'(DC2Type:datetime)\'');
        $this->addSql('ALTER TABLE record ADD CONSTRAINT FK_9B349F9112469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE record ADD CONSTRAINT FK_9B349F91F7BFE87C FOREIGN KEY (sub_category_id) REFERENCES sub_category (id)');
        $this->addSql('CREATE INDEX IDX_9B349F9112469DE2 ON record (category_id)');
        $this->addSql('CREATE INDEX IDX_9B349F91F7BFE87C ON record (sub_category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE record DROP FOREIGN KEY FK_9B349F9112469DE2');
        $this->addSql('ALTER TABLE record DROP FOREIGN KEY FK_9B349F91F7BFE87C');
        $this->addSql('CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, tree_root INT DEFAULT NULL, parent_id INT DEFAULT NULL, title VARCHAR(64) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, lft INT NOT NULL, lvl INT NOT NULL, rgt INT NOT NULL, INDEX IDX_3AF34668A977936C (tree_root), INDEX IDX_3AF34668727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE categories ADD CONSTRAINT FK_3AF34668727ACA70 FOREIGN KEY (parent_id) REFERENCES categories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE categories ADD CONSTRAINT FK_3AF34668A977936C FOREIGN KEY (tree_root) REFERENCES categories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sub_category DROP FOREIGN KEY FK_BCE3F79812469DE2');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE sub_category');
        $this->addSql('ALTER TABLE account CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('DROP INDEX IDX_9B349F9112469DE2 ON record');
        $this->addSql('DROP INDEX IDX_9B349F91F7BFE87C ON record');
        $this->addSql('ALTER TABLE record DROP category_id, DROP sub_category_id, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE command_result CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE imported_file CHANGE imported_at imported_at DATETIME NOT NULL, CHANGE file_created_at file_created_at DATETIME NOT NULL');
    }
}
