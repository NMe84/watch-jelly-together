<?php

/*
 * WatchJellyTogether
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\ExceptionDataCollector;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Component\Translation\DataCollectorTranslator;

abstract class FunctionalTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    /** @var array<string, mixed>[] */
    private static array $translationMessages = [];

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->client->disableReboot();
        self::$translationMessages = [];
    }

    protected function tearDown(): void
    {
        /** @var DataCollectorTranslator $collector */
        $collector = $this->getContainer()->get('translator.data_collector');
        /** @var array{id: string, state: int, domain: string, locale: string} $message */
        foreach ($collector->getCollectedMessages() as $message) {
            if (DataCollectorTranslator::MESSAGE_DEFINED !== $message['state']) {
                $messageId = md5(serialize([$message['locale'], $message['domain'], $message['id']]));
                self::$translationMessages[$messageId] = $message;
            }
        }

        parent::tearDown();
    }

    public static function tearDownAfterClass(): void
    {
        if (!empty(self::$translationMessages)) {
            usort(self::$translationMessages, fn (array $a, array $b) => $a['domain'] <=> $b['domain'] ?: $a['locale'] <=> $b['locale'] ?: $a['id'] <=> $b['id']);

            $output = new ConsoleOutput();
            $output->writeln(['', '<comment>Missing translations:</comment>', '']);
            $table = new Table($output);
            $table->setHeaders(['Domain', 'Locale', 'Id', 'Had fallback']);
            foreach (self::$translationMessages as $message) {
                $table->addRow([$message['domain'], $message['locale'], $message['id'], $message['fallbackLocale']]);
            }
            $table->render();
        }

        parent::tearDownAfterClass();
    }

    protected function getClientResponse(bool $expectException = false): Response
    {
        $response = $this->client->getResponse();
        if ($response->isServerError() && !$expectException) {
            $this->assertInstanceOf(Profile::class, $profile = $this->client->getProfile());
            $this->assertInstanceOf(ExceptionDataCollector::class, $collector = $profile->getCollector('exception'));

            $exception = $collector->getException();
            $message = $exception->getMessage();
            while ($exception) {
                if ($exception instanceof FlattenException) {
                    $message .= " ==> {$exception->getAsString()}\n\n";
                }
                $exception = $exception->getPrevious();
            }
            $this->fail($message);
        }

        return $response;
    }

    /**
     * @param string[] $list
     * @return iterable<string, string[]>
     */
    protected static function mapFlatList(array $list): iterable
    {
        return array_combine($list, array_map(fn ($arr) => [$arr], $list));
    }
}
