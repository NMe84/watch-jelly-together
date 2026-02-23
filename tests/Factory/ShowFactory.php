<?php

/*
 * WatchJellyTogether
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Entity\Show;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/** @extends PersistentObjectFactory<Show> */
final class ShowFactory extends PersistentObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return Show::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'id' => self::faker()->text(),
            'serverId' => self::faker()->text(),
            'name' => self::faker()->text(),
        ];
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this;
    }
}
