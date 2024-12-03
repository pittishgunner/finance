<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241129113117 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subscriptions ADD subscription LONGTEXT NOT NULL');
        $this->addSql('CREATE INDEX subscription_idx ON subscriptions (subscription)');
        $this->addSql('ALTER TABLE subscriptions RENAME INDEX idx_4778a01a76ed395 TO user_id_idx');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX subscription_idx ON subscriptions');
        $this->addSql('ALTER TABLE subscriptions DROP subscription');
        $this->addSql('ALTER TABLE subscriptions RENAME INDEX user_id_idx TO IDX_4778A01A76ED395');
    }
}
