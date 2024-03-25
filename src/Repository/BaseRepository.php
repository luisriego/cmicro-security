<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

abstract class BaseRepository extends ServiceEntityRepository
{
    protected string $entityClass;

    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        $this->entityClass = $entityClass;
        parent::__construct($registry, $entityClass);
    }
}
