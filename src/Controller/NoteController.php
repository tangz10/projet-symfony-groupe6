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

        $me = $this->getUser();
        if (!$me instanceof \App\Entity\Participant) {
            throw $this->createAccessDeniedException();
        }

        $etat = $sortie->getEtat()?->getLibelle();
        if (!$etat == 'Passée') {
            $this->addFlash('error', 'Vous pourrez noter une fois la sortie terminée.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        if (!$sortie->getParticipantInscrit()->contains($me)) {
            $this->addFlash('error', 'Seuls les participants inscrits peuvent noter.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

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
