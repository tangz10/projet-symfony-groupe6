<?php

namespace App\Security\Voter;

use App\Entity\Participant;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ParticipantVoter extends Voter
{
    public const VIEW = 'PARTICIPANT_VIEW';
    public const EDIT = 'PARTICIPANT_EDIT';
    public const CHANGE_PASSWORD = 'PARTICIPANT_CHANGE_PASSWORD';
    public const DELETE = 'PARTICIPANT_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, [self::VIEW, self::EDIT, self::CHANGE_PASSWORD, self::DELETE], true)
            && $subject instanceof Participant;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof Participant) {
            return false; // anonyme ou type inattendu
        }

        // Admin: tout permis
        if (\in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        // Règle "soi-même"
        $isSelf = $user->getId() === $subject->getId();

        return match ($attribute) {
            self::VIEW, self::EDIT, self::CHANGE_PASSWORD => $isSelf,
            self::DELETE => false, // réservé admin uniquement
            default => false,
        };
    }
}

