<?php

namespace App\EventListener;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsEventListener]
final class InactiveUserLogoutListener
{
    public function __construct(
        private Security $security,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    public function __invoke(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $participant = $this->security->getUser();

        // dd($participant);

        if (!$participant) {
            return;
        }

        if ($participant->isActif() === false) {
            $this->security->logout(false);

            $request = $event->getRequest();
            $request->getSession()->getFlashBag()->add('inactifCompte', 'Votre compte a été désactivé.');

            $response = new RedirectResponse(
                $this->urlGenerator->generate('app_login')
            );

            $event->setResponse($response);
        }
    }
}
