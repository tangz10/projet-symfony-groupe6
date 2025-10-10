<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\Etat;
use App\Form\SortieFilterType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Psr\Log\LoggerInterface;

#[Route('/sortie')]
class SortieController extends AbstractController
{
    #[Route(name: 'app_sortie_index', methods: ['GET'])]
    public function index(Request $request, SortieRepository $repo): Response
    {
        $form = $this->createForm(SortieFilterType::class);
        $form->handleRequest($request);

        $filters = $form->getData() ?? [];
        $user = $this->getUser();

        $sorties = $repo->findForListing($filters, $user);

        return $this->render('sortie/index.html.twig', [
            'form' => $form->createView(),
            'sorties' => $sorties,
            'me' => $user,
        ]);
    }

    #[Route('/new', name: 'app_sortie_new', methods: ['GET','POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $em, EtatRepository $etatRepository): Response
    {
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sortie->setParticipantOrganisateur($this->getUser());

            if ($etat = $etatRepository->findOneBy(['libelle' => 'Créée'])) {
                $sortie->setEtat($etat);
            }

            $em->persist($sortie);
            $em->flush();

            return $this->redirectToRoute('app_sortie_index');
        }

        $status = $form->isSubmitted() ? 422 : 200;

        return $this->render('sortie/new.html.twig', [
            'form' => $form->createView(),
        ], new Response(status: $status));
    }

    #[Route('/{id}', name: 'app_sortie_show', requirements: ['id' => '\d+'])]
    public function show(Sortie $sortie): Response
    {
        return $this->render('sortie/show.html.twig', [
            's'  => $sortie,
            'me' => $this->getUser(),
        ]);
    }

    #[Route('/{id}/annuler', name: 'app_sortie_annuler', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function annuler(
        Sortie $sortie,
        Request $request,
        EtatRepository $etatRepository,
        EntityManagerInterface $em
    ) {
        if (!$this->isCsrfTokenValid('cancel'.$sortie->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        $me = $this->getUser();

        if (!$sortie->getParticipantOrganisateur() || $sortie->getParticipantOrganisateur()->getId() !== $me->getId()) {
            $this->addFlash('error', 'Vous ne pouvez pas annuler cette sortie.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        $now = new \DateTimeImmutable();
        if ($sortie->getDateHeureDebut() && $sortie->getDateHeureDebut() <= $now) {
            $this->addFlash('error', 'La sortie a déjà commencé : annulation impossible.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        $motif = trim($request->request->get('motif', ''));

        $etatAnnulee = $etatRepository->findOneBy(['libelle' => 'Annulée']);
        if (!$etatAnnulee) {
            $this->addFlash('error', 'État "Annulée" introuvable.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        $sortie->setEtat($etatAnnulee);

        if (method_exists($sortie, 'setMotifAnnulation')) {
            $sortie->setMotifAnnulation($motif ?: null);
        }

        $em->flush();

        $this->addFlash('success', 'La sortie a été annulée.');
        return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
    }

    #[Route('/{id}/publier', name: 'app_sortie_publier', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function publier(
        Sortie $sortie,
        Request $request,
        EtatRepository $etatRepository,
        EntityManagerInterface $em
    ) {
        if (!$this->isCsrfTokenValid('publish'.$sortie->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        $me = $this->getUser();
        if (!$sortie->getParticipantOrganisateur() || $sortie->getParticipantOrganisateur()->getId() !== $me->getId()) {
            $this->addFlash('error', 'Vous ne pouvez pas publier cette sortie.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        if (!$sortie->getEtat() || $sortie->getEtat()->getLibelle() !== 'Créée') {
            $this->addFlash('error', 'Seules les sorties à l’état "Créée" peuvent être publiées.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        $etatOuverte = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
        if (!$etatOuverte) {
            $this->addFlash('error', 'État "Ouverte" introuvable.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        $sortie->setEtat($etatOuverte);
        $em->flush();

        $this->addFlash('success', 'La sortie a été publiée (ouverte aux inscriptions).');
        return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
    }

    #[Route('/{id}/inscrire', name: 'app_sortie_inscrire', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function inscrire(
        Sortie $sortie,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        // Validation du token CSRF
        if (!$this->isCsrfTokenValid('inscrire'.$sortie->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        $user = $this->getUser();

        // Vérifier que l'utilisateur est bien un Participant
        if (!$user instanceof \App\Entity\Participant) {
            throw $this->createAccessDeniedException('Utilisateur non valide.');
        }

        $participantId = $user->getId();

        // Vérifier que l'état est "Ouverte" (insensible à la casse)
        if (!$sortie->getEtat() || strtolower(trim($sortie->getEtat()->getLibelle())) !== 'ouverte') {
            $this->addFlash('error', 'Cette sortie n\'est pas ouverte à l\'inscription pour l\'instant. État actuel : ' . ($sortie->getEtat() ? $sortie->getEtat()->getLibelle() : 'null'));
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        // Vérifier que le participant n'est pas déjà inscrit (requête SQL directe)
        $conn = $em->getConnection();
        $sql = 'SELECT COUNT(*) as count FROM sortie_participant WHERE sortie_id = :sortie_id AND participant_id = :participant_id';
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery(['sortie_id' => $sortie->getId(), 'participant_id' => $participantId]);
        $alreadyRegistered = $result->fetchOne() > 0;

        if ($alreadyRegistered) {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à cette sortie.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        // Vérifier le nombre max d'inscrits
        $sqlCount = 'SELECT COUNT(*) as count FROM sortie_participant WHERE sortie_id = :sortie_id';
        $stmtCount = $conn->prepare($sqlCount);
        $resultCount = $stmtCount->executeQuery(['sortie_id' => $sortie->getId()]);
        $currentCount = $resultCount->fetchOne();

        if ($currentCount >= $sortie->getNbInscriptionsMax()) {
            $this->addFlash('error', 'Cette sortie est complète.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        // Vérifier la date limite d'inscription
        $now = new \DateTime();
        if ($sortie->getDateLimiteInscription() && $sortie->getDateLimiteInscription() < $now) {
            $this->addFlash('error', 'La date limite d\'inscription est dépassée.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        try {
            // INSERT DIRECT dans la table de jointure (contournement du problème Doctrine)
            $sqlInsert = 'INSERT INTO sortie_participant (sortie_id, participant_id) VALUES (:sortie_id, :participant_id)';
            $stmtInsert = $conn->prepare($sqlInsert);
            $stmtInsert->executeStatement([
                'sortie_id' => $sortie->getId(),
                'participant_id' => $participantId
            ]);

            $this->addFlash('success', 'Inscription réussie !');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'inscription : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
    }

    #[Route('/{id}/desister', name: 'app_sortie_desister', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function desister(
        Sortie $sortie,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        // Validation du token CSRF
        if (!$this->isCsrfTokenValid('desister'.$sortie->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        $user = $this->getUser();

        // Vérifier que l'utilisateur est bien un Participant
        if (!$user instanceof \App\Entity\Participant) {
            throw $this->createAccessDeniedException('Utilisateur non valide.');
        }

        // CRUCIAL : Récupérer le participant depuis la base pour avoir une entité managée
        $participant = $em->getRepository(\App\Entity\Participant::class)->find($user->getId());

        if (!$participant) {
            throw $this->createNotFoundException('Participant introuvable.');
        }

        // Vérifier que le participant est bien inscrit
        if (!$sortie->getParticipantInscrit()->contains($participant)) {
            $this->addFlash('warning', 'Vous n\'êtes pas inscrit à cette sortie.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        // Vérifier que la sortie n'a pas déjà commencé
        $now = new \DateTime();
        if ($sortie->getDateHeureDebut() && $sortie->getDateHeureDebut() <= $now) {
            $this->addFlash('error', 'La sortie a déjà commencé, vous ne pouvez plus vous désister.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        // Retirer l'inscription
        $sortie->removeParticipantInscrit($participant);

        // Flush pour persister la modification
        $em->flush();

        $this->addFlash('success', 'Vous vous êtes désisté de cette sortie.');
        return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
    }

    #[Route('/{id}/edit', name: 'app_sortie_edit', methods: ['GET','POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Sortie $sortie, Request $request, EntityManagerInterface $em): Response
    {
        $me = $this->getUser();

        if (!$sortie->getParticipantOrganisateur() || $sortie->getParticipantOrganisateur()->getId() !== $me->getId()) {
            $this->addFlash('error', 'Vous ne pouvez pas modifier cette sortie.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        if (!$sortie->getEtat() || $sortie->getEtat()->getLibelle() !== 'Créée') {
            $this->addFlash('error', 'Seules les sorties à l’état "Créée" sont modifiables.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Sortie mise à jour.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        return $this->render('sortie/edit.html.twig', [
            'form' => $form->createView(),
            's'    => $sortie,
            'me'   => $me,
        ]);
    }
}
