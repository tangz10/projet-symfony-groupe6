<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251007140301 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sortie_participant (sortie_id INT NOT NULL, participant_id INT NOT NULL, INDEX IDX_E6D4CDADCC72D953 (sortie_id), INDEX IDX_E6D4CDAD9D1C3019 (participant_id), PRIMARY KEY(sortie_id, participant_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sortie_participant ADD CONSTRAINT FK_E6D4CDADCC72D953 FOREIGN KEY (sortie_id) REFERENCES sortie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sortie_participant ADD CONSTRAINT FK_E6D4CDAD9D1C3019 FOREIGN KEY (participant_id) REFERENCES participant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sortie_participantinscrit DROP FOREIGN KEY FK_Participant_Sortie');
        $this->addSql('ALTER TABLE sortie_participantinscrit DROP FOREIGN KEY FK_Sortie_Participant');
        $this->addSql('DROP TABLE sortie_participantinscrit');
        $this->addSql('ALTER TABLE lieu DROP FOREIGN KEY FK_Lieu_Ville');
        $this->addSql('ALTER TABLE lieu ADD CONSTRAINT FK_2F577D59A73F0036 FOREIGN KEY (ville_id) REFERENCES ville (id)');
        $this->addSql('ALTER TABLE lieu RENAME INDEX fk_lieu_ville TO IDX_2F577D59A73F0036');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_Participant_Site');
        $this->addSql('ALTER TABLE participant CHANGE administrateur administrateur TINYINT(1) NOT NULL, CHANGE actif actif TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B11F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('ALTER TABLE participant RENAME INDEX fk_participant_site TO IDX_D79F6B11F6BD1646');
        $this->addSql('ALTER TABLE participant RENAME INDEX email TO UNIQ_IDENTIFIER_EMAIL');
        $this->addSql('ALTER TABLE sortie DROP FOREIGN KEY FK_sortie_etat');
        $this->addSql('ALTER TABLE sortie DROP FOREIGN KEY FK_sortie_lieu');
        $this->addSql('ALTER TABLE sortie DROP FOREIGN KEY FK_sortie_organisateur');
        $this->addSql('ALTER TABLE sortie DROP FOREIGN KEY FK_sortie_site');
        $this->addSql('ALTER TABLE sortie ADD CONSTRAINT FK_3C3FD3F2F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('ALTER TABLE sortie ADD CONSTRAINT FK_3C3FD3F2D5E86FF FOREIGN KEY (etat_id) REFERENCES etat (id)');
        $this->addSql('ALTER TABLE sortie ADD CONSTRAINT FK_3C3FD3F217AE6A42 FOREIGN KEY (participant_organisateur_id) REFERENCES participant (id)');
        $this->addSql('ALTER TABLE sortie ADD CONSTRAINT FK_3C3FD3F26AB213CC FOREIGN KEY (lieu_id) REFERENCES lieu (id)');
        $this->addSql('ALTER TABLE sortie RENAME INDEX idx_sortie_site TO IDX_3C3FD3F2F6BD1646');
        $this->addSql('ALTER TABLE sortie RENAME INDEX idx_sortie_etat TO IDX_3C3FD3F2D5E86FF');
        $this->addSql('ALTER TABLE sortie RENAME INDEX idx_sortie_org TO IDX_3C3FD3F217AE6A42');
        $this->addSql('ALTER TABLE sortie RENAME INDEX idx_sortie_lieu TO IDX_3C3FD3F26AB213CC');
        $this->addSql('ALTER TABLE ville CHANGE codePostal code_postal VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sortie_participantinscrit (sortie_id INT NOT NULL, participant_id INT NOT NULL, INDEX FK_Participant_Sortie (participant_id), INDEX IDX_9614DBDCCC72D953 (sortie_id), PRIMARY KEY(sortie_id, participant_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE sortie_participantinscrit ADD CONSTRAINT FK_Participant_Sortie FOREIGN KEY (participant_id) REFERENCES participant (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sortie_participantinscrit ADD CONSTRAINT FK_Sortie_Participant FOREIGN KEY (sortie_id) REFERENCES sortie (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sortie_participant DROP FOREIGN KEY FK_E6D4CDADCC72D953');
        $this->addSql('ALTER TABLE sortie_participant DROP FOREIGN KEY FK_E6D4CDAD9D1C3019');
        $this->addSql('DROP TABLE sortie_participant');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE lieu DROP FOREIGN KEY FK_2F577D59A73F0036');
        $this->addSql('ALTER TABLE lieu ADD CONSTRAINT FK_Lieu_Ville FOREIGN KEY (ville_id) REFERENCES ville (id) ON UPDATE CASCADE ON DELETE SET NULL');
        $this->addSql('ALTER TABLE lieu RENAME INDEX idx_2f577d59a73f0036 TO FK_Lieu_Ville');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B11F6BD1646');
        $this->addSql('ALTER TABLE participant CHANGE administrateur administrateur TINYINT(1) DEFAULT 0 NOT NULL, CHANGE actif actif TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_Participant_Site FOREIGN KEY (Site_id) REFERENCES site (id) ON UPDATE CASCADE ON DELETE SET NULL');
        $this->addSql('ALTER TABLE participant RENAME INDEX uniq_identifier_email TO email');
        $this->addSql('ALTER TABLE participant RENAME INDEX idx_d79f6b11f6bd1646 TO FK_Participant_Site');
        $this->addSql('ALTER TABLE ville CHANGE code_postal codePostal VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE sortie DROP FOREIGN KEY FK_3C3FD3F2F6BD1646');
        $this->addSql('ALTER TABLE sortie DROP FOREIGN KEY FK_3C3FD3F2D5E86FF');
        $this->addSql('ALTER TABLE sortie DROP FOREIGN KEY FK_3C3FD3F217AE6A42');
        $this->addSql('ALTER TABLE sortie DROP FOREIGN KEY FK_3C3FD3F26AB213CC');
        $this->addSql('ALTER TABLE sortie ADD CONSTRAINT FK_sortie_etat FOREIGN KEY (etat_id) REFERENCES etat (id) ON UPDATE CASCADE ON DELETE SET NULL');
        $this->addSql('ALTER TABLE sortie ADD CONSTRAINT FK_sortie_lieu FOREIGN KEY (lieu_id) REFERENCES lieu (id) ON UPDATE CASCADE ON DELETE SET NULL');
        $this->addSql('ALTER TABLE sortie ADD CONSTRAINT FK_sortie_organisateur FOREIGN KEY (participant_organisateur_id) REFERENCES participant (id) ON UPDATE CASCADE ON DELETE SET NULL');
        $this->addSql('ALTER TABLE sortie ADD CONSTRAINT FK_sortie_site FOREIGN KEY (site_id) REFERENCES site (id) ON UPDATE CASCADE ON DELETE SET NULL');
        $this->addSql('ALTER TABLE sortie RENAME INDEX idx_3c3fd3f2f6bd1646 TO IDX_sortie_site');
        $this->addSql('ALTER TABLE sortie RENAME INDEX idx_3c3fd3f2d5e86ff TO IDX_sortie_etat');
        $this->addSql('ALTER TABLE sortie RENAME INDEX idx_3c3fd3f217ae6a42 TO IDX_sortie_org');
        $this->addSql('ALTER TABLE sortie RENAME INDEX idx_3c3fd3f26ab213cc TO IDX_sortie_lieu');
    }
}
