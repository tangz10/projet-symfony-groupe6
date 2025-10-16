<?php

namespace App\Controller;

use App\Entity\Note;
use App\Entity\Sortie;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class NoteController extends AbstractController
{
    #[Route('/sortie/{id}/noter', name: 'app_sortie_noter', methods: ['POST'])]
    public function noter(Sortie $sortie, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('rate'.$sortie->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        // Décision d'accès centralisée via voter + AccessDeniedSubscriber pour les messages
        $this->denyAccessUnlessGranted('NOTE_RATE', $sortie);

        $me = $this->getUser();

        $note = (int) $request->request->get('note', 0);
        if ($note < 1 || $note > 5) {
            $this->addFlash('error', 'La note doit être entre 1 et 5.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        $noteRepo = $em->getRepository(Note::class);
        $rating = $noteRepo->findOneBy(['sortie' => $sortie, 'participant' => $me]) ?? new Note();
        $rating->setSortie($sortie)->setParticipant($me)->setNote($note);

        $em->persist($rating);
        $em->flush();

        $this->addFlash('success', 'Merci pour votre note !');
        return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
    }
}
