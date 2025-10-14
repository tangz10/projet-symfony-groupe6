<?php

namespace App\Command;

use App\Message\ArchiveSortiesMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsCommand(
    name: 'app:schedule-archive',
    description: 'Planifie l\'archivage automatique des sorties à 23h30'
)]
class ScheduleArchiveCommand extends Command
{
    public function __construct(
        private MessageBusInterface $messageBus
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // POUR TEST : exécution dans 30 secondes
        $now = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        $scheduledTime = (clone $now)->modify('+120 seconds');

        $io->info(sprintf('Heure actuelle : %s', $now->format('d/m/Y à H:i:s')));

        /* VERSION PRODUCTION (à décommenter après test)
        // Calculer le délai jusqu'à 23h30 ce soir
        $now = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        $scheduledTime = new \DateTime('today 23:30:00', new \DateTimeZone('Europe/Paris'));

        // Si on est déjà passé 23h30, planifier pour demain
        if ($now > $scheduledTime) {
            $scheduledTime->modify('+1 day');
        }
        */

        $delay = $scheduledTime->getTimestamp() - $now->getTimestamp();
        $delayInMilliseconds = $delay * 1000;

        // Dispatcher le message avec un délai
        $message = new ArchiveSortiesMessage($scheduledTime);
        $this->messageBus->dispatch($message, [
            new DelayStamp($delayInMilliseconds)
        ]);

        $io->success(sprintf(
            'Archivage planifié pour %s (dans %d secondes)',
            $scheduledTime->format('d/m/Y à H:i:s'),
            $delay
        ));

        return Command::SUCCESS;
    }
}
