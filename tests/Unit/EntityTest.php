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
use App\Tests\Story\AppStory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Attribute\WithStory;

#[WithStory(AppStory::class)]
class EntityTest extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
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

        $newShow = new Show('C', '3', 'Show C');
        self::assertEquals('C', $newShow->getId());
        self::assertEquals('3', $newShow->getServerId());
        self::assertEquals('Show C', $newShow->getName());
        $newShow
            ->setId('D')
            ->setServerId('4')
            ->setName('Show D')
        ;
        self::assertEquals('D', $newShow->getId());
        self::assertEquals('4', $newShow->getServerId());
        self::assertEquals('Show D', $newShow->getName());
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

        $newUser = new User('Z', 'User Z');
        self::assertEquals('Z', $newUser->getId());
        self::assertEquals('User Z', $newUser->getName());
        $newUser
            ->setName('User N')
            ->setId('N')
        ;
        self::assertEquals('N', $newUser->getId());
        self::assertEquals('User N', $newUser->getName());
    }

    private function getEntityManager(): EntityManagerInterface
    {
        $em = $this->getContainer()->get(EntityManagerInterface::class);
        $this->assertInstanceOf(EntityManagerInterface::class, $em);

        return $em;
    }
}
