<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251006135757 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE participant ADD email VARCHAR(180) NOT NULL, ADD roles JSON NOT NULL, ADD password VARCHAR(255) NOT NULL, ADD date_heure_debut DATE NOT NULL, ADD duree INT NOT NULL, ADD date_limite_inscription DATE NOT NULL, ADD nb_inscription_max INT NOT NULL, ADD infos_sortie VARCHAR(255) NOT NULL, ADD etat VARCHAR(255) NOT NULL, DROP prenom, DROP telephone, DROP mail, DROP administrateur, DROP actif');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON participant (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_IDENTIFIER_EMAIL ON participant');
        $this->addSql('ALTER TABLE participant ADD prenom VARCHAR(255) NOT NULL, ADD telephone VARCHAR(255) NOT NULL, ADD mail VARCHAR(255) NOT NULL, ADD administrateur TINYINT(1) NOT NULL, ADD actif TINYINT(1) NOT NULL, DROP email, DROP roles, DROP password, DROP date_heure_debut, DROP duree, DROP date_limite_inscription, DROP nb_inscription_max, DROP infos_sortie, DROP etat');
    }
}
