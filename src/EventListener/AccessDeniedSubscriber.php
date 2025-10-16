<?php

namespace App\EventListener;

use App\Entity\Participant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\SecurityBundle\Security;

class AccessDeniedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RouterInterface $router,
        private Security $security,
        private EntityManagerInterface $em,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            // Priorité élevée pour devancer les handlers par défaut
            KernelEvents::EXCEPTION => ['onKernelException', 128],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        // Ne traiter que la requête principale
        if (method_exists($event, 'isMainRequest') && !$event->isMainRequest()) {
            return;
        }

        $throwable = $event->getThrowable();
        if (!$throwable instanceof AccessDeniedException) {
            return;
        }

        $request = $event->getRequest();
        $session = $request->getSession();
        $route = (string)$request->attributes->get('_route');
        $user = $this->security->getUser();

        // Non authentifié → login + message
        if (!$user instanceof \App\Entity\Participant) {
            $session?->getFlashBag()->add('error', 'Veuillez vous connecter pour accéder à cette page.');
            $event->setResponse(new RedirectResponse($this->router->generate('app_login')));
            return;
        }

        // Par défaut
        $message = "Vous n'avez pas l'autorisation requise pour effectuer cette action.";
        $redirectUrl = $this->router->generate('app_index');

        // Participant: rediriger vers son propre profil
        if (str_starts_with($route, 'app_participant_')) {
            $message = "Vous ne pouvez pas consulter le profil d'un autre membre.";
            $redirectUrl = $this->router->generate('app_participant_show', ['id' => $user->getId()]);
        }
        // Sortie: messages dédiés + tentative de retour sur la page de la sortie si id présent
        elseif (str_starts_with($route, 'app_sortie_')) {
            $id = $request->attributes->get('id');
            $targetShow = $id ? $this->router->generate('app_sortie_show', ['id' => $id]) : $this->router->generate('app_sortie_index');

            if ($route === 'app_sortie_edit') {
                $message = "Seul l'organisateur de la sortie ou un administrateur peux éditer une sortie";
                $redirectUrl = $targetShow;
            } elseif ($route === 'app_sortie_annuler') {
                $message = "Seul l'organisateur de la sortie ou un administrateur peut annuler une sortie.";
                $redirectUrl = $targetShow;
            } elseif ($route === 'app_sortie_publier') {
                $message = "Vous ne pouvez pas publier cette sortie.";
                $redirectUrl = $targetShow;
            } elseif ($route === 'app_sortie_inscrire') {
                $message = "Il faut être connecté en tant que participant pour s'inscrire à une sortie.";
                $redirectUrl = $targetShow;
            } elseif ($route === 'app_sortie_desister') {
                $message = "Il faut être connecté en tant que participant pour se désister d'une sortie.";
                $redirectUrl = $targetShow;
            } elseif ($route === 'app_sortie_noter') {
                // Messages d'origine selon la cause: sortie non terminée ou non inscrit
                if ($id) {
                    $sortie = $this->em->getRepository(\App\Entity\Sortie::class)->find($id);
                    if ($sortie) {
                        $etat = $sortie->getEtat()?->getLibelle();
                        $isTerminee = in_array($etat, ['Passée', 'Terminée'], true);
                        $isInscrit = $sortie->getParticipantInscrit()->exists(fn($k, $p) => $p->getId() === $user->getId());

                        if (!$isTerminee) {
                            $message = 'Vous pourrez noter une fois la sortie terminée.';
                        } elseif (!$isInscrit) {
                            $message = 'Seuls les participants inscrits peuvent noter.';
                        } else {
                            $message = "Vous n'avez pas l'autorisation de noter cette sortie.";
                        }
                    } else {
                        $message = "Vous n'avez pas l'autorisation de noter cette sortie.";
                    }
                } else {
                    $message = "Vous n'avez pas l'autorisation de noter cette sortie.";
                }
                $redirectUrl = $targetShow;
            } else {
                $message = "Vous ne disposez pas des droits suffisants pour cette action sur la sortie.";
                $redirectUrl = $targetShow;
            }
        }
        // Admin-only
        elseif (str_starts_with($route, 'app_site_') || str_starts_with($route, 'app_ville_') || str_starts_with($route, 'app_lieu_')) {
            $message = "Cette section est réservée aux administrateurs.";
            // Redirection vers la page d'accueil des sorties pour les sections admin-only
            $redirectUrl = $this->router->generate('app_sortie_index');
        }

        $session?->getFlashBag()->add('error', $message);
        $event->setResponse(new RedirectResponse($redirectUrl));
    }
}
