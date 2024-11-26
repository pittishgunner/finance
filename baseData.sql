-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               11.5.2-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping data for table finance.account: ~11 rows (approximately)
DELETE FROM `account`;
INSERT INTO `account` (`id`, `bank`, `currency`, `iban`, `alias`, `description`, `enabled`, `created_at`) VALUES
	(1, 'Revolut', 'DKK', 'REVOREVOREVOREVOREVORDKK', 'DKK', NULL, 0, '2024-11-04 11:54:05.3365'),
	(2, 'Revolut', 'EUR', 'REVOREVOREVOREVOREVOREUR', '💶 EUR', NULL, 0, '2024-11-04 11:54:05.6779'),
	(3, 'Revolut', 'GBP', 'REVOREVOREVOREVOREVORGBP', 'GBP', NULL, 0, '2024-11-04 11:54:05.6818'),
	(4, 'Revolut', 'RON', 'REVOREVOREVOREVOREVORRON', 'RON', NULL, 0, '2024-11-04 11:54:05.6856'),
	(5, 'ING', 'USD', 'RO22INGB0000999902626887', '💵 Primar', NULL, 0, '2024-11-04 11:54:05.6893'),
	(6, 'ING', 'EUR', 'RO24INGB0000999905332084', '💶 Primar', NULL, 0, '2024-11-04 11:54:05.7008'),
	(7, 'ING', 'RON', 'RO64INGB5649999901181524', '🤑 Primar', NULL, 0, '2024-11-04 11:54:05.7139'),
	(8, 'Banca Transilvania', 'RON', 'RO72BTRL06301201H13496XX', 'Primar', NULL, 0, '2024-11-04 11:54:05.7247'),
	(9, 'ING', 'RON', 'RO87INGB0000999908493441', 'Secundar', NULL, 0, '2024-11-04 11:54:05.7283'),
	(10, 'ING', 'RON', 'RO95INGB0000999903001060', 'Economii', NULL, 0, '2024-11-04 11:54:05.7357'),
	(11, 'ING', 'RON', '----INGB----999915308019', 'Credit Nevoi Personale', NULL, 0, '2024-11-18 13:51:39.5116');

-- Dumping data for table finance.category: ~13 rows (approximately)
DELETE FROM `category`;
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

-- Dumping data for table finance.category_rule: ~45 rows (approximately)
DELETE FROM `category_rule`;
INSERT INTO `category_rule` (`id`, `account_id`, `category_id`, `sub_category_id`, `matches`, `debit`, `credit`, `enabled`, `position`, `name`) VALUES
	(1, NULL, 13, 40, '{"type":"TRANSFER"}|Transfer,Popescu,Opris,FARCAS,Munteanu Alexandru,Durnea,MUNTEANU DRAGOS,Miron Cristian,Pop Mihai,Bucurestean Ioana Andrada,Radulesc Anca Maria,Goaga Adela Smaranda,SAMFIRA CONSTANTIN,{"type":"TOPUP"}|Topup', NULL, 'amount > 0', 1, 0, 'Transferuri catre mine'),
	(2, NULL, 13, 36, '2SHIP SOLUTIONS INC|Salariu canada,Munteanu A. Alexandru|Salariu canada', NULL, NULL, 1, 1, 'Salariu Canada'),
	(3, NULL, 13, 35, 'SMARTVALUE SERVICES SRL', NULL, NULL, 1, 2, 'Salariu Timisoara'),
	(4, NULL, 8, 19, 'comisioane|Comisioane,Comision,Acoperire sold negativ neautorizat', NULL, NULL, 1, 3, 'Comisioane pe toate conturile'),
	(5, NULL, 6, 13, 'Retragere numerar,{"type":"ATM"}|Retragere numerar', NULL, NULL, 1, 4, 'Retrageri numerar'),
	(6, NULL, 4, 10, 'NN ASIGURARI,ING ASIGURARI', NULL, NULL, 1, 5, 'Asigurari pe toate conturile'),
	(7, NULL, 13, 37, 'Actualizare dobanda,Bonusuri', NULL, 'amount > 0', 1, 6, 'Dobanzi primite'),
	(8, 10, 13, 38, 'Tranzactie Round Up|Round Up,Ai economisit|Round Up', NULL, 'amount > 0', 1, 7, 'Round up intrari'),
	(9, 7, 4, 39, 'Tranzactie Round Up|Round Up,Ai economisit|Round Up', NULL, NULL, 1, 8, 'Round up iesiri'),
	(10, NULL, 10, 24, '{"type":"TRANSFER"}|Transfer,MUNTEANU DRAGOS|Dragos,Miron Cristian|Blacky,Pop Mihai,Bucurestean Ioana Andrada|Fina,Radulesc Anca Maria,Goaga Adela Smaranda,SAMFIRA CONSTANTIN|Junior,{"Transfer":""|Transfer,Rares Munteanu|Rares,Tudor Munteanu|Tudor,{"type":"TEMP_BLOCK"}|Transfer,MUNTEAN DRAGOS ALIN|Naganu,MUNTEANU Aida Isabela|Iza,Muntean Dragos|Naganu,Munteanu Rares|Rares,Munteanu Tudor|Tudor,Trif Dorin|Cretu,Trusca Dorel,Victor Prajitura,Cosmin- Sergiu Puscasu|Junior', 'amount > 0', NULL, 1, 9, 'Transferuri catre alte persoane'),
	(12, NULL, 13, 42, '{"type":"EXCHANGE"}|Exchange,Schimb valutar Home', NULL, 'amount > 0', 1, 11, 'Schimburi valutare, doar intrari'),
	(13, NULL, 7, 16, 'Steam,ITUNES.COM/BILL|Apple,CLAUS WEB|Hosting,Abonament BT 24,APPLE.COM/BILL|Apple,NETFLIX|Netflix,alfaweb.ro|Hosting,GODADDY|Domenii,SpotifyRO|Spotify', 'amount > 0', NULL, 1, 12, 'Servicii online'),
	(15, NULL, 3, 32, 'Lidl,Kaufland,DISTRI-HIPER|Auchan,COLUMBUS OPERATIONAL SRL|Carrefour,PROFI MAG|Profi,AUCHAN|Auchan,CARREFOUR ROMANIA|Carrefour,BILLA|Billa,CARREFOUR|Carrefour,AUC 0040|Auchan,ARTIMA|Artima,PROFI 2748|Profi M7,PENNY HUNED3|Penny M7,REWE ROMANIA|Penny,Mag 2748|Profi M7,SUPECO|Supeco,PENNY HUNED1|Penny,MEGAIMAGE|Megaimage', 'amount > 0', NULL, 1, 14, 'Supermarket'),
	(18, NULL, 10, 41, 'Schimb valutar Home|Exchange,Beneficiar: Alexandru Munteanu|Exchange,{"type":"EXCHANGE"}|Exchange', 'amount > 0', NULL, 1, 17, 'Schimburi valutare, doar iesiri'),
	(20, NULL, 12, 29, 'COSMOTE MOBILE|Cosmote,WWW.ORANGE.RO|Orange,TELEKOM|Telekom,ORANGE ROMANIA|Orange,VODAFONE|Vodafone', 'amount > 0', NULL, 1, 19, 'Cheltuieli telefon'),
	(21, 6, 1, 43, 'Cumparare POS,GOCAMPER|Rulota', 'amount > 0', NULL, 1, 20, 'Cheltuieli in afara tarii'),
	(23, NULL, 3, 45, 'PayU*eMAG.ro|eMAG,PayU*pcgarage|PC Garage,LIBRAPAY  RO  BUCURESTI|Librapay', 'amount > 0', NULL, 1, 21, 'Cheluieli online'),
	(24, NULL, 12, 28, 'WWW.MYLINE-EON|E-ON,ENEL ENERGIE SA|Enel,WWW.APAPROD.RO|Apa,SC APA PROD SA|Apa,ENEL ENERGIE|Enel,E.ON Energie|E-ON,EON GAZ|E-ON,WWW.EON.RO|E-ON,PPC ENERGIE S.A.|Enel', 'amount > 0', NULL, 1, 22, 'Cheltuieli cu casa'),
	(26, NULL, 3, 33, 'IQOS,IQOS KAUFLAND|IQOS,TABAC|IQOS,Mediapost Hit Mail SA|IQOS', 'amount > 0', NULL, 1, 0, 'IQOS'),
	(27, NULL, 3, 9, '{"type":"CARD_PAYMENT"}|Revolut card,{"type":"REV_PAYMENT"}|Revolut payment,1001 ARTICOLE  RO  HUNEDOARA', 'amount > 0', NULL, 1, 23, 'Shopping diverse'),
	(29, NULL, 11, 25, 'LUKOIL|Lukoil,MRC AUTOSTRADA A1|OMV,OMV|OMV,MOL |Mol,ROMPETROL|Rompetrol', 'amount > 0', NULL, 1, 25, 'Combustibil si taxe de drum'),
	(31, NULL, 3, 46, 'MEDIA GALAXY|Media Galaxy,NEXT COMPUTER DEP|Next Computer,ALTEX|Altex', 'amount > 0', NULL, 1, 27, 'Electronice'),
	(32, NULL, 3, 47, 'Farmacia|Farmacie,Farmacie|Farmacie,SENSIBLU|Farmacie,Catena|Farmacie,DIANTHUS PHARMA|Farmacie,VILEUS|Farmacie,HELP NET|Farmacie,TEA FARMEX|Farmacie,VITAMIX|Farmacie,SF. FRANCISC|Farmacie,DONA|Farmacie', 'amount > 0', NULL, 1, 28, 'Farmacii'),
	(33, NULL, 3, 48, 'SC FINELOR PRESTCOM|Finelor,SERGIANA PRODIMPEX|Sergiana,LS TRAVEL RETAIL|Inmedio,CENTRUL DE PRINT|Centrul de print,MAGAZIN O.M. DEP  RO  HUNEDOARA|ABC,PEPCO|Pepco,CONTAKT DEVA|Huse si accesorii telefoane,FINELOR PRESTCOM|Abc,VALEMO|Abc,Melissa Expres|Abc,TINALOR FRUCT|Abc,GRAPECO MIXT|Abc,IOAN ROXI DUNCA|Abc,COMPLEX POTCOAVA|Abc,MAGAZIN PESTISU MIC|Abc', 'amount > 0', NULL, 1, 29, 'ABC-uri'),
	(35, NULL, 12, 30, 'digicare.rcs-rds.ro|Digi,RCS AND RDS|Digi,RCS RDS S.A.|Digi,RCS & RDS SA|Digi,DIGI_ROMANIA_SA|Digi', 'amount > 0', NULL, 1, 22, 'Utilitati internet'),
	(36, NULL, 3, 5, 'DEDEMAN|Dedeman,IKEA ROMANIA|IKEA,PRAKTIKER|Praktiker,BRICOSTORE|Bricostore,LEROY MERLIN|Leroy Merlin,JYSK|Jysk,VIDI PROD|Bricolaj,HORNBACH|Hornbach', 'amount > 0', NULL, 1, 32, 'Bricolaj'),
	(37, NULL, 13, 44, 'Cumparare POS corectie|Corectie', NULL, 'amount > 0', 1, 32, 'Corectii'),
	(38, NULL, 9, 23, 'MIDI DEVELOPMENT SRL|Beraria H,SUMMER BREEZE|Restaurant,ROPRESSO CAFE|Cafenea,4 COLTURI|Pizza', 'amount > 0', NULL, 1, 33, 'Baruri si restaurante'),
	(39, NULL, 3, 8, 'ROUMASPORT SRL|Decathlon', 'amount > 0', NULL, 1, 34, 'Articole sportive'),
	(40, NULL, 3, 6, 'NORIELTOYS|Noriel,JUMBO|Jumbo,NORIEL|Noriel,SMYTHS TOYS|Toys,SMYK ALL FOR KIDS|Smyk,SMYK|Jucarii', NULL, NULL, 1, 34, 'Jucarii'),
	(41, NULL, 3, 7, 'ALISS SHOES|Aliss,C&A|C & A,BERSHKA|Bershka,C & A|C & A,CCC|CCC,H&M|H&M,Deichmann|Deichmann,VALDAX|Marcela,TAKKO|Takko,NEW YORKER|New Yorker,MISS FASHION|Fashion,Decathlon|Decathlon,HUMANA|Secondhand,HERVIS|Hervis,KiK|Kik,ZEYA|Zeya,BENVENUTI|Benvenuti,BELLE BIJOU|BelleBijou,SINSAY|Sinsay,RAY GLOBUS SRL|Second Hand', NULL, NULL, 1, 35, 'Imbracaminte si papuci'),
	(42, NULL, 3, 49, 'Glovo,IRINA COMSERVICE|Bulbucan,COMPOTRANS| Macelarie,NICOLETTE|Macelarie,MACELARIE|Macelarie,AXA OMEGA|Patiserie', 'amount > 0', NULL, 1, 36, 'Mancare'),
	(43, NULL, 13, 50, 'CRM SOFTWARE SRL|Salariu CRM', NULL, 'amount > 0', 1, 37, 'Salariu CRM'),
	(45, NULL, 13, 51, 'Alocare fonduri credit', NULL, 'amount > 0', 1, 33, 'Intrare credit in cont secundar'),
	(46, NULL, 5, 12, 'Alocare fonduri credit', 'amount > 0', NULL, 1, 34, 'Alocare fonduri credit'),
	(47, NULL, 7, 17, 'Cord Blood|Celule stem,RADIOLOGIE DENTARA|Radiologie', 'amount > 0', NULL, 1, 35, 'Chelutiel pe servicii medicale'),
	(48, NULL, 1, 3, 'WIZZ AIR|Wizz,GATE RETAIL|Cheltuieli aeroport,RYANAIR|Ryanair,AEROP. INT. TIMISOARA|Cheltuieli aeroport,AEROPORTO|Cheltuieli aeroport,Airport  GB  Luton|Cheltuieli aeroport', 'amount > 0', NULL, 1, 35, 'Zboruri'),
	(49, NULL, 3, 4, 'ALMAVET|Veterinar,ANDIVET|Veterinar,CRIS GLOBAL TRADING|Hrana animale,VABRO RETAIL|Hrana animale,ANIMED|Veterinar,ANIMAX|Hrana animale,GAT WOOD|Hrana animale,VARSANYI CARMEN|Hrana animale', 'amount > 0', NULL, 1, 36, 'Animale'),
	(50, NULL, 9, 52, 'STEAM|Steam,CDKEYS|Cd Keys,MINECRAFT|Minecraft,ROBLOX|Roblox,XSOLLA|Jocuri', 'amount > 0', NULL, 1, 37, 'Jocuri online'),
	(51, NULL, 11, 53, 'HELP.UBER.COM|Uber', 'amount > 0', NULL, 1, 38, 'Taxiuri si uber'),
	(52, NULL, 9, 22, 'FOR PRINT|Rechizite,ALAMOS|Rechizite,DIVERTA|Rechizite,MPY|Admitere liceu', 'amount > 0', NULL, 1, 39, 'Rechizite'),
	(53, NULL, 9, 54, 'MCDONALDS|McDonalds,KFC|Kfc,PREMIER RESTAURANTS|Spartan,SPARTAN|Spartan,LAGARDERE TRAVEL|Fast food,GRUP EUROPAN PROD|Fast food,PATRIK PIZZA|Fast food', 'amount > 0', NULL, 1, 40, 'Fast Food'),
	(54, NULL, 9, 55, 'ARENA NATIONALA|Concert,IA BILET SRL  RO  BUCURESTI,TICKETPRO|Concert', 'amount > 0', NULL, 1, 41, 'Teatru si concerte'),
	(55, NULL, 9, 56, 'CLUB RED MOTOR|Karturi,RED MOTOR|Karturi', 'amount > 0', NULL, 1, 42, 'Karting'),
	(56, NULL, 11, 57, 'MATEROM|Polo,TESS SIB|VW', 'amount > 0', NULL, 1, 43, 'Costuri cu masina');

-- Dumping data for table finance.sub_category: ~57 rows (approximately)
DELETE FROM `sub_category`;
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
	(51, 13, 'Loans'),
	(52, 9, 'Gaming'),
	(53, 11, 'Uber and taxi'),
	(54, 9, 'Fast Food'),
	(55, 9, 'Entertainment'),
	(56, 9, 'Karting'),
	(57, 11, 'Car costs');

-- Dumping data for table finance.user: ~3 rows (approximately)
DELETE FROM `user`;
INSERT INTO `user` (`id`, `email`, `roles`, `password`, `enabled`, `first_name`, `last_name`, `avatar`, `created_at`, `updated_at`) VALUES
	(1, 'importer.user@example.com', '[]', '$2y$13$HU3qnc1V9VBCM8nQI7IxNOAM/BmXyxpxp5jsmIb1hYlJ4E//60y/q', 1, 'Importer', 'User', NULL, '2024-11-01 12:20:08.0000', '2024-11-21 09:05:54.0000'),
	(2, 'munteanucalexandru@gmail.com', '["ROLE_USER","ROLE_SUPER_ADMIN"]', '$2y$13$GEmTBAmyNzL7Xc3JRDaJUOcQfFPhUUT9v.lSN9yvINLg6Yd9FDHfC', 1, 'Alex', 'M', 'unnamed-3-1732179900.jpg', '2024-11-01 12:20:09.0000', '2024-11-21 09:05:01.0000'),
	(3, 'munteanuaida2@gmail.com', '["ROLE_USER","ROLE_SUPER_ADMIN"]', '$2y$13$GEmTBAmyNzL7Xc3JRDaJUOcQfFPhUUT9v.lSN9yvINLg6Yd9FDHfC', 1, 'Iza', 'M', 'unnamed-4-1732180516.jpg', '2024-11-21 11:14:03.0000', '2024-11-21 09:15:19.0000');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
