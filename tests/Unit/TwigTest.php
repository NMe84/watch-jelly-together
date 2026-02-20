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
use App\Twig\AppExtension;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigTest extends KernelTestCase
{
    public function testFilters(): void
    {
        $extension = $this->getExtension();

        /** @var TwigFilter[] $filters */
        $filters = array_reduce($extension->getFilters(), function (array $filters, TwigFilter $filter) {
            $filters[$filter->getName()] = $filter;

            return $filters;
        }, []);

        $this->assertEmpty($filters);
    }

    public function testFunctions(): void
    {
        $extension = $this->getExtension();

        /** @var TwigFunction[] $functions */
        $functions = array_reduce($extension->getFunctions(), function (array $functions, TwigFunction $function) {
            $functions[$function->getName()] = $function;

            return $functions;
        }, []);

        $this->assertIsCallable($linkShow = $functions['jellyfin_show_link']->getCallable());
        $this->assertSame(
            sprintf('%s/web/#/details?id=%s&serverId=%s', 'http://localhost:8096', '123', '456'),
            $linkShow(new Show('123', '456'))
        );

        $this->expectException(\TypeError::class);
        $linkShow($this);
    }

    public function testGlobals(): void
    {
        $extension = $this->getExtension();
        $globals = $extension->getGlobals();

        $this->assertArrayHasKey('jellyfin_base_url', $globals);
        $this->assertIsString($globals['jellyfin_base_url']);
        $this->assertSame('http://localhost:8096', $globals['jellyfin_base_url']);
    }

    private function getExtension(): AppExtension
    {
        $extension = $this->getContainer()->get(AppExtension::class);
        $this->assertInstanceOf(AppExtension::class, $extension);

        return $extension;
    }
}
