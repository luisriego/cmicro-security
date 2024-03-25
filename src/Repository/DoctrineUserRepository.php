<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Exception\ResourceNotFoundException;
use App\IRepository\UserRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

use function sprintf;

class DoctrineUserRepository extends BaseRepository implements PasswordUpgraderInterface, UserRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function add(User $user, bool $flush = false): void
    {
        $this->getEntityManager()->persist($user);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function save(User $user, bool $flush = false): void
    {
        $this->getEntityManager()->persist($user);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $user, bool $flush = false): void
    {
        $this->getEntityManager()->remove($user);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);

        $this->add($user, true);
    }

    public function findOneByIdOrFail(string $id): User
    {
        if (null === $user = $this->find($id)) {
            throw ResourceNotFoundException::createFromClassAndId(User::class, $id);
        }

        // @var User $user
        return $user;
    }

    public function findOneByEmail(string $email): ?User
    {
        /** @var User $user */
        $user = $this->findOneBy(['email' => $email]);

        return $user;
    }

    public function findOneByEmailOrFail(string $email): User
    {
        if (null === $user = $this->find($email)) {
            throw ResourceNotFoundException::createFromClassAndEmail(User::class, $email);
        }

        // @var User $user
        return $user;
    }

    //    public function findAllByCondoId(string $condoId): ?array
    //    {
    //        $result = $this->createQueryBuilder('u')
    //            ->andWhere('u.condo = :val')
    //            ->setParameter('val', $condoId)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //
    //        return $result;
    //    }
    //
    //    public function search(UserFilter $filter): PaginatedResponse
    //    {
    //        $page = $filter->page;
    //        $limit = $filter->limit;
    //        $condoId = $filter->condoId;
    //        $sort = $filter->sort;
    //        $order = $filter->order;
    //        $name = $filter->name;
    //
    //        if ('' === $sort) {
    //            $sort = 'name';
    //        }
    //        if ('' === $order) {
    //            $order = 'desc';
    //        }
    //
    //        $qb = $this->repository->createQueryBuilder('u');
    //        $qb->orderBy(\sprintf('u.%s', $sort), $order);
    //        $qb
    //            ->andWhere('u.condo = :condoId')
    //            ->setParameter(':condoId', $condoId);
    //
    //        if (null !== $name) {
    //            $qb
    //                ->andWhere('u.name LIKE :name')
    //                ->setParameter(':name', $name.'%');
    //        }
    //
    //        $paginator = new Paginator($qb->getQuery());
    //        $paginator->getQuery()
    //            ->setFirstResult($limit * ($page - 1))
    //            ->setMaxResults($limit);
    //
    //        return PaginatedResponse::create($paginator->getIterator()->getArrayCopy(), $paginator->count(), $page, $limit);
    //    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
