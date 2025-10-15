<?php

namespace App\Service;

use App\Entity\Sortie;
use App\Entity\Etat;
use App\Repository\EtatRepository;
use Doctrine\ORM\EntityManagerInterface;

class SortieStateResolver
{
    public function __construct(
        private EtatRepository $etatRepository,
        private EntityManagerInterface $em
    ) {
    }

    /**
     * Calcule et applique l’état attendu. Retourne true si l’état a changé.
     */
    public function resolveAndApply(Sortie $sortie, \DateTimeInterface $now = null): bool
    {
        $now = $now ? \DateTime::createFromInterface($now) : new \DateTime('now');

        // Etats
        $etatAnnulee   = $this->getEtat('Annulée');
        $etatOuverte   = $this->getEtat('Ouverte');
        $etatCloturee  = $this->getEtat('Clôturée');
        $etatEnCours   = $this->getEtat('Activité en cours');
        $etatPassee    = $this->getEtat('Passée');
        $etatCreee     = $this->getEtat('Créée');

        $current = $sortie->getEtat();
        $currentLabel = $current?->getLibelle();

        // Si annulée: on ne change plus automatiquement
        if ($currentLabel && $this->equalsEtat($current, $etatAnnulee)) {
            return false;
        }

        // Données nécessaires
        $dateDebut = $sortie->getDateHeureDebut();
        $duree = $sortie->getDuree() ?? 0; // minutes
        $dateLimite = $sortie->getDateLimiteInscription();
        $nbMax = $sortie->getNbInscriptionsMax() ?? 0;
        $nbInscrits = $sortie->getParticipantInscrit()->count();

        // Dates nulles: prudence, ne change rien
        if (!$dateDebut || !$dateLimite) {
            return false;
        }

        // Calcul date de fin = début + durée (minutes). getDateHeureDebut est un DATE (00:00) dans le mapping, on fait au mieux.
        $dateFin = (clone $dateDebut)->modify("+{$duree} minutes");

        // Ordre des règles temporelles
        if ($now >= $dateFin) {
            $target = $etatPassee;
        } elseif ($now >= $dateDebut && $now < $dateFin) {
            $target = $etatEnCours;
        } else {
            // Avant le début: Ouverte ou Clôturée selon capacité/date limite
            $isFull = $nbInscrits >= $nbMax && $nbMax > 0;
            $isAfterLimit = $now > $dateLimite;
            $target = ($isFull || $isAfterLimit) ? $etatCloturee : $etatOuverte;
        }

        // Edge: si état "Créée" et target calculé = Ouverte, on bascule sur Ouverte (publication implicite)
        if ($current && $this->equalsEtat($current, $etatCreee) && $this->equalsEtat($target, $etatOuverte)) {
            // ok, on passera à Ouverte ci-dessous
        }

        if (!$current || !$this->equalsEtat($current, $target)) {
            $sortie->setEtat($target);
            return true;
        }

        return false;
    }

    private function getEtat(string $label): Etat
    {
        $etat = $this->etatRepository->createQueryBuilder('e')
            ->where('LOWER(e.libelle) = :l')
            ->setParameter('l', mb_strtolower($label))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$etat) {
            // Crée l’état à la volée si manquant (sécurité dev)
            $etat = new Etat();
            $etat->setLibelle($label);
            $this->em->persist($etat);
            $this->em->flush();
        }
        return $etat;
    }

    private function equalsEtat(?Etat $a, ?Etat $b): bool
    {
        return $a && $b && mb_strtolower($a->getLibelle()) === mb_strtolower($b->getLibelle());
    }
}

