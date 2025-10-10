<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

#[AsEventListener(event: CheckPassportEvent::class)]
final class CheckUserEnabledListener
{
    public function __invoke(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();
        $user = $passport->getUser();

        if (method_exists($user, 'isActif') && !$user->isActif()) {
            throw new CustomUserMessageAuthenticationException(
                'Votre compte a été désactivé. Veuillez contacter un administrateur.'
            );
        }
    }
}
