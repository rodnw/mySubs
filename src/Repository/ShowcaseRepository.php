<?php

namespace App\Repository;

use App\Entity\Showcase;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Showcase>
 */
class ShowcaseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Showcase::class);
    }

    /**
     * @return Showcase[]
     */
    public function findPublished(?int $limit = null): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.published = true')
            ->orderBy('s.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
