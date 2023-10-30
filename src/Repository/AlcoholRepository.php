<?php

namespace App\Repository;

use App\Entity\Alcohol;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Alcohol>
 *
 * @method Alcohol|null find($id, $lockMode = null, $lockVersion = null)
 * @method Alcohol|null findOneBy(array $criteria, array $orderBy = null)
 * @method Alcohol[]    findAll()
 * @method Alcohol[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AlcoholRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Alcohol::class);
    }

    public function save(Alcohol $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Alcohol $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function paginate(Query $query, int $page = 1, int $perPage = 25): array
    {
        $paginator = new Paginator($query);
        $total = $paginator->count();

        return [
            'total' => $total,
            'items' => $paginator
                ->getQuery()
                ->setFirstResult(($page - 1) * $perPage)
                ->setMaxResults($perPage)
                ->getResult()
        ];
    }

    public function findAllWithOptionalFilters(
        string $name = null,
        string $type = null,
        int $page = 1,
        int $perPage = 25
    ): array {
        $qb = $this->createQueryBuilder('a');

        if ($name) {
            $qb->andWhere('lower(a.name) LIKE :name')
                ->setParameter('name', '%' . strtolower($name) . '%');
        }
        if ($type) {
            $qb->andWhere('a.type = :type')
                ->setParameter('type', $type);
        }

        $query = $qb->getQuery();
        return $this->paginate($query, $page, $perPage);
    }

    //    /**
    //     * @return Alcohol[] Returns an array of Alcohol objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Alcohol
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
