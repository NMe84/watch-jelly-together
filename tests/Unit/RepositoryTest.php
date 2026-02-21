<?php

/*
 * WatchJellyTogether
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Repository\UserConnectionRepository;
use App\Story\AppStory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class RepositoryTest extends KernelTestCase
{
    use ResetDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
        AppStory::load();
    }

    public function testUserConnectionFindAll(): void
    {
        $repo = $this->getUserConnectionRepository();
        self::assertCount(count($repo->findAll()), $repo->findBy([]));
    }

    private function getUserConnectionRepository(): UserConnectionRepository
    {
        $repo = $this->getContainer()->get(UserConnectionRepository::class);
        $this->assertInstanceOf(UserConnectionRepository::class, $repo);

        return $repo;
    }
}
