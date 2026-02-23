<?php

/*
 * WatchJellyTogether
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Scheduler;

use App\Messenger\Message\SyncMasterDataMessage;
use App\Messenger\Message\SyncWatchStatesMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule as SymfonySchedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsSchedule('sync_jellyfin')]
readonly class WatchStatesSchedule implements ScheduleProviderInterface
{
    public function __construct(
        private CacheInterface $cache,
    ) {
    }

    public function getSchedule(): SymfonySchedule
    {
        return new SymfonySchedule()
            ->stateful($this->cache)
            ->processOnlyLastMissedRun(true)
            ->with(RecurringMessage::cron('0/5 * * * *', new SyncWatchStatesMessage()))
            ->with(RecurringMessage::cron('0 * * * *', new SyncMasterDataMessage()))
        ;
    }
}
