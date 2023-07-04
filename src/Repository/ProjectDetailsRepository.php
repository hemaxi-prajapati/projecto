<?php

namespace App\Repository;

use App\Entity\ProjectDetails;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectDetails>
 *
 * @method ProjectDetails|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjectDetails|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjectDetails[]    findAll()
 * @method ProjectDetails[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectDetailsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectDetails::class);
    }

    public function save(ProjectDetails $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProjectDetails $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }





    //Returns an array of ProjectDetails objects whose status match and belongs to given project manager
    public function findProjectsByStatusAndProjectManagerQueryBuilder($uid, $status): QueryBuilder
    {
        return $this->createQueryBuilder('pd')
            ->andWhere('pd.ProjectManager = :uid')
            ->setParameter('uid', $uid)
            ->andWhere('pd.Status = :status')
            ->setParameter('status', $status)
            ->orderBy('pd.id', 'DESC');
    }

    public function findAllProjectsQueryBuilder($user): ?QueryBuilder
    {

        $queryBuilder = $this->createQueryBuilder('pd')
            ->andWhere('pd.ProjectManager = :user')
            ->setParameter('user', $user)
            ->orderBy('pd.id', 'DESC');
        return $queryBuilder;
    }
    public function findAllProjectsWithCountStatusQueryBuilder($user): ?QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select('p.Status, COUNT(p.id) ')
            ->andWhere('p.ProjectManager = :projectManager')
            ->setParameter('projectManager', $user->getID())
            ->groupBy('p.Status')
            ;
        return $queryBuilder;
    }
    public function findAllProjectsWithCountStatusWithDepatmentQueryBuilder($department): ?QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('pd')
            ->select("count(DISTINCT pd.id),pd.Status")
            ->leftJoin('pd.projectAssignments', 'pa')
            ->leftjoin('pa.User', 'u')
            ->where('u.Department= :dept')
            ->setParameter('dept', $department)
            ->groupBy("pd.Status")
            ;
        return $queryBuilder;
    }
    public function findAllProjectsByAnyTypeUserQueryBuilder($user, $projectID): ?QueryBuilder
    {

        $queryBuilder = $this->createQueryBuilder('pd')
            ->andWhere('pd.ProjectManager = :user')
            ->setParameter('user', $user)
            ->andWhere('pd.id = :pId')
            ->setParameter('pId', $projectID);
        return $queryBuilder;
    }

    //    public function findOneBySomeField($value): ?ProjectDetails
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }



    // public function findAssignedProjectsByStatus($user, $stauts){
    //     $queryBuilder = $this->createQueryBuilder('pd')
    //         // ->select('u', 'pd')
    //         ->join('pd.projectAssignments', 'pa')
    //         ->join('pa.User', 'u')
    //         ->where('pa.User= :user')
    //         ->andWhere('pd.Status = :status')
    //         ->setParameter('user', $user)
    //         ->setParameter('status', $stauts);

    //     return $queryBuilder->getQuery()->getResult();
    // }
}
