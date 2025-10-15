<?php
// src/Service/ParticipantCsvImporter.php
namespace App\Service;

use App\Entity\Participant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantCsvImporter
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function import(string $filePath): array
    {
        $results = ['success' => 0, 'errors' => []];

        if (!file_exists($filePath)) {
            $results['errors'][] = 'Fichier introuvable';
            return $results;
        }

        $file = fopen($filePath, 'r');

        // Sauter l'en-tête
        fgetcsv($file);

        $line = 1;
        while (($data = fgetcsv($file)) !== false) {
            $line++;

            try {
                if (count($data) < 6) {
                    $results['errors'][] = "Ligne $line : données incomplètes";
                    continue;
                }

                [$nom, $prenom, $email, $telephone, $pseudo, $password, $administrateur] = $data;

                // Vérifier si l'email existe déjà
                if ($this->entityManager->getRepository(Participant::class)->findOneBy(['email' => $email])) {
                    $results['errors'][] = "Ligne $line : email '$email' déjà existant";
                    continue;
                }

                $participant = new Participant();
                $participant->setNom($nom);
                $participant->setPrenom($prenom);
                $participant->setEmail($email);
                $participant->setTelephone($telephone);
                $participant->setPseudo($pseudo);
                $participant->setActif(true);

                if ($administrateur === "FALSE" ) {
                    $administrateur = false;
                } else {
                    $administrateur = true;
                }

                $participant->setAdministrateur($administrateur);

                // Hash du mot de passe
                $hashedPassword = $this->passwordHasher->hashPassword($participant, $password);
                $participant->setPassword($hashedPassword);

                $this->entityManager->persist($participant);
                $results['success']++;

                // Flush tous les 50 pour optimiser
                if ($results['success'] % 50 === 0) {
                    $this->entityManager->flush();
                }

            } catch (\Exception $e) {
                $results['errors'][] = "Ligne $line : {$e->getMessage()}";
            }
        }

        fclose($file);
        $this->entityManager->flush();

        return $results;
    }
}
