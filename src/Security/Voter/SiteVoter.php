<?php

namespace App\Security\Voter;

use App\Entity\Participant;
use App\Entity\Site;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter pour l'entité Site.
 * Politique: seuls les administrateurs peuvent gérer et consulter les sites.
 * Les messages utilisateurs et redirections sont gérés par AccessDeniedSubscriber.
 */
class SiteVoter extends Voter
{
    public const LIST = 'SITE_LIST';
    public const VIEW = 'SITE_VIEW';
    public const CREATE = 'SITE_CREATE';
    public const EDIT = 'SITE_EDIT';
    public const DELETE = 'SITE_DELETE';

    public function __construct(private readonly Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::LIST, self::VIEW, self::CREATE, self::EDIT, self::DELETE], true)
            && (
                in_array($attribute, [self::LIST, self::CREATE], true)
                    ? ($subject === null || $subject instanceof Site)
                    : ($subject instanceof Site)
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

