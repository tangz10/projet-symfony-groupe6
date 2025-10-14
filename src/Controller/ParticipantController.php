<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\Security; // ✅ important !

#[Route('/participant')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class ParticipantController extends AbstractController
{
    #[Route(name: 'app_participant_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(ParticipantRepository $participantRepository): Response
    {
        return $this->render('participant/index.html.twig', [
            'participants' => $participantRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_participant_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $participant = new Participant();
        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($participant);
            $entityManager->flush();

            return $this->redirectToRoute('app_participant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('participant/new.html.twig', [
            'participant' => $participant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_participant_show', methods: ['GET'])]
    public function show(Participant $participant): Response
    {

        $user = $this->getUser();

        if (!$this->isGranted('ROLE_ADMIN') && (!$user || $user->getId() !== $participant->getId())) {
            throw $this->createAccessDeniedException('Accès refusé');
        }

        return $this->render('participant/show.html.twig', [
            'participant' => $participant,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_participant_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Participant $participant, EntityManagerInterface $entityManager): Response
    {

        $user = $this->getUser();

        if (!$this->isGranted('ROLE_ADMIN') && (!$user || $user->getId() !== $participant->getId())) {
            throw $this->createAccessDeniedException('Accès refusé');
        }

        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($participant->isAdministrateur() === true) {
                $participant->setRoles(['ROLE_ADMIN']);
            } else {
                $participant->setRoles(['ROLE_USER']);
            }

            if ($request->request->get('delete_photo') === '1') {
                $participant->setPhotoProfilFile(null);
                $participant->setPhotoProfil(null);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_participant_show', [
                'id' => $participant->getId(),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('participant/edit.html.twig', [
            'participant' => $participant,
            'nomParticipant' => $participant->getPrenom(),
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_participant_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Participant $participant, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$participant->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($participant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_participant_index', [], Response::HTTP_SEE_OTHER);
    }
}
