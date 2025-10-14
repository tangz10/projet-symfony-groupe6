<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration neutralisée: les tables/colonnes visées sont déjà créées par Version20251014120829.
 * On laisse un “no-op” pour conserver l'ID de migration sans casser l'ordre.
 */
final class Version20251014122345 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'No-op: les tables note/reset_password_request et colonnes participant ont été créées par une migration précédente.';
    }

    public function up(Schema $schema): void
    {
        // no-op: déjà géré par Version20251014120829
    }

    public function down(Schema $schema): void
    {
        // no-op: rien à annuler car cette migration ne fait plus d'altérations
    }
}
