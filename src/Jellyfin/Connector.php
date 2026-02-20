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

readonly class Connector
{
    public function __construct(
        #[Autowire(env: 'default:app.jellyfin.url:JELLYFIN_URL')] private string $baseUrl,
        #[Autowire(env: 'JELLYFIN_API_KEY'), \SensitiveParameter] private string $apiKey,
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
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
            $client = $this->getClient();
            $response = $client->request(Request::METHOD_GET, '/Users');

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
            $client = $this->getClient();
            $response = $client->request(Request::METHOD_GET, '/Items', [
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

    private function getClient(): HttpClientInterface
    {
        return HttpClient::createForBaseUri($this->baseUrl, [
            'headers' => [
                'Accept' => 'application/json',
                'X-MediaBrowser-Token' => $this->apiKey,
            ],
        ]);
    }
}
