<?php

/*
 * WatchJellyTogether
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

use App\Scheduler\WatchStatesSchedule;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\Cache\CacheInterface;

class WatchStatesScheduleTest extends KernelTestCase
{
    #[AllowMockObjectsWithoutExpectations]
    public function testItRegistersTwoRecurringMessages(): void
    {
        $cache = $this->createMock(CacheInterface::class);

        $provider = new WatchStatesSchedule($cache);
        $schedule = $provider->getSchedule();
        $this->assertTrue($provider->getSchedule()->shouldProcessOnlyLastMissedRun());

        $recurring = $schedule->getRecurringMessages();
        $this->assertCount(2, $recurring);
    }
}
