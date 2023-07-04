<?php

namespace App\Repository;

use App\Entity\Meetings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder as ORMQueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Meetings>
 *
 * @method Meetings|null find($id, $lockMode = null, $lockVersion = null)
 * @method Meetings|null findOneBy(array $criteria, array $orderBy = null)
 * @method Meetings[]    findAll()
 * @method Meetings[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MeetingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Meetings::class);
    }

    public function save(Meetings $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Meetings $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function findByCreatedUserQueryBuilder($userId): ORMQueryBuilder
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.createdBy = :uId')
            ->setParameter('uId', $userId)
            ->orderBy('m.id', 'ASC');
    }

    //    /**
    //     * @return Meetings[] Returns an array of Meetings objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }


    //    public function findOneBySomeField($value): ?Meetings
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
