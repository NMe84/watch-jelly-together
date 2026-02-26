<?php

/*
 * WatchJellyTogether
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Show;
use App\Entity\UserConnection;
use App\Messenger\Message\SyncMasterDataMessage;
use App\Tests\Story\AppStory;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Zenstruck\Foundry\Attribute\WithStory;

#[WithStory(AppStory::class)]
class UserConnectionTest extends FunctionalTestCase
{
    #[DataProvider('publicUrlProvider')]
    public function testPublicPagesAreAccessible(string $url): void
    {
        $this->client->request('GET', $url);
        $response = $this->getClientResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    #[DataProvider('nonExistentUrlProvider')]
    public function testNonExistentUrlGives404(string $url): void
    {
        $this->client->request('GET', $url);
        $response = $this->getClientResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[DataProvider('wrongMethodUrlProvider')]
    public function testWrongMethodUrlGives405(string $url): void
    {
        $this->client->request('GET', $url);
        $response = $this->getClientResponse();
        $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
    }

    public function testCreatingConnectionSucceeds(): void
    {
        $oldCount = $this->getEntityManager()->getRepository(UserConnection::class)->count();

        $crawler = $this->client->request('GET', '/connections/create');
        $form = $crawler->selectButton('Save')->form();
        $form['user_connection[show]'] = 'A';
        $form['user_connection[users]'] = ['X', 'Y'];
        $this->client->submit($form);

        $response = $this->getClientResponse();
        $this->assertTrue($response->isRedirect('/connections'));
        $this->assertEquals($oldCount + 1, $this->getEntityManager()->getRepository(UserConnection::class)->count());
    }

    public function testEditingConnectionSucceeds(): void
    {
        $connection = $this->getEntityManager()->getRepository(UserConnection::class)->find(2);
        self::assertInstanceOf(UserConnection::class, $connection);
        self::assertInstanceOf(Show::class, $connection->getShow());
        self::assertEquals('Show B', $connection->getShow()->getName());

        $crawler = $this->client->request('GET', '/connections/2/edit');
        $form = $crawler->selectButton('Save')->form();
        $form['user_connection[show]'] = 'A';
        $this->client->submit($form);

        $response = $this->getClientResponse();
        $this->assertTrue($response->isRedirect('/connections'));

        $this->getEntityManager()->clear();
        $connection = $this->getEntityManager()->getRepository(UserConnection::class)->find(2);
        self::assertInstanceOf(UserConnection::class, $connection);
        self::assertInstanceOf(Show::class, $connection->getShow());
        self::assertEquals('Show A', $connection->getShow()->getName());
    }

    public function testDeletingConnectionSucceeds(): void
    {
        $oldCount = $this->getEntityManager()->getRepository(UserConnection::class)->count();

        $this->client->request('POST', '/connections/1/delete');

        $response = $this->getClientResponse();
        $this->assertTrue($response->isRedirect('/connections'));
        $this->assertEquals(max(0, $oldCount - 1), $this->getEntityManager()->getRepository(UserConnection::class)->count());
    }

    public function testSyncMasterdataQueuesMessage(): void
    {
        $this->client->request('POST', '/connections/sync-masterdata');

        $response = $this->getClientResponse();
        $this->assertTrue($response->isRedirect('/connections'));

        $messages = iterator_to_array($this->getMessageTransport()->get());
        $this->assertCount(1, $messages);
        $message = $messages[0]->getMessage();
        $this->assertInstanceOf(SyncMasterDataMessage::class, $message);
    }

    /** @return iterable<string, string[]> */
    public static function publicUrlProvider(): iterable
    {
        return static::mapFlatList([
            '/connections',
            '/connections/create',
            '/connections/2/edit',
        ]);
    }

    /** @return iterable<string, string[]> */
    public static function nonExistentUrlProvider(): iterable
    {
        return [
            'edit non-existing connection' => ['/connections/999/edit'],
        ];
    }

    /** @return iterable<string, string[]> */
    public static function wrongMethodUrlProvider(): iterable
    {
        return [
            'delete non-existing connection' => ['/connections/999/delete'],
        ];
    }

    private function getEntityManager(): EntityManagerInterface
    {
        $em = $this->getContainer()->get(EntityManagerInterface::class);
        $this->assertInstanceOf(EntityManagerInterface::class, $em);

        return $em;
    }

    private function getMessageTransport(): TransportInterface
    {
        $transport = self::getContainer()->get('messenger.transport.async');
        $this->assertInstanceOf(TransportInterface::class, $transport);

        return $transport;
    }
}
