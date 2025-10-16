<?php

namespace App\Security\Voter;

use App\Entity\Lieu;
use App\Entity\Participant;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter pour l'entité Lieu.
 * Politique: tous les utilisateurs authentifiés peuvent gérer les lieux.
 * Les villes et sites nécessitent ROLE_ADMIN (géré séparément dans AccessDeniedSubscriber).
 */
class LieuVoter extends Voter
{
    public const LIST = 'LIEU_LIST';
    public const VIEW = 'LIEU_VIEW';
    public const CREATE = 'LIEU_CREATE';
    public const EDIT = 'LIEU_EDIT';
    public const DELETE = 'LIEU_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::LIST, self::VIEW, self::CREATE, self::EDIT, self::DELETE], true)
            && (
            in_array($attribute, [self::LIST, self::CREATE], true)
                ? ($subject === null || $subject instanceof Lieu)
                : ($subject instanceof Lieu)
            );
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // Seuls les utilisateurs authentifiés peuvent gérer les lieux
        return $user instanceof Participant;
    }
}
