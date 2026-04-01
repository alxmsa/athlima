<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260401141817 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE categorie (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(80) NOT NULL, description LONGTEXT DEFAULT NULL, couleur_hex VARCHAR(7) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE exercice (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, type_effort VARCHAR(20) NOT NULL, equipement VARCHAR(100) DEFAULT NULL, image_url VARCHAR(255) DEFAULT NULL, est_public TINYINT NOT NULL, created_at DATETIME NOT NULL, categorie_id INT NOT NULL, INDEX IDX_E418C74DBCF5E72D (categorie_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE exercice_muscle (exercice_id INT NOT NULL, muscle_id INT NOT NULL, INDEX IDX_2A9ECEF589D40298 (exercice_id), INDEX IDX_2A9ECEF5354FDBB4 (muscle_id), PRIMARY KEY (exercice_id, muscle_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE exercice_seance (id INT AUTO_INCREMENT NOT NULL, ordre SMALLINT NOT NULL, nb_reps_cible SMALLINT NOT NULL, poids_cible_kg NUMERIC(6, 2) DEFAULT NULL, temps_repos_sec SMALLINT NOT NULL, note LONGTEXT DEFAULT NULL, seance_id INT NOT NULL, exercice_id INT NOT NULL, INDEX IDX_6F22A14E3797A94 (seance_id), INDEX IDX_6F22A1489D40298 (exercice_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE muscle (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(80) NOT NULL, groupe_musculaire VARCHAR(80) NOT NULL, est_principal TINYINT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE objectif (id INT AUTO_INCREMENT NOT NULL, type_objectif VARCHAR(20) NOT NULL, valeur_cible NUMERIC(7, 2) DEFAULT NULL, date_echeance DATE DEFAULT NULL, est_atteint TINYINT NOT NULL, created_at DATETIME NOT NULL, utilisateur_id INT NOT NULL, UNIQUE INDEX UNIQ_E2F86851FB88E14F (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE seance (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(150) NOT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME DEFAULT NULL, duree_min SMALLINT DEFAULT NULL, statut VARCHAR(20) NOT NULL, note LONGTEXT DEFAULT NULL, utilisateur_id INT NOT NULL, INDEX IDX_DF7DFD0EFB88E14F (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE serie (id INT AUTO_INCREMENT NOT NULL, num_serie SMALLINT NOT NULL, nb_reps_realisees SMALLINT NOT NULL, poids_kg NUMERIC(6, 2) NOT NULL, duree_sec SMALLINT DEFAULT NULL, est_pr TINYINT NOT NULL, created_at DATETIME NOT NULL, exercice_seance_id INT NOT NULL, INDEX IDX_AA3A933441B69182 (exercice_seance_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, mot_de_passe VARCHAR(255) NOT NULL, prenom VARCHAR(80) NOT NULL, nom VARCHAR(80) NOT NULL, date_naissance DATE DEFAULT NULL, poids_kg NUMERIC(5, 2) DEFAULT NULL, taille_cm NUMERIC(5, 1) DEFAULT NULL, niveau VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_1D1C63B3E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE exercice ADD CONSTRAINT FK_E418C74DBCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id)');
        $this->addSql('ALTER TABLE exercice_muscle ADD CONSTRAINT FK_2A9ECEF589D40298 FOREIGN KEY (exercice_id) REFERENCES exercice (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE exercice_muscle ADD CONSTRAINT FK_2A9ECEF5354FDBB4 FOREIGN KEY (muscle_id) REFERENCES muscle (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE exercice_seance ADD CONSTRAINT FK_6F22A14E3797A94 FOREIGN KEY (seance_id) REFERENCES seance (id)');
        $this->addSql('ALTER TABLE exercice_seance ADD CONSTRAINT FK_6F22A1489D40298 FOREIGN KEY (exercice_id) REFERENCES exercice (id)');
        $this->addSql('ALTER TABLE objectif ADD CONSTRAINT FK_E2F86851FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE seance ADD CONSTRAINT FK_DF7DFD0EFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE serie ADD CONSTRAINT FK_AA3A933441B69182 FOREIGN KEY (exercice_seance_id) REFERENCES exercice_seance (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE exercice DROP FOREIGN KEY FK_E418C74DBCF5E72D');
        $this->addSql('ALTER TABLE exercice_muscle DROP FOREIGN KEY FK_2A9ECEF589D40298');
        $this->addSql('ALTER TABLE exercice_muscle DROP FOREIGN KEY FK_2A9ECEF5354FDBB4');
        $this->addSql('ALTER TABLE exercice_seance DROP FOREIGN KEY FK_6F22A14E3797A94');
        $this->addSql('ALTER TABLE exercice_seance DROP FOREIGN KEY FK_6F22A1489D40298');
        $this->addSql('ALTER TABLE objectif DROP FOREIGN KEY FK_E2F86851FB88E14F');
        $this->addSql('ALTER TABLE seance DROP FOREIGN KEY FK_DF7DFD0EFB88E14F');
        $this->addSql('ALTER TABLE serie DROP FOREIGN KEY FK_AA3A933441B69182');
        $this->addSql('DROP TABLE categorie');
        $this->addSql('DROP TABLE exercice');
        $this->addSql('DROP TABLE exercice_muscle');
        $this->addSql('DROP TABLE exercice_seance');
        $this->addSql('DROP TABLE muscle');
        $this->addSql('DROP TABLE objectif');
        $this->addSql('DROP TABLE seance');
        $this->addSql('DROP TABLE serie');
        $this->addSql('DROP TABLE utilisateur');
    }
}
