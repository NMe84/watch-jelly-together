<?php

/*
 * WatchJellyTogether
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class UserConnection
{
    #[ORM\Column, ORM\Id, ORM\GeneratedValue]
    private int $id;

    /** @var Collection<string, User> */
    #[ORM\ManyToMany(targetEntity: User::class, cascade: ['persist'], fetch: 'EAGER', orphanRemoval: true, indexBy: 'id')]
    private Collection $users;

    #[ORM\ManyToOne, ORM\JoinColumn(nullable: false)]
    private Show $show;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    /** @return Collection<string, User> */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /** @param Collection<string, User> $users */
    public function setUsers(Collection $users): static
    {
        $this->users = $users;

        return $this;
    }

    public function addUser(User $user): static
    {
        $this->users->add($user);

        return $this;
    }

    public function removeUser(User $user): static
    {
        $this->users->removeElement($user);

        return $this;
    }

    public function getShow(): ?Show
    {
        return $this->show;
    }

    public function setShow(Show $show): static
    {
        $this->show = $show;

        return $this;
    }
}
