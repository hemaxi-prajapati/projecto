<?php

namespace App\Repository;

use App\Entity\ProjectAssignment;
use App\Entity\User;
use App\Repository\ProjectDetailsRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends ServiceEntityRepository<ProjectAssignment>
 *
 * @method ProjectAssignment|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjectAssignment|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjectAssignment[]    findAll()
 * @method ProjectAssignment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectAssignmentRepository extends ServiceEntityRepository
{
    public function __construct(private ManagerRegistry $registry, private EntityManagerInterface $em)
    {
        parent::__construct($registry, ProjectAssignment::class);
    }

    public function save(ProjectAssignment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProjectAssignment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findPAIdfromUidPid($pid, $uid): ProjectAssignment
    {
        return $this->createQueryBuilder('pa')
            ->andWhere('pa.Project= :pid')
            ->setParameter('pid', $pid)
            ->andWhere('pa.User = :uid')
            ->setParameter('uid', $uid)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllProjectsWithCountStatusWithDepatmentQueryBuilder($department): ?QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('pa')
            ->select("count( DISTINCT pd.id),pd.Status")
            ->leftJoin('pd.projectAssignments', 'pa')
            ->leftjoin('pa.User', 'u')
            ->where('u.Department= :dept')
            ->setParameter('dept', $department)
            ->groupBy("pd.Status");
        return $queryBuilder;
    }

    // to find if user to assigned to any task
    public function findUserAssignedToTask($tid): ?ProjectAssignment
    {

        return $this->createQueryBuilder('pa')
            ->join('pa.taskWithProjects', 'twp')
            ->andWhere('twp.id = :tid')
            ->setParameter('tid', $tid)
            ->getQuery()
            ->getOneOrNullResult();
    }

    //find all user in currrent project
    public function findAllInThisProjectQueryBuilder($projectId)
    {
        $queryBuilder = $this->createQueryBuilder('pa');
        return ($queryBuilder
            ->andwhere('pa.Project =:project')
            ->setParameter('project', $projectId)
            ->Join("pa.User", 'u')
        );
    }


    public function NeedAprovelEmployeeQueryBuilder($departmentId)
    {
        return ($this->createQueryBuilder('pa')
            ->join('pa.User', 'u')
            ->andWhere('u.Department = :de')
            ->setParameter('de', $departmentId)
            ->andWhere('pa.Status = :val')
            ->setParameter('val', ProjectAssignment::USER_TASK_STATUS_YET_TO_ASSIGN)
            ->orderBy('pa.id', 'ASC')
        );
    }

    public function findProjectByDepartmentQuerybuilder($departmentId)
    {
        $queryBuilder = $this->createQueryBuilder('pa')
            ->select('IDENTITY(pa.Project) AS projectId')
            ->join("pa.User", 'u')
            ->join("pa.Project", 'pd')
            ->andWhere('u.Department = :department')
            ->setParameter('department', $departmentId)
            ->groupBy('projectId');
        return $queryBuilder;
    }
    public function findProjectByDepartmentWithCountStatusQuerybuilder($departmentId)
    {
        $queryBuilder = $this->createQueryBuilder('pa')
            ->select('IDENTITY(pa.Project) AS projectId,pa.Status')
            ->join("pa.User", 'u')
            ->join("pa.Project", 'pd')
            ->andWhere('u.Department = :department')
            ->setParameter('department', $departmentId)
            ->groupBy('projectId,pa.Status');
        return $queryBuilder;
    }


    public function findProjectAssignUserWithCountStatusQuerybuilder(User $user)
    {
        $queryBuilder = $this->createQueryBuilder('pa')
            // ->select('Count(pa.id),pa.Status')
            ->select('pd.Status, COUNT(pd.id)')
            ->join('pa.Project', 'pd')
            ->join('pa.User', 'u')
            ->where('pa.User= :user')
            ->setParameter('user', $user)
            ->andWhere('pa.Status != :status')
            ->setParameter('status', ProjectAssignment::USER_TASK_STATUS_YET_TO_ASSIGN)
            ->groupBy('pd.Status');
        return $queryBuilder;
    }

    public function findUsersAssignedToProject($project)
    {
        $queryBuilder = $this->createQueryBuilder('pa')
            ->select('u.Firstname', 'u.LastName', 'pd.id')
            ->join('pa.User', 'u')
            ->join('pa.Project', 'pd')
            ->andWhere("pa.Project = :project")
            ->setParameter('project', $project);
        return $queryBuilder->getQuery()->getResult();;
    }



    //    /**
    //     * @return ProjectAssignment[] Returns an array of ProjectAssignment objects
    //     */   
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ProjectAssignment
    //    {
    //          eturn $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }



    public function findAssignedProject(User $user)
    {
        $queryBuilder = $this->createQueryBuilder('pa')
            ->join('pa.User', 'u')
            ->where('pa.User= :user')
            ->setParameter('user', $user)
            ->andWhere('pa.Status != :status')
            ->setParameter('status', ProjectAssignment::USER_TASK_STATUS_YET_TO_ASSIGN)
            ->join('pa.Project', 'pd');
        return $queryBuilder;
    }


    public function findAssignedProjectsByStatus($user, $status)
    {
        $queryBuilder = $this->createQueryBuilder('pa')
            ->join('pa.User', 'u')
            ->join('pa.Project', 'pd')
            ->where('pa.User= :user')
            ->setParameter('user', $user)
            ->andWhere('pa.Status= :Pastatus')
            ->setParameter('Pastatus', ProjectAssignment::USER_TASK_STATUS_YET_TO_ASSIGN)
            ->andWhere('pd.Status = :status')
            ->setParameter('status', $status);

        return $queryBuilder;
    }
}