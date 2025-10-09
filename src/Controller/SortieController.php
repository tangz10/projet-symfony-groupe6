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
}
