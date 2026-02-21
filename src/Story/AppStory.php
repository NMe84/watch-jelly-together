<?php

/*
 * WatchJellyTogether
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Story;

use App\Factory\ShowFactory;
use App\Factory\UserConnectionFactory;
use App\Factory\UserFactory;
use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;

#[AsFixture(name: 'main')]
final class AppStory extends Story
{
    public function build(): void
    {
        $showA = ShowFactory::createOne(['id' => 'A', 'name' => 'Show A', 'serverId' => '1']);
        $showB = ShowFactory::createOne(['id' => 'B', 'name' => 'Show B', 'serverId' => '2']);

        $userX = UserFactory::createOne(['id' => 'X', 'name' => 'User X']);
        $userY = UserFactory::createOne(['id' => 'Y', 'name' => 'User Y']);

        UserConnectionFactory::createOne(['show' => $showA, 'users' => [$userX, $userY]]);
        UserConnectionFactory::createOne(['show' => $showB, 'users' => [$userX, $userY]]);
    }
}
