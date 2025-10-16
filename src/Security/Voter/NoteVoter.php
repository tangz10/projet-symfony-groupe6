<?php

namespace App\Security\Voter;

use App\Entity\Participant;
use App\Entity\Sortie;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class NoteVoter extends Voter
{
    public const RATE = 'NOTE_RATE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::RATE && $subject instanceof Sortie;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof Participant) {
            return false; // anonyme ou type inattendu
        }

        if (!$subject instanceof Sortie) {
            return false;
        }

        $etat = $subject->getEtat()?->getLibelle();
        $isTerminee = in_array($etat, ['Passée', 'Terminée'], true);
        if (!$isTerminee) {
            return false;
        }

        $isInscrit = $subject->getParticipantInscrit()->exists(fn($k, $p) => $p->getId() === $user->getId());
        if (!$isInscrit) {
            return false;
        }

        return true;
    }
}
