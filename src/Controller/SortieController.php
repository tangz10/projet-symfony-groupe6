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

class SortieController extends AbstractController
{
    #[Route('/', name: 'sortie_index', methods: ['GET'])]
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
    #[Route('/sortie/new', name: 'sortie_new', methods: ['GET','POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $em, EtatRepository $etatRepository): Response
    {
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sortie->setParticipantOrganisateur($this->getUser());

            if ($etat = $etatRepository->findOneBy(['libelle' => 'Créée'])) {
                $sortie->setEtatRelation($etat);
            }

            $em->persist($sortie);
            $em->flush();

            return $this->redirectToRoute('sortie_index');
        }

        $status = $form->isSubmitted() ? 422 : 200;

        return $this->render('sortie/createSortie.html.twig', [
            'form' => $form->createView(),
        ], new Response(status: $status));
    }

}
