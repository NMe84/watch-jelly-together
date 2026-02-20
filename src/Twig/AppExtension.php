<?php

/*
 * WatchJellyTogether
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Show;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    use ClockAwareTrait;

    public function __construct(
        #[Autowire(env: 'JELLYFIN_URL')] private readonly string $jellyfinUrl,
    ) {
    }

    /** @return array<string, mixed> */
    public function getGlobals(): array
    {
        return [
            'jellyfin_base_url' => $this->jellyfinUrl,
        ];
    }

    /** @return TwigFilter[] */
    public function getFilters(): array
    {
        return [];
    }

    /** @return TwigFunction[] */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('jellyfin_show_link', fn (Show $show) => sprintf('%s/web/#/details?id=%s&serverId=%s', $this->jellyfinUrl, $show->getId(), $show->getServerId())),
        ];
    }
}
