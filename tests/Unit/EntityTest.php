<?php

/*
 * WatchJellyTogether
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Show;
use App\Entity\User;
use App\Story\AppStory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class EntityTest extends KernelTestCase
{
    use ResetDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
        AppStory::load();
    }

    public function testShow(): void
    {
        $em = $this->getEntityManager();

        $showA = $em->getRepository(Show::class)->find('A');
        self::assertInstanceOf(Show::class, $showA);
        self::assertEquals('A', $showA->getId());
        self::assertEquals('Show A', $showA->getName());
        self::assertEquals('1', $showA->getServerId());

        $nonExistentShow = $em->getRepository(Show::class)->find('NonExistent');
        self::assertNull($nonExistentShow);
    }

    public function testUser(): void
    {
        $em = $this->getEntityManager();

        $userX = $em->getRepository(User::class)->find('X');
        self::assertInstanceOf(User::class, $userX);
        self::assertEquals('X', $userX->getId());
        self::assertEquals('User X', $userX->getName());

        $nonExistentUser = $em->getRepository(User::class)->find('NonExistent');
        self::assertNull($nonExistentUser);
    }

    private function getEntityManager(): EntityManagerInterface
    {
        $em = $this->getContainer()->get(EntityManagerInterface::class);
        $this->assertInstanceOf(EntityManagerInterface::class, $em);

        return $em;
    }
}
