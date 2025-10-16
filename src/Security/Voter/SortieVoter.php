<?php

namespace App\Security\Voter;

use App\Entity\Participant;
use App\Entity\Sortie;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SortieVoter extends Voter
{
    public const REGISTER = 'SORTIE_REGISTER';
    public const UNREGISTER = 'SORTIE_UNREGISTER';
    public const EDIT = 'SORTIE_EDIT';
    public const CANCEL = 'SORTIE_CANCEL';
    public const VIEW = 'SORTIE_VIEW';

    protected function supports(string $attribute, $subject): bool
    {
        if (!\in_array($attribute, [self::REGISTER, self::UNREGISTER, self::EDIT, self::CANCEL, self::VIEW], true)) {
            return false;
        }
        return null === $subject || $subject instanceof Sortie;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if ($attribute === self::REGISTER || $attribute === self::UNREGISTER) {
            return $user instanceof Participant;
        }

        if ($attribute === self::EDIT || $attribute === self::CANCEL) {
            if (!$user instanceof Participant) {
                return false; // anonyme ou type inattendu
            }
            // Admin autorisé
            if (\in_array('ROLE_ADMIN', $user->getRoles(), true)) {
                return true;
            }
            // Organisateur autorisé
            if ($subject instanceof Sortie) {
                $org = $subject->getParticipantOrganisateur();
                if ($org && $org->getId() === $user->getId()) {
                    return true;
                }
            }
            return false;
        }

        // Les autres attributs ne sont pas encore contraints par le voter
        return true;
    }
}
