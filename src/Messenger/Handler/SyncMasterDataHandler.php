<?php

/*
 * WatchJellyTogether
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Messenger\Handler;

use App\Jellyfin\Connector;
use App\Messenger\Message\SyncMasterDataMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

readonly class SyncMasterDataHandler
{
    public function __construct(private Connector $connector)
    {
    }

    #[AsMessageHandler]
    public function __invoke(SyncMasterDataMessage $message): void
    {
        $this->connector->refreshUsers();
        $this->connector->refreshShows();
    }
}
