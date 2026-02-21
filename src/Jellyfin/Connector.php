<?php

/*
 * WatchJellyTogether
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Jellyfin;

use App\Entity\Show;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Connector
{
    private ?HttpClientInterface $client = null;

    public function __construct(
        #[Autowire(env: 'default:app.jellyfin.url:JELLYFIN_URL')] private readonly string $baseUrl,
        #[Autowire(env: 'JELLYFIN_API_KEY'), \SensitiveParameter] private readonly string $apiKey,
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
    ) {
    }

    /** @return array<int, User> */
    public function getUsers(): array
    {
        $users = $this->em->getRepository(User::class)->findBy([], ['name' => 'ASC']);
        if (!empty($users)) {
            return $users;
        }

        $this->refreshUsers();

        return $this->em->getRepository(User::class)->findBy([], ['name' => 'ASC']);
    }

    public function refreshUsers(): void
    {
        try {
            $response = $this->getClient()->request(Request::METHOD_GET, '/Users');

            /** @var array<int, array{Id: string, Name: string}> $data */
            $data = json_decode($response->getContent(), true);
            foreach ($data as $row) {
                if (!($user = $this->em->getRepository(User::class)->find($row['Id']))) {
                    $user = new User($row['Id'], $row['Name']);
                    $this->em->persist($user);
                }
                $user->setName($row['Name']);
            }
        } catch (\Throwable $e) {
            $this->logger->warning(sprintf('Failed to fetch users from Jellyfin: %s', $e->getMessage()));
        } finally {
            $this->em->flush();
        }
    }

    /** @return array<int, Show> */
    public function getShows(): array
    {
        $shows = $this->em->getRepository(Show::class)->findBy([], ['name' => 'ASC']);
        if (!empty($shows)) {
            return $shows;
        }

        $this->refreshShows();

        return $this->em->getRepository(Show::class)->findBy([], ['name' => 'ASC']);
    }

    public function refreshShows(): void
    {
        try {
            $response = $this->getClient()->request(Request::METHOD_GET, '/Items', [
                'query' => [
                    'IncludeItemTypes' => 'Series',
                    'Recursive' => 'true',
                ],
            ]);

            /** @var array{Items?: array<int, array{Id: string, ServerId: string, Name: string}>} $data */
            $data = json_decode($response->getContent(), true);
            foreach ($data['Items'] ?? [] as $row) {
                if (!($show = $this->em->getRepository(Show::class)->find($row['Id']))) {
                    $show = new Show($row['Id'], $row['ServerId'], $row['Name']);
                    $this->em->persist($show);
                }
                $show
                    ->setName($row['Name'])
                    ->setServerId($row['ServerId'])
                ;

                $this->em->flush();
            }
        } catch (\Throwable $e) {
            $this->logger->warning(sprintf('Failed to fetch tv shows from Jellyfin: %s', $e->getMessage()));
        } finally {
            $this->em->flush();
        }
    }

    /** @param array<int, User> $users */
    public function syncWatchStates(array $users, Show $show): void
    {
        $globalWatchData = $individualWatchData = [];
        foreach ($users as $user) {
            foreach ($this->getUserEpisodes($user, $show) as $episode) {
                $individualWatchData[$user->getId()][$episode['Id']] = (bool) $episode['UserData']['Played'];
                $globalWatchData[$episode['Id']] = ($globalWatchData[$episode['Id']] ?? false) || $episode['UserData']['Played'];
            }
        }

        foreach ($globalWatchData as $episodeId => $isWatched) {
            if ($isWatched) {
                foreach ($users as $user) {
                    if (empty($individualWatchData[$user->getId()][$episodeId] ?? false)) {
                        // Only send the watch state if it wasn't already marked as watched for this user, to avoid unnecessary API calls
                        $this->markPlayed($user->getId(), $episodeId);
                        $this->logger->debug(sprintf('Marked episode %s as watched for user %s', $episodeId, $user->getName()));
                    }
                }
            }
        }
    }

    /** @return array<int, array{Id: string, Name: string, UserData: array{Played: bool}}> */
    public function getUserEpisodes(User $user, Show $show): array
    {
        try {
            $response = $this->getClient()->request(Request::METHOD_GET, sprintf('/Users/%s/Items', $user->getId()), [
                'query' => [
                    'IncludeItemTypes' => 'Episode',
                    'ParentId' => $show->getId(),
                    'Recursive' => 'true',
                ],
            ]);

            /** @var array{Items?: array<int, array{Id: string, Name: string, UserData: array{Played: bool}}>} $data */
            $data = json_decode($response->getContent(), true);

            return $data['Items'] ?? [];
        } catch (\Throwable $e) {
            $this->logger->warning(sprintf('Failed to fetch episodes for user %s and show %s from Jellyfin: %s', $user->getName(), $show->getName(), $e->getMessage()));

            return [];
        }
    }

    public function markPlayed(string $userId, string $itemId): void
    {
        try {
            $this->getClient()->request(Request::METHOD_POST, "/Users/$userId/PlayedItems/$itemId");
        } catch (\Throwable $e) {
            $this->logger->warning(sprintf('Failed to mark item %s as played for user %s: %s', $itemId, $userId, $e->getMessage()));
        }
    }

    private function getClient(): HttpClientInterface
    {
        $this->client ??= HttpClient::createForBaseUri($this->baseUrl, [
            'headers' => [
                'Accept' => 'application/json',
                'X-MediaBrowser-Token' => $this->apiKey,
            ],
        ]);

        return $this->client;
    }
}
