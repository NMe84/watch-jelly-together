<?php

/*
 * WatchJellyTogether
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Messenger\Handler;

use App\Entity\UserConnection;
use App\Messenger\Message\SyncShowWatchStatesMessage;
use App\Messenger\Message\SyncWatchStatesMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class SyncWatchStatesHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
        private MessageBusInterface $messageBus,
    ) {
    }

    #[AsMessageHandler]
    public function __invoke(SyncWatchStatesMessage $message): void
    {
        $counter = 0;
        foreach ($this->em->getRepository(UserConnection::class)->findAll() as $connection) {
            assert(is_int($connection->getId()));
            $this->messageBus->dispatch(new SyncShowWatchStatesMessage($connection->getId()));
            ++$counter;
        }

        $this->logger->info(sprintf('Queued %d connections for watch state sync', $counter));
    }
}
