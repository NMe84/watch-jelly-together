<?php

/*
 * WatchJellyTogether
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Messenger\Handler;

use App\Entity\Show;
use App\Entity\UserConnection;
use App\Jellyfin\Connector;
use App\Messenger\Message\SyncWatchStatesMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

readonly class SyncWatchStatesHandler
{
    public function __construct(
        private Connector $connector,
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
    ) {
    }

    #[AsMessageHandler]
    public function __invoke(SyncWatchStatesMessage $message): void
    {
        if ($connection = $this->em->getRepository(UserConnection::class)->find($message->getUserConnectionId())) {
            assert($connection->getShow() instanceof Show);
            $this->connector->syncWatchStates($connection->getUsers()->toArray(), $connection->getShow());
            $this->logger->info(
                sprintf('Synced watch states for show %s between users %s', $connection->getShow()->getName(), implode(', ', $connection->getUsers()->toArray()))
            );
        }
    }
}
