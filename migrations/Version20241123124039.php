<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241123124039 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE account (id INT AUTO_INCREMENT NOT NULL, bank VARCHAR(255) NOT NULL, currency VARCHAR(255) NOT NULL, iban VARCHAR(255) NOT NULL, alias VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, enabled TINYINT(1) NOT NULL, created_at DATETIME(4) NOT NULL COMMENT \'(DC2Type:datetime_microseconds)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE captured_request (id INT AUTO_INCREMENT NOT NULL, ip VARCHAR(255) DEFAULT NULL, source VARCHAR(255) DEFAULT NULL, message VARCHAR(1024) DEFAULT NULL, content LONGTEXT DEFAULT NULL, headers LONGTEXT DEFAULT NULL, request LONGTEXT DEFAULT NULL, server LONGTEXT DEFAULT NULL, created_at DATETIME(4) DEFAULT NULL COMMENT \'(DC2Type:datetime_microseconds)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category_rule (id INT AUTO_INCREMENT NOT NULL, account_id INT DEFAULT NULL, category_id INT DEFAULT NULL, sub_category_id INT DEFAULT NULL, matches LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', debit VARCHAR(255) DEFAULT NULL, credit VARCHAR(255) DEFAULT NULL, enabled TINYINT(1) NOT NULL, position INT NOT NULL, name VARCHAR(255) DEFAULT NULL, INDEX IDX_CD43D68B9B6B5FBA (account_id), INDEX IDX_CD43D68B12469DE2 (category_id), INDEX IDX_CD43D68BF7BFE87C (sub_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE command_result (id INT AUTO_INCREMENT NOT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', command VARCHAR(255) NOT NULL, result VARCHAR(255) NOT NULL, output LONGTEXT DEFAULT NULL, duration DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE imported_file (id INT AUTO_INCREMENT NOT NULL, account_id INT NOT NULL, folder VARCHAR(255) NOT NULL, file_name VARCHAR(255) NOT NULL, imported_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', force_re_import TINYINT(1) NOT NULL, file_created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', parsed_records INT DEFAULT NULL, INDEX IDX_451D1DFD9B6B5FBA (account_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE missing_record (id INT AUTO_INCREMENT NOT NULL, account_id INT NOT NULL, created_at DATETIME(4) DEFAULT NULL COMMENT \'(DC2Type:datetime_microseconds)\', parsed_record LONGTEXT NOT NULL, updated_at DATETIME(4) DEFAULT NULL COMMENT \'(DC2Type:datetime_microseconds)\', solved TINYINT(1) NOT NULL, INDEX IDX_CBF84AB79B6B5FBA (account_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE missing_record_record (missing_record_id INT NOT NULL, record_id INT NOT NULL, INDEX IDX_F01E52AD1FA0F0CF (missing_record_id), INDEX IDX_F01E52AD4DFD750C (record_id), PRIMARY KEY(missing_record_id, record_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE record (id INT AUTO_INCREMENT NOT NULL, account_id INT NOT NULL, category_id INT DEFAULT NULL, sub_category_id INT DEFAULT NULL, captured_request_id INT DEFAULT NULL, created_at DATETIME(4) NOT NULL COMMENT \'(DC2Type:datetime_microseconds)\', date DATE NOT NULL, debit DOUBLE PRECISION NOT NULL, credit DOUBLE PRECISION NOT NULL, balance DOUBLE PRECISION NOT NULL, description LONGTEXT DEFAULT NULL, details LONGTEXT DEFAULT NULL, hash VARCHAR(255) NOT NULL, updated_at DATETIME(4) DEFAULT NULL COMMENT \'(DC2Type:datetime_microseconds)\', notified_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_9B349F919B6B5FBA (account_id), INDEX IDX_9B349F9112469DE2 (category_id), INDEX IDX_9B349F91F7BFE87C (sub_category_id), INDEX IDX_9B349F9139484E62 (captured_request_id), INDEX date_idx (date), INDEX debit_idx (debit), INDEX credit_idx (credit), INDEX balanceidx (balance), INDEX hash_idx (hash), INDEX notified_idx (notified_at), INDEX created_idx (created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sub_category (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_BCE3F79812469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_389B7835E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tagging (resource_type VARCHAR(255) NOT NULL, resource_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_A4AED123BAD26311 (tag_id), PRIMARY KEY(tag_id, resource_type, resource_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, avatar VARCHAR(255) DEFAULT NULL, created_at DATETIME(4) NOT NULL COMMENT \'(DC2Type:datetime_microseconds)\', updated_at DATETIME(4) DEFAULT NULL COMMENT \'(DC2Type:datetime_microseconds)\', UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE category_rule ADD CONSTRAINT FK_CD43D68B9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE category_rule ADD CONSTRAINT FK_CD43D68B12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE category_rule ADD CONSTRAINT FK_CD43D68BF7BFE87C FOREIGN KEY (sub_category_id) REFERENCES sub_category (id)');
        $this->addSql('ALTER TABLE imported_file ADD CONSTRAINT FK_451D1DFD9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE missing_record ADD CONSTRAINT FK_CBF84AB79B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE missing_record_record ADD CONSTRAINT FK_F01E52AD1FA0F0CF FOREIGN KEY (missing_record_id) REFERENCES missing_record (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE missing_record_record ADD CONSTRAINT FK_F01E52AD4DFD750C FOREIGN KEY (record_id) REFERENCES record (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE record ADD CONSTRAINT FK_9B349F919B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE record ADD CONSTRAINT FK_9B349F9112469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE record ADD CONSTRAINT FK_9B349F91F7BFE87C FOREIGN KEY (sub_category_id) REFERENCES sub_category (id)');
        $this->addSql('ALTER TABLE record ADD CONSTRAINT FK_9B349F9139484E62 FOREIGN KEY (captured_request_id) REFERENCES captured_request (id)');
        $this->addSql('ALTER TABLE sub_category ADD CONSTRAINT FK_BCE3F79812469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE tagging ADD CONSTRAINT FK_A4AED123BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id)');

        $this->addSql(<<<'SQL'
INSERT INTO `account` (`id`, `bank`, `currency`, `iban`, `alias`, `description`, `enabled`, `created_at`) VALUES
	(1, 'Revolut', 'DKK', 'REVOREVOREVOREVOREVORDKK', 'DKK', NULL, 0, '2024-11-04 11:54:05.336556'),
	(2, 'Revolut', 'EUR', 'REVOREVOREVOREVOREVOREUR', '💶 EUR', NULL, 0, '2024-11-04 11:54:05.677983'),
	(3, 'Revolut', 'GBP', 'REVOREVOREVOREVOREVORGBP', 'GBP', NULL, 0, '2024-11-04 11:54:05.681846'),
	(4, 'Revolut', 'RON', 'REVOREVOREVOREVOREVORRON', 'RON', NULL, 0, '2024-11-04 11:54:05.685620'),
	(5, 'ING', 'USD', 'RO22INGB0000999902626887', '💵 Primar', NULL, 0, '2024-11-04 11:54:05.689331'),
	(6, 'ING', 'EUR', 'RO24INGB0000999905332084', '💶 Primar', NULL, 0, '2024-11-04 11:54:05.700802'),
	(7, 'ING', 'RON', 'RO64INGB5649999901181524', '🤑 Primar', NULL, 0, '2024-11-04 11:54:05.713935'),
	(8, 'Banca Transilvania', 'RON', 'RO72BTRL06301201H13496XX', 'Primar', NULL, 0, '2024-11-04 11:54:05.724734'),
	(9, 'ING', 'RON', 'RO87INGB0000999908493441', 'Secundar', NULL, 0, '2024-11-04 11:54:05.728367'),
	(10, 'ING', 'RON', 'RO95INGB0000999903001060', 'Economii', NULL, 0, '2024-11-04 11:54:05.735733'),
	(11, 'ING', 'RON', '----INGB----999915308019', 'Credit Nevoi Personale', NULL, 0, '2024-11-18 13:51:39.511690');");
SQL);
        $this->addSql(<<<'SQL'
INSERT INTO `category` (`id`, `name`) VALUES
	(1, '🧳 Travel'),
	(2, '⛛ Other'),
	(3, '🛒 Shopping'),
	(4, '☂️ Investments and insurance'),
	(5, '💸 Loans'),
	(6, '🏧 ATM withdrawal'),
	(7, '💼 Services'),
	(8, '🏦 Taxes and commissions'),
	(9, '🍷 Leasure and education'),
	(10, '↔️ Transfers'),
	(11, '🚗 Transport'),
	(12, '⚡ Utilities'),
	(13, '💰Income');
SQL);

        $this->addSql(<<<'SQL'
INSERT INTO `sub_category` (`id`, `category_id`, `name`) VALUES
	(1, 1, 'Tourism agencies'),
	(2, 1, 'Hotels'),
	(3, 1, 'Flights and airport costs'),
	(4, 3, 'Pets'),
	(5, 3, 'House and garden'),
	(6, 3, 'Children'),
	(7, 3, 'Fashion'),
	(8, 3, 'Sport'),
	(9, 3, 'Miscellaneous'),
	(10, 4, 'Insurance and pensions'),
	(11, 4, 'Investments'),
	(12, 5, 'Personal needs loan'),
	(13, 6, 'ATM withdrawal'),
	(14, 7, 'Donations'),
	(15, 7, 'Miscellaneous services'),
	(16, 7, 'Online services'),
	(17, 7, 'Health'),
	(18, 7, 'Personal care'),
	(19, 8, 'Bank commissions'),
	(20, 8, 'Property taxes'),
	(21, 9, 'Fun'),
	(22, 9, 'Education'),
	(23, 9, 'Bars and restaurants'),
	(24, 10, 'Transfers to other persons'),
	(25, 11, 'Gas and Tolls'),
	(26, 11, 'Car costs'),
	(27, 11, 'Transport'),
	(28, 12, 'Gas and electricity'),
	(29, 12, 'Phone bills'),
	(30, 12, 'Internet provider'),
	(31, 12, 'Water'),
	(32, 3, 'Supermarket'),
	(33, 3, 'IQOS'),
	(34, 3, 'Beer'),
	(35, 13, 'Timisoara'),
	(36, 13, 'Canada'),
	(37, 13, 'Interest'),
	(38, 13, 'Savings'),
	(39, 4, 'Savings'),
	(40, 13, 'Transfers'),
	(41, 10, 'Exchanges'),
	(42, 13, 'Exchanges'),
	(43, 1, 'Expenses'),
	(44, 13, 'Corrections'),
	(45, 3, 'Online Products'),
	(46, 3, 'Electronics'),
	(47, 3, 'Health'),
	(48, 3, 'Small Markets'),
	(49, 3, 'Food'),
	(50, 13, 'Timisoara CRM'),
	(51, 13, 'Loans');
SQL);
        $this->addSql(<<<'SQL'
INSERT INTO `category_rule` (`id`, `account_id`, `category_id`, `sub_category_id`, `matches`, `debit`, `credit`, `enabled`, `position`, `name`) VALUES
	(1, NULL, 13, 40, '{"type":"TRANSFER"}|Transfer,Popescu,Opris,FARCAS,Munteanu Alexandru,Durnea,MUNTEANU DRAGOS,Miron Cristian,Pop Mihai,Bucurestean Ioana Andrada,Radulesc Anca Maria,Goaga Adela Smaranda,SAMFIRA CONSTANTIN,{"type":"TOPUP"}|Topup', NULL, 'amount > 0', 1, 0, 'Transferuri catre mine'),
	(2, NULL, 13, 36, '2SHIP SOLUTIONS INC|Salariu canada,Munteanu A. Alexandru|Salariu canada', NULL, NULL, 1, 1, 'Salariu Canada'),
	(3, NULL, 13, 35, 'SMARTVALUE SERVICES SRL', NULL, NULL, 1, 2, 'Salariu Timisoara'),
	(4, NULL, 8, 19, 'comisioane|Comisioane,Comision,Acoperire sold negativ neautorizat', NULL, NULL, 1, 3, 'Comisioane pe toate conturile'),
	(5, NULL, 6, 13, 'Retragere numerar,{"type":"ATM"}|Retragere numerar', NULL, NULL, 1, 4, 'Retrageri numerar'),
	(6, NULL, 4, 10, 'NN ASIGURARI,ING ASIGURARI', NULL, NULL, 1, 5, 'Asigurari pe toate conturile'),
	(7, NULL, 13, 37, 'Actualizare dobanda,Bonusuri', NULL, 'amount > 0', 1, 6, 'Dobanzi primite'),
	(8, 10, 13, 38, 'Tranzactie Round Up,Ai economisit', NULL, 'amount > 0', 1, 7, 'Round up intrari'),
	(9, 7, 4, 39, 'Tranzactie Round Up,Ai economisit', NULL, NULL, 1, 8, 'Round up iesiri'),
	(10, NULL, 10, 24, '{"type":"TRANSFER"}|Transfer,MUNTEANU DRAGOS|Dragos,Miron Cristian|Blacky,Pop Mihai,Bucurestean Ioana Andrada|Fina,Radulesc Anca Maria,Goaga Adela Smaranda,SAMFIRA CONSTANTIN|Junior,{"Transfer":""|Transfer,Rares Munteanu|Rares,Tudor Munteanu|Tudor,{"type":"TEMP_BLOCK"}|Transfer,MUNTEAN DRAGOS ALIN|Naganu,MUNTEANU Aida Isabela|Iza,Muntean Dragos|Naganu,Munteanu Rares|Rares,Munteanu Tudor|Tudor,Trif Dorin|Cretu,Trusca Dorel,Victor Prajitura', 'amount > 0', NULL, 1, 9, 'Transferuri catre alte persoane'),
	(12, NULL, 13, 42, '{"type":"EXCHANGE"}|Exchange,Schimb valutar Home', NULL, 'amount > 0', 1, 11, 'Schimburi valutare, doar intrari'),
	(13, NULL, 7, 16, 'Steam,ITUNES.COM/BILL|Apple,CLAUS WEB|Hosting,Abonament BT 24,APPLE.COM/BILL|Apple', 'amount > 0', NULL, 1, 12, 'Servicii online'),
	(15, NULL, 3, 32, 'Lidl,Kaufland,DISTRI-HIPER|Auchan,COLUMBUS OPERATIONAL SRL|Carrefour,PROFI MAG|Profi,AUCHAN|Auchan,CARREFOUR ROMANIA|Carrefour,REWE ROMANIA|Penny,PENNY|Penny', 'amount > 0', NULL, 1, 14, 'Supermarket'),
	(18, NULL, 10, 41, 'Schimb valutar Home|Exchange,Beneficiar: Alexandru Munteanu|Exchange,{"type":"EXCHANGE"}|Exchange', 'amount > 0', NULL, 1, 17, 'Schimburi valutare, doar iesiri'),
	(20, NULL, 12, 29, 'COSMOTE MOBILE|Cosmote,WWW.ORANGE.RO|Orange,TELEKOM|Telekom,ORANGE ROMANIA|Orange,VODAFONE|Vodafone', 'amount > 0', NULL, 1, 19, 'Cheltuieli telefon'),
	(21, 6, 1, 43, 'Cumparare POS', 'amount > 0', NULL, 1, 20, NULL),
	(23, NULL, 3, 45, 'PayU*eMAG.ro|eMAG,PayU*pcgarage|PC Garage,LIBRAPAY  RO  BUCURESTI|Librapay', 'amount > 0', NULL, 1, 21, NULL),
	(24, NULL, 12, 28, 'WWW.MYLINE-EON|E-ON,ENEL ENERGIE SA|Enel,WWW.APAPROD.RO|Apa,SC APA PROD SA|Apa', 'amount > 0', NULL, 1, 22, 'Cheltuieli cu casa'),
	(26, NULL, 3, 33, 'IQOS,IQOS KAUFLAND|IQOS,TABAC|IQOS,Mediapost Hit Mail SA|IQOS', 'amount > 0', NULL, 1, 0, 'IQOS'),
	(27, NULL, 3, 9, '{"type":"CARD_PAYMENT"}|Revolut card,{"type":"REV_PAYMENT"}|Revolut payment,1001 ARTICOLE  RO  HUNEDOARA', 'amount > 0', NULL, 1, 23, NULL),
	(29, NULL, 11, 25, 'LUKOIL|Lukoil,MRC AUTOSTRADA A1|OMV,OMV|OMV,MOL |Mol', 'amount > 0', NULL, 1, 25, NULL),
	(31, NULL, 3, 46, 'MEDIA GALAXY|Media Galaxy,NEXT COMPUTER DEP|Next Computer,ALTEX|Altex', 'amount > 0', NULL, 1, 27, NULL),
	(32, NULL, 3, 47, 'Farmacia|Farmacie,Farmacie|Farmacie,SENSIBLU|Farmacie,Catena|Farmacie', 'amount > 0', NULL, 1, 28, NULL),
	(33, NULL, 3, 48, 'SC FINELOR PRESTCOM|Finelor,SERGIANA PRODIMPEX|Sergiana,LS TRAVEL RETAIL|Inmedio,CENTRUL DE PRINT|Centrul de print,MAGAZIN O.M. DEP  RO  HUNEDOARA|ABC,PEPCO|Pepco,CONTAKT DEVA|Huse si accesorii telefoane', 'amount > 0', NULL, 1, 29, NULL),
	(35, NULL, 12, 30, 'digicare.rcs-rds.ro|Digi', 'amount > 0', NULL, 1, 22, NULL),
	(36, NULL, 3, 5, 'DEDEMAN|Dedeman,IKEA ROMANIA|IKEA,PRAKTIKER|Praktiker,BRICOSTORE|Bricostore,LEROY MERLIN|Leroy Merlin', 'amount > 0', NULL, 1, 32, NULL),
	(37, NULL, 13, 44, 'Cumparare POS corectie|Corectie', NULL, 'amount > 0', 1, 32, 'Corectii'),
	(38, NULL, 9, 23, 'MIDI DEVELOPMENT SRL|Beraria H,MCDONALDS|McDonalds,KFC|Kfc', 'amount > 0', NULL, 1, 33, NULL),
	(39, NULL, 3, 8, 'ROUMASPORT SRL|Decathlon', 'amount > 0', NULL, 1, 34, NULL),
	(40, NULL, 3, 6, 'NORIELTOYS|Noriel,JUMBO|Jumbo,NORIEL|Noriel,SMYTHS TOYS|Toys', NULL, NULL, 1, 34, NULL),
	(41, NULL, 3, 7, 'ALISS SHOES|Aliss,C&A|C & A', NULL, NULL, 1, 35, NULL),
	(42, NULL, 3, 49, 'Glovo', 'amount > 0', NULL, 1, 36, NULL),
	(43, NULL, 13, 50, 'CRM SOFTWARE SRL|Salariu CRM', NULL, 'amount > 0', 1, 37, 'Salariu CRM'),
	(45, NULL, 13, 51, 'Alocare fonduri credit', NULL, 'amount > 0', 1, 33, 'Intrare credit in cont secundar'),
	(46, NULL, 5, 12, 'Alocare fonduri credit', 'amount > 0', NULL, 1, 34, NULL),
	(47, NULL, 7, 17, 'Cord Blood|Celule stem', 'amount > 0', NULL, 1, 35, NULL),
	(48, NULL, 1, 3, 'WIZZ AIR|Wizz,GATE RETAIL|Cheltuieli aeroport,RYANAIR|Ryanair,AEROP. INT. TIMISOARA|Cheltuieli aeroport,AEROPORTO|Cheltuieli aeroport,Airport  GB  Luton|Cheltuieli aeroport', 'amount > 0', NULL, 1, 35, 'Zboruri'),
	(49, NULL, 3, 4, 'ALMAVET|Veterinar,ANDIVET|Veterinar,CRIS GLOBAL TRADING|Food,VABRO RETAIL|Food', 'amount > 0', NULL, 1, 36, 'Animale');

SQL);

        $this->addSql(<<<'SQL'
INSERT INTO `user` (`id`, `email`, `roles`, `password`, `enabled`, `first_name`, `last_name`, `avatar`, `created_at`, `updated_at`) VALUES
	(1, 'importer.user@example.com', '[]', '$2y$13$HU3qnc1V9VBCM8nQI7IxNOAM/BmXyxpxp5jsmIb1hYlJ4E//60y/q', 1, 'Importer', 'User', NULL, '2024-11-01 12:20:08', '2024-11-21 09:05:54'),
	(2, 'munteanucalexandru@gmail.com', '["ROLE_USER","ROLE_SUPER_ADMIN"]', '$2y$13$GEmTBAmyNzL7Xc3JRDaJUOcQfFPhUUT9v.lSN9yvINLg6Yd9FDHfC', 1, 'Alex', 'M', 'unnamed-3-1732179900.jpg', '2024-11-01 12:20:09', '2024-11-21 09:05:01'),
	(3, 'munteanuaida2@gmail.com', '["ROLE_USER","ROLE_SUPER_ADMIN"]', 'N7I5xx3T2F7C', 1, 'Iza', 'M', 'unnamed-4-1732180516.jpg', '2024-11-21 11:14:03', '2024-11-21 09:15:19');

SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category_rule DROP FOREIGN KEY FK_CD43D68B9B6B5FBA');
        $this->addSql('ALTER TABLE category_rule DROP FOREIGN KEY FK_CD43D68B12469DE2');
        $this->addSql('ALTER TABLE category_rule DROP FOREIGN KEY FK_CD43D68BF7BFE87C');
        $this->addSql('ALTER TABLE imported_file DROP FOREIGN KEY FK_451D1DFD9B6B5FBA');
        $this->addSql('ALTER TABLE missing_record DROP FOREIGN KEY FK_CBF84AB79B6B5FBA');
        $this->addSql('ALTER TABLE missing_record_record DROP FOREIGN KEY FK_F01E52AD1FA0F0CF');
        $this->addSql('ALTER TABLE missing_record_record DROP FOREIGN KEY FK_F01E52AD4DFD750C');
        $this->addSql('ALTER TABLE record DROP FOREIGN KEY FK_9B349F919B6B5FBA');
        $this->addSql('ALTER TABLE record DROP FOREIGN KEY FK_9B349F9112469DE2');
        $this->addSql('ALTER TABLE record DROP FOREIGN KEY FK_9B349F91F7BFE87C');
        $this->addSql('ALTER TABLE record DROP FOREIGN KEY FK_9B349F9139484E62');
        $this->addSql('ALTER TABLE sub_category DROP FOREIGN KEY FK_BCE3F79812469DE2');
        $this->addSql('ALTER TABLE tagging DROP FOREIGN KEY FK_A4AED123BAD26311');
        $this->addSql('DROP TABLE account');
        $this->addSql('DROP TABLE captured_request');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE category_rule');
        $this->addSql('DROP TABLE command_result');
        $this->addSql('DROP TABLE imported_file');
        $this->addSql('DROP TABLE missing_record');
        $this->addSql('DROP TABLE missing_record_record');
        $this->addSql('DROP TABLE record');
        $this->addSql('DROP TABLE sub_category');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE tagging');
        $this->addSql('DROP TABLE `user`');
    }
}
