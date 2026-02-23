<?php

/*
 * WatchJellyTogether
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

use App\Entity\Show;
use App\Entity\User;
use App\Jellyfin\Connector;
use App\Tests\Story\AppStory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Zenstruck\Foundry\Attribute\WithStory;

#[WithStory(AppStory::class)]
class ConnectorTest extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
    }

    public function testGetShows(): void
    {
        $shows = $this->getConnector()->getShows();
        self::assertCount(2, $shows);
        $showIds = array_map(fn (Show $show) => $show->getId(), $shows);
        self::assertContains('A', $showIds);
        self::assertContains('B', $showIds);
    }

    public function testGetUsers(): void
    {
        $users = $this->getConnector()->getUsers();
        self::assertCount(2, $users);
        $userIds = array_map(fn (User $user) => $user->getId(), $users);
        self::assertContains('X', $userIds);
        self::assertContains('Y', $userIds);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testRefreshShowsCreatesNewShow(): void
    {
        $showId = '123';
        $responseData = [
            'Items' => [
                [
                    'Id' => $showId,
                    'ServerId' => 'abc',
                    'Name' => 'Test Show',
                ],
            ],
        ];

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock
            ->method('getContent')
            ->willReturn(json_encode($responseData))
        ;

        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $httpClientMock
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                '/Items',
                [
                    'query' => [
                        'IncludeItemTypes' => 'Series',
                        'Recursive' => 'true',
                    ],
                ],
            )
            ->willReturn($responseMock)
        ;

        $repositoryMock = $this->createMock(EntityRepository::class);
        $repositoryMock
            ->expects($this->once())
            ->method('find')
            ->with($showId)
            ->willReturn(null)
        ;

        $emMock = $this->createMock(EntityManagerInterface::class);
        $emMock
            ->method('getRepository')
            ->willReturn($repositoryMock)
        ;

        $emMock
            ->expects($this->once())
            ->method('persist')
        ;

        $emMock
            ->expects($this->atLeastOnce())
            ->method('flush')
        ;

        $service = new Connector(
            'http://localhost:8096',
            '123',
            $emMock,
            $this->createMock(LoggerInterface::class),
            $httpClientMock,
        );

        $service->refreshShows();
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testGetUserEpisodesReturnsEpisodes(): void
    {
        $user = new User('user-1', 'Test User');
        $show = new Show('show-1', 'server-1', 'Test Show');

        $apiResponse = [
            'Items' => [
                [
                    'Id' => 'ep-1',
                    'Name' => 'Episode 1',
                    'UserData' => ['Played' => true],
                ],
                [
                    'Id' => 'ep-2',
                    'Name' => 'Episode 2',
                    'UserData' => ['Played' => false],
                ],
            ],
        ];

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock
            ->method('getContent')
            ->willReturn(json_encode($apiResponse))
        ;

        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $httpClientMock
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                sprintf('/Users/%s/Items', $user->getId()),
                [
                    'query' => [
                        'IncludeItemTypes' => 'Episode',
                        'ParentId' => $show->getId(),
                        'Recursive' => 'true',
                    ],
                ]
            )
            ->willReturn($responseMock)
        ;

        $emMock = $this->createMock(EntityManagerInterface::class);

        $connector = new Connector(
            'http://localhost:8096',
            '123',
            $emMock,
            $this->createMock(LoggerInterface::class),
            $httpClientMock,
        );

        $result = $connector->getUserEpisodes($user, $show);

        $this->assertCount(2, $result);
        $this->assertSame('ep-1', $result[0]['Id']);
        $this->assertTrue($result[0]['UserData']['Played']);
        $this->assertFalse($result[1]['UserData']['Played']);
    }

    private function getConnector(): Connector
    {
        $connector = $this->getContainer()->get(Connector::class);
        $this->assertInstanceOf(Connector::class, $connector);

        return $connector;
    }
}
