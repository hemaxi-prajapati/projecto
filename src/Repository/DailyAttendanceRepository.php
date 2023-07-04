<?php

namespace App\Repository;

use App\Entity\DailyAttendance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DailyAttendance>
 *
 * @method DailyAttendance|null find($id, $lockMode = null, $lockVersion = null)
 * @method DailyAttendance|null findOneBy(array $criteria, array $orderBy = null)
 * @method DailyAttendance[]    findAll()
 * @method DailyAttendance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DailyAttendanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DailyAttendance::class);
    }

    public function save(DailyAttendance $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DailyAttendance $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return DailyAttendance[] Returns an array of DailyAttendance objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?DailyAttendance
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }




    public function findByUserWithCurruntdateQueryBuilder($userId, $date)
    {
        return $this->createQueryBuilder('d')
            ->Select("d.id,d.checkIn,d.checkOut ,d.createdAt as created,d.updatedAt,timediff(TIME(d.checkOut),TIME (d.checkIn)) as totalTimeRow")
            ->Where('d.user = :user')
            ->setParameter('user', $userId)
            ->andWhere(' DATE(d.createdAt) = DATE(:now)')
            ->setParameter('now', $date)
            ->orderBy('d.id', 'DESC');
    }
    public function findAllTimeByUserQueryBuilder()
    {
        return $this->createQueryBuilder('d')

            ->select(" sum(timetosec(timediff(TIME(d.checkOut),TIME (d.checkIn)))) as timer,IDENTITY(d.user) as userId,DATE(d.createdAt) as create")
            ->groupBy('userId')
            ->addGroupBy('create')
            ->orderBy('create ', 'DESC');
    }

    public function findByUserWithLastSevenDaaysTotalTime($userId)
    {
        return $this->createQueryBuilder('d')
            ->where('d.user = :user')
            ->setParameter('user', $userId);;
    }


    public function findAllAttendanceByUserQueryBuilder($user)
    {
        return $this->createQueryBuilder('d')
            ->where('d.user = :user')
            ->setParameter('user', $user);
    }
}
