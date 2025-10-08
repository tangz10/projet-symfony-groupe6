<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\Etat;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/sortie')]
class SortieController extends AbstractController
{
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

            return $this->redirectToRoute('app_index');
        }

        $status = $form->isSubmitted() ? 422 : 200;

        return $this->render('sortie/createSortie.html.twig', [
            'form' => $form->createView(),
        ], new Response(status: $status));
    }

}
