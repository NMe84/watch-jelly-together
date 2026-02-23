<?php

/*
 * WatchJellyTogether
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Messenger\Message;

class SyncWatchStatesMessage implements MessageInterface
{
    public function __construct(
        private readonly string $userConnectionId,
    ) {
    }

    public function getUserConnectionId(): string
    {
        return $this->userConnectionId;
    }
}
