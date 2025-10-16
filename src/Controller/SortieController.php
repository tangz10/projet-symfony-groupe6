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
use Symfony\Component\Messenger\MessageBusInterface;
use App\Message\RefreshOneSortieStateMessage;
use App\Service\MeteoService;

#[Route('/sortie')]
#[IsGranted('IS_AUTHENTICATED_FULLY')] // restauration: le contrôleur nécessite une authentification complète
class SortieController extends AbstractController
{
    #[Route(name: 'app_sortie_index', methods: ['GET'])]
    public function index(Request $request, SortieRepository $repo, MeteoService $meteo): Response
    {
        $form = $this->createForm(SortieFilterType::class);
        $form->handleRequest($request);

        $filters = $form->getData() ?? [];
        $user = $this->getUser();

        $sorties = $repo->findForListing($filters, $user);

        $meteos = [];
        foreach ($sorties as $s) {
            $d = $s->getDateHeureDebut();
            $lieu = $s->getLieu();

            $reason = null;
            if (!$d) {
                $reason = 'Date de sortie inconnue';
            } elseif (!$lieu) {
                $reason = 'Lieu de la sortie inconnu';
            } elseif ($lieu->getLatitude() === null || $lieu->getLongitude() === null) {
                $reason = 'Coordonnées du lieu manquantes';
            } else {
                $dt = \DateTimeImmutable::createFromMutable($d);
                $fc = $meteo->getDailyForecast($dt, (float)$lieu->getLatitude(), (float)$lieu->getLongitude());

                if ($fc) {
                    $meteos[$s->getId()] = $fc;
                    continue;
                }

                $today0 = new \DateTimeImmutable('today');
                $days = (int)$today0->diff($dt->setTime(0,0))->format('%r%a');

                if ($days > 15) {
                    $reason = 'Date trop lointaine (prévision au-delà de 16 jours)';
                } elseif ($days < -60) {
                    $reason = 'Historique indisponible pour cette date';
                } else {
                    $reason = 'Aucune donnée météo fournie par l’API pour cette date';
                }
            }

            if ($reason) {
                $meteos[$s->getId()] = ['na_reason' => $reason];
            }
        }

        return $this->render('sortie/index.html.twig', [
            'form'    => $form->createView(),
            'sorties' => $sorties,
            'me'      => $user,
            'meteo'  => $meteos,
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
            's'    => $sortie,
        ], new Response(status: $status));
    }

    #[Route('/{id}', name: 'app_sortie_show', methods: ['GET'])]
    public function show(
        Sortie $sortie,
        EtatRepository $etatRepository,
        EntityManagerInterface $em,
        MeteoService $meteo
    ): Response {
        $now = new \DateTimeImmutable();

        $lib = $sortie->getEtat()?->getLibelle();

        if ($lib !== 'Annulée'
            && $sortie->getDateLimiteInscription()
            && $sortie->getDateLimiteInscription() <= $now
            && in_array($lib, ['Créée', 'Ouverte'], true)) {

            if ($etatCloturee = $etatRepository->findOneBy(['libelle' => 'Clôturée'])) {
                $sortie->setEtat($etatCloturee);
                $em->flush();
            }
        }

        $notes = $sortie->getNote();
        $moy = $notes->count() ? array_sum(array_map(fn($r)=>$r->getNote(), $notes->toArray())) / $notes->count() : null;
        $maNote = null;
        if ($this->getUser() instanceof \App\Entity\Participant) {
            foreach ($notes as $r) {
                if ($r->getParticipant()->getId() === $this->getUser()->getId()) {
                    $maNote = $r->getNote();
                    break;
                }
            }
        }

        // Initialisation par défaut pour éviter une variable non définie
        $meteoNow = null;
        if ($sortie->getLieu()?->getLatitude() !== null
            && $sortie->getLieu()?->getLongitude() !== null
            && $sortie->getDateHeureDebut() instanceof \DateTimeInterface) {

            $meteoNow = $meteo->getDailyForecast(
                $sortie->getDateHeureDebut(),
                (float)$sortie->getLieu()->getLatitude(),
                (float)$sortie->getLieu()->getLongitude()
            );
        }

        return $this->render('sortie/show.html.twig', [
            's'  => $sortie,
            'me' => $this->getUser(),
            'avgNote' => $moy,
            'myNote' => $maNote,
            'meteo' => $meteoNow,
        ]);
    }

    #[Route('/{id}/annuler', name: 'app_sortie_annuler', methods: ['POST'])]
    #[IsGranted('ROLE_USER')] // restauration: accès réservé aux utilisateurs connectés
    public function annuler(
        Sortie $sortie,
        Request $request,
        EtatRepository $etatRepository,
        EntityManagerInterface $em,
        MessageBusInterface $bus
    ) {
        // Vérification via voter: si refus → message + retour show (pas de 403 ici pour conserver l'UX)
        if (!$this->isGranted('SORTIE_CANCEL', $sortie)) {
            $this->addFlash('error', "Seul l'organisateur de la sortie ou un administrateur peut annuler une sortie.");
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        // CSRF obligatoire (POST uniquement)
        if (!$this->isCsrfTokenValid('cancel'.$sortie->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        $me = $this->getUser();
        if (!$me instanceof \App\Entity\Participant) {
            throw $this->createAccessDeniedException('Utilisateur non valide.');
        }

        $isAdmin = $this->isGranted('ROLE_ADMIN');

        $isOrganisateur = $sortie->getParticipantOrganisateur()
            && $sortie->getParticipantOrganisateur()->getId() === $me->getId();

        if (!($isAdmin || $isOrganisateur)) {
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

        // Rafraîchissement asynchrone de l'état
        $bus->dispatch(new RefreshOneSortieStateMessage($sortie->getId()));

        return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
    }

    #[Route('/{id}/publier', name: 'app_sortie_publier', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function publier(
        Sortie $sortie,
        Request $request,
        EtatRepository $etatRepository,
        EntityManagerInterface $em,
        MessageBusInterface $bus
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

        // Tick de recalcul
        $bus->dispatch(new RefreshOneSortieStateMessage($sortie->getId()));

        $this->addFlash('success', 'La sortie a été publiée (ouverte aux inscriptions).');
        return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
    }

    #[Route('/{id}/inscrire', name: 'app_sortie_inscrire', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function inscrire(
        Sortie $sortie,
        Request $request,
        EntityManagerInterface $em,
        EtatRepository $etatRepo,
        MessageBusInterface $bus
    ): Response {
        // Décision via voter (peut rester pour robustesse, mais l'anonyme ne rentre plus ici)
        if (!$this->isGranted('SORTIE_REGISTER', $sortie)) {
            $this->addFlash('error', "Il faut être connecté en tant que participant pour s'inscrire à une sortie.");
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        // CSRF (POST uniquement)
        if (!$this->isCsrfTokenValid('inscrire'.$sortie->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        $user = $this->getUser();
        $participantId = $user->getId();

        if (!$sortie->getEtat() || strtolower(trim($sortie->getEtat()->getLibelle())) !== 'ouverte') {
            $this->addFlash('error', 'Cette sortie n\'est pas ouverte à l\'inscription pour l\'instant. État actuel : ' . ($sortie->getEtat() ? $sortie->getEtat()->getLibelle() : 'null'));
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        $conn = $em->getConnection();
        $sql = 'SELECT COUNT(*) as count FROM sortie_participant WHERE sortie_id = :sortie_id AND participant_id = :participant_id';
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery(['sortie_id' => $sortie->getId(), 'participant_id' => $participantId]);
        $alreadyRegistered = $result->fetchOne() > 0;

        if ($alreadyRegistered) {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à cette sortie.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        $sqlCount = 'SELECT COUNT(*) as count FROM sortie_participant WHERE sortie_id = :sortie_id';
        $stmtCount = $conn->prepare($sqlCount);
        $resultCount = $stmtCount->executeQuery(['sortie_id' => $sortie->getId()]);
        $currentCount = $resultCount->fetchOne();

        if ($currentCount >= $sortie->getNbInscriptionsMax()) {
            $this->addFlash('error', 'Cette sortie est complète.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        $now = new \DateTime();
        if ($sortie->getDateLimiteInscription() && $sortie->getDateLimiteInscription() < $now) {
            $this->addFlash('error', 'La date limite d\'inscription est dépassée.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        try {
            $sqlInsert = 'INSERT INTO sortie_participant (sortie_id, participant_id) VALUES (:sortie_id, :participant_id)';
            $stmtInsert = $conn->prepare($sqlInsert);
            $stmtInsert->executeStatement([
                'sortie_id' => $sortie->getId(),
                'participant_id' => $participantId
            ]);

            $sqlCountAfter = 'SELECT COUNT(*) FROM sortie_participant WHERE sortie_id = :sid';
            $countAfter = (int)$conn->prepare($sqlCountAfter)->executeQuery(['sid' => $sortie->getId()])->fetchOne();

            if ($sortie->getNbInscriptionsMax() !== null && $countAfter >= $sortie->getNbInscriptionsMax()) {
                $etatCloturee = $etatRepo->findOneBy(['libelle' => 'Clôturée']);
                if ($etatCloturee) {
                    $sortie->setEtat($etatCloturee);
                    $em->flush();
                }
            }

            $this->addFlash('success', 'Inscription réussie !');
            $bus->dispatch(new RefreshOneSortieStateMessage($sortie->getId()));
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
        EntityManagerInterface $em,
        EtatRepository $etatRepo,
        MessageBusInterface $bus
    ): Response {
        if (!$this->isCsrfTokenValid('desister'.$sortie->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        // Voter: exige un Participant connecté pour se désister
        $this->denyAccessUnlessGranted('SORTIE_UNREGISTER', $sortie);

        $user = $this->getUser();

        $participant = $em->getRepository(\App\Entity\Participant::class)->find($user->getId());

        if (!$participant) {
            throw $this->createNotFoundException('Participant introuvable.');
        }

        if (!$sortie->getParticipantInscrit()->contains($participant)) {
            $this->addFlash('warning', 'Vous n\'êtes pas inscrit à cette sortie.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        $now = new \DateTime();
        if ($sortie->getDateHeureDebut() && $sortie->getDateHeureDebut() <= $now) {
            $this->addFlash('error', 'La sortie a déjà commencé, vous ne pouvez plus vous désister.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        $sortie->removeParticipantInscrit($participant);

        $em->flush();

        $conn = $em->getConnection();
        $countNow = (int)$conn->prepare('SELECT COUNT(*) FROM sortie_participant WHERE sortie_id = :sid')
            ->executeQuery(['sid' => $sortie->getId()])
            ->fetchOne();

        $max = $sortie->getNbInscriptionsMax() ?? PHP_INT_MAX;

        $now = new \DateTimeImmutable('now');
        $limit = $sortie->getDateLimiteInscription(); // \DateTimeInterface|null
        $limitPassed = $limit ? ($limit < $now->setTime(0,0)) : false;

        if ($countNow >= $max) {
            $etat = $etatRepo->findOneBy(['libelle' => 'Clôturée']);
            if ($etat) {
                $sortie->setEtat($etat);
                $em->flush();
            }
        } else {
            if ($limitPassed) {
                $etat = $etatRepo->findOneBy(['libelle' => 'Clôturée']);
            } else {
                $etat = $etatRepo->findOneBy(['libelle' => 'Ouverte']);
            }
            if ($etat) {
                $sortie->setEtat($etat);
                $em->flush();
            }
        }

        $this->addFlash('success', 'Vous vous êtes désisté de cette sortie.');

        // Recalcul asynchrone après désistement
        $bus->dispatch(new RefreshOneSortieStateMessage($sortie->getId()));

        return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
    }

    #[Route('/{id}/edit', name: 'app_sortie_edit', methods: ['GET','POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Sortie $sortie, Request $request, EntityManagerInterface $em): Response
    {
        // Voter en premier: si refus → message + retour show
        if (!$this->isGranted('SORTIE_EDIT', $sortie)) {
            $this->addFlash('error', "Seul l'organisateur de la sortie ou un administrateur peux éditer une sortie");
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        $me = $this->getUser();
        $isAdmin = $this->isGranted('ROLE_ADMIN');
        $isOrganisateur = $sortie->getParticipantOrganisateur() && $sortie->getParticipantOrganisateur()->getId() === $me->getId();

        if (!($isOrganisateur || $isAdmin)) {
            $this->addFlash('error', "Seul l'organisateur de la sortie ou un administrateur peux éditer une sortie");
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        if (!$sortie->getEtat() || $sortie->getEtat()->getLibelle() !== 'Créée') {
            $this->addFlash('error', 'Seules les sorties à l’état "Créée" sont modifiables.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($request->request->get('delete_photo') === '1') {
                $sortie->setPhotoFile(null);
                $sortie->setPhoto(null);
            }

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
