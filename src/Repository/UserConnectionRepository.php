<?php

/*
 * WatchJellyTogether
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Repository;

use App\Entity\UserConnection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<UserConnection> */
class UserConnectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserConnection::class);
    }

    /** @return UserConnection[] */
    public function findAll(): array
    {
        /** @var UserConnection[] $connections */
        $connections = $this
            ->createQueryBuilder('uc')
            ->join('uc.show', 's')
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        return $connections;
    }
}
