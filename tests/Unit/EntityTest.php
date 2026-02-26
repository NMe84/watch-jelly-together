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
use App\Entity\UserConnection;
use App\Tests\Story\AppStory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zenstruck\Foundry\Attribute\WithStory;

#[WithStory(AppStory::class)]
class EntityTest extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
    }

    public function testShowProperties(): void
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
        self::assertEquals('Show D', (string) $newShow);
    }

    public function testUserProperties(): void
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
        self::assertEquals('User N', (string) $newUser);
    }

    public function testUserConnectionProperties(): void
    {
        $em = $this->getEntityManager();

        $userConnection = $em->getRepository(UserConnection::class)->find(1);
        self::assertInstanceOf(UserConnection::class, $userConnection);
        self::assertEquals(1, $userConnection->getId());
        self::assertCount(2, $userConnection->getUsers());
        self::assertInstanceOf(Show::class, $userConnection->getShow());
        self::assertEquals('Show A', $userConnection->getShow()->getName());

        $userConnection = $userConnection->setUsers(new ArrayCollection([]));
        self::assertCount(0, $userConnection->getUsers());
    }

    public function testUserConnectionValidation(): void
    {
        $user1 = new User('uc1', 'User UC1');
        $user2 = new User('uc2', 'User UC2');
        $show = new Show('s1', 'srv1', 'Show S1');

        $userConnection = new UserConnection();
        $userConnection->setShow($show);
        $userConnection->addUser($user1);

        $validator = $this->getValidator();
        $violations = $validator->validate($userConnection);
        self::assertCount(1, $violations);

        $userConnection->addUser($user2);
        $violations = $validator->validate($userConnection);
        self::assertCount(0, $violations);

        $userConnection->removeUser($user1);
        $violations = $validator->validate($userConnection);
        self::assertCount(1, $violations);
    }

    private function getEntityManager(): EntityManagerInterface
    {
        $em = $this->getContainer()->get(EntityManagerInterface::class);
        $this->assertInstanceOf(EntityManagerInterface::class, $em);

        return $em;
    }

    private function getValidator(): ValidatorInterface
    {
        $validator = $this->getContainer()->get('validator');
        $this->assertInstanceOf(ValidatorInterface::class, $validator);

        return $validator;
    }
}
