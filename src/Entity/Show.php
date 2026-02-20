<?php

/*
 * WatchJellyTogether
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity, UniqueEntity('externalId')]
class Show
{
    #[ORM\Column, ORM\Id]
    private string $id;

    #[ORM\Column]
    private string $serverId;

    #[ORM\Column]
    #[Assert\NotBlank]
    private string $name;

    public function __construct(string $id, string $serverId = '', string $name = '')
    {
        $this->id = $id;
        $this->serverId = $serverId;
        $this->name = $name;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getServerId(): string
    {
        return $this->serverId;
    }

    public function setServerId(string $serverId): static
    {
        $this->serverId = $serverId;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }
}
