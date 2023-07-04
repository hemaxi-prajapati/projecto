<?php

namespace App\Repository;

use App\Entity\OtpAuthentication;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OtpAuthentication>
 *
 * @method OtpAuthentication|null find($id, $lockMode = null, $lockVersion = null)
 * @method OtpAuthentication|null findOneBy(array $criteria, array $orderBy = null)
 * @method OtpAuthentication[]    findAll()
 * @method OtpAuthentication[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OtpAuthenticationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OtpAuthentication::class);
    }

    public function save(OtpAuthentication $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(OtpAuthentication $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return OtpAuthentication[] Returns an array of OtpAuthentication objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?OtpAuthentication
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
