<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240222201632 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE marque (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(60) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE personne (id INT AUTO_INCREMENT NOT NULL, voiture_id INT DEFAULT NULL, ville_id INT DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, nom VARCHAR(60) DEFAULT NULL, prenom VARCHAR(60) DEFAULT NULL, tel VARCHAR(12) DEFAULT NULL, pseudo VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_FCEC9EFE7927C74 (email), UNIQUE INDEX UNIQ_FCEC9EF86CC499D (pseudo), INDEX IDX_FCEC9EF181A8BA (voiture_id), INDEX IDX_FCEC9EFA73F0036 (ville_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE trajet (id INT AUTO_INCREMENT NOT NULL, depart_ville_id INT NOT NULL, arriver_ville_id INT NOT NULL, conducteur_id INT NOT NULL, date_depart DATETIME NOT NULL, kms INT DEFAULT NULL, places_disponible INT DEFAULT NULL, statut VARCHAR(255) NOT NULL, INDEX IDX_2B5BA98C28F601E8 (depart_ville_id), INDEX IDX_2B5BA98C20918113 (arriver_ville_id), INDEX IDX_2B5BA98CF16F4AC6 (conducteur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE trajet_personne (trajet_id INT NOT NULL, personne_id INT NOT NULL, INDEX IDX_58D4CBCBD12A823 (trajet_id), INDEX IDX_58D4CBCBA21BD112 (personne_id), PRIMARY KEY(trajet_id, personne_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ville (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, code_postal VARCHAR(5) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE voiture (id INT AUTO_INCREMENT NOT NULL, marque_id INT DEFAULT NULL, modele VARCHAR(60) DEFAULT NULL, place INT DEFAULT NULL, immatriculation VARCHAR(12) NOT NULL, UNIQUE INDEX UNIQ_E9E2810FBE73422E (immatriculation), INDEX IDX_E9E2810F4827B9B2 (marque_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE personne ADD CONSTRAINT FK_FCEC9EF181A8BA FOREIGN KEY (voiture_id) REFERENCES voiture (id)');
        $this->addSql('ALTER TABLE personne ADD CONSTRAINT FK_FCEC9EFA73F0036 FOREIGN KEY (ville_id) REFERENCES ville (id)');
        $this->addSql('ALTER TABLE trajet ADD CONSTRAINT FK_2B5BA98C28F601E8 FOREIGN KEY (depart_ville_id) REFERENCES ville (id)');
        $this->addSql('ALTER TABLE trajet ADD CONSTRAINT FK_2B5BA98C20918113 FOREIGN KEY (arriver_ville_id) REFERENCES ville (id)');
        $this->addSql('ALTER TABLE trajet ADD CONSTRAINT FK_2B5BA98CF16F4AC6 FOREIGN KEY (conducteur_id) REFERENCES personne (id)');
        $this->addSql('ALTER TABLE trajet_personne ADD CONSTRAINT FK_58D4CBCBD12A823 FOREIGN KEY (trajet_id) REFERENCES trajet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE trajet_personne ADD CONSTRAINT FK_58D4CBCBA21BD112 FOREIGN KEY (personne_id) REFERENCES personne (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE voiture ADD CONSTRAINT FK_E9E2810F4827B9B2 FOREIGN KEY (marque_id) REFERENCES marque (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE personne DROP FOREIGN KEY FK_FCEC9EF181A8BA');
        $this->addSql('ALTER TABLE personne DROP FOREIGN KEY FK_FCEC9EFA73F0036');
        $this->addSql('ALTER TABLE trajet DROP FOREIGN KEY FK_2B5BA98C28F601E8');
        $this->addSql('ALTER TABLE trajet DROP FOREIGN KEY FK_2B5BA98C20918113');
        $this->addSql('ALTER TABLE trajet DROP FOREIGN KEY FK_2B5BA98CF16F4AC6');
        $this->addSql('ALTER TABLE trajet_personne DROP FOREIGN KEY FK_58D4CBCBD12A823');
        $this->addSql('ALTER TABLE trajet_personne DROP FOREIGN KEY FK_58D4CBCBA21BD112');
        $this->addSql('ALTER TABLE voiture DROP FOREIGN KEY FK_E9E2810F4827B9B2');
        $this->addSql('DROP TABLE marque');
        $this->addSql('DROP TABLE personne');
        $this->addSql('DROP TABLE trajet');
        $this->addSql('DROP TABLE trajet_personne');
        $this->addSql('DROP TABLE ville');
        $this->addSql('DROP TABLE voiture');
    }
}
