<?php

/*
 * WatchJellyTogether
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Tests\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;

class AppTest extends FunctionalTestCase
{
    #[DataProvider('publicUrlProvider')]
    public function testPublicPagesAreAccessible(string $url): void
    {
        $this->client->request('GET', $url);
        $response = $this->getClientResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    /** @return iterable<string, string[]> */
    public static function publicUrlProvider(): iterable
    {
        return static::mapFlatList([
            '/',
            '/connections',
        ]);
    }

    #[DataProvider('unwantedUrlProvider')]
    public function testUnwantedUrlGives404(string $url): void
    {
        $this->client->request('GET', $url);
        $response = $this->getClientResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    /** @return iterable<string, string[]> */
    public static function unwantedUrlProvider(): iterable
    {
        return [
            'current timestamp' => ['/' . time()],
            'random md5' => ['/' . md5(strval(time()))],
            'random sha1' => ['/' . sha1(strval(time()))],
            'WordPress Admin' => ['/wp-admin'],
            'WordPress XML RPC' => ['/xmlrpc.php'],
            'commonly used admin path' => ['/admin'],
        ];
    }
}
