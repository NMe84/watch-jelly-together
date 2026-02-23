<?php

/*
 * WatchJellyTogether
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Entity\UserConnection;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**  @extends PersistentObjectFactory<UserConnection> */
final class UserConnectionFactory extends PersistentObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return UserConnection::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'show' => ShowFactory::new(),
            'users' => [UserFactory::new(), UserFactory::new()],
        ];
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this;
    }
}
