<?php

namespace App\Security\Voter;

use App\Entity\Participant;
use App\Entity\Ville;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter pour l'entité Ville.
 * Politique: seuls les administrateurs peuvent gérer et consulter les villes.
 * Les messages utilisateurs et redirections sont gérés par AccessDeniedSubscriber.
 */
class VilleVoter extends Voter
{
    public const LIST = 'VILLE_LIST';
    public const VIEW = 'VILLE_VIEW';
    public const CREATE = 'VILLE_CREATE';
    public const EDIT = 'VILLE_EDIT';
    public const DELETE = 'VILLE_DELETE';

    public function __construct(private readonly Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::LIST, self::VIEW, self::CREATE, self::EDIT, self::DELETE], true)
            && (
                in_array($attribute, [self::LIST, self::CREATE], true)
                    ? ($subject === null || $subject instanceof Ville)
                    : ($subject instanceof Ville)
            );
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof Participant) {
            return false;
        }

        // Toutes les actions (consultation, création, édition, suppression) : admin uniquement
        return $this->security->isGranted('ROLE_ADMIN');
    }
}
