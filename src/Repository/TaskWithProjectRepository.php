<?php

namespace App\Repository;

use App\Entity\ProjectAssignment;
use App\Entity\TaskWithProject;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TaskWithProject>
 *
 * @method TaskWithProject|null find($id, $lockMode = null, $lockVersion = null)
 * @method TaskWithProject|null findOneBy(array $criteria, array $orderBy = null)
 * @method TaskWithProject[]    findAll()
 * @method TaskWithProject[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskWithProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskWithProject::class);
    }

    public function save(TaskWithProject $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TaskWithProject $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }



    public function findAllTaskForProjectQueryBuilder($pid)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.project = :pid')
            ->setParameter('pid', $pid)
            ->orderBy('t.id', 'ASC');
    }
    public function findTaskWithAllDetails($project): array
    {
        return ($this->createQueryBuilder('p')
            ->select('task', 'pa', 'pd', 'user')
            ->from(TaskWithProject::class, 'task')
            ->join('task.project', 'pd')
            ->andWhere('pd.id = :project')
            ->setParameter('project', $project)
            ->getQuery()
            ->getResult());
    }

    public function findAllTaskForProjectManagerQueryBuilder($pmid)
    {
        return $this->createQueryBuilder('t')
            ->join('t.project', 'pd')
            ->andWhere('pd.ProjectManager = :pmid')
            ->setParameter('pmid', $pmid)
            ->orderBy('t.id', 'ASC');
    }
    public function findAllTaskForProjectManagerWithStatusCountQueryBuilder($pmid)
    {
        return $this->createQueryBuilder('t')
            ->select("count(t.id),t.Status")
            ->join('t.project', 'pd')
            ->andWhere('pd.ProjectManager = :pmid')
            ->setParameter('pmid', $pmid)
            ->groupBy("t.Status");
    }
    public function findByNowDate()
    {
        return $this->createQueryBuilder('t')
            ->Where('DATE_DIFF(t.ActualEndDate,:currentDate)<7')
            ->setParameter('currentDate', new \DateTime('now'))
            ->andWhere('t.Status = :status')
            ->setParameter('status', TaskWithProject::TASK_STATUS_IN_PROGRESS)
            ->orderBy('t.ActualEndDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
    //    /**
    //     * @return TaskWithProject[] Returns an array of TaskWithProject objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    public function findTaskWithAllDetailsQuerybuilder($project): QueryBuilder
    {
        return $this->createQueryBuilder('task')
            // ->join('task.ProjectAssignment', 'pa')
            ->join('task.project', 'pd')
            ->andWhere('pd.id = :project')
            ->setParameter('project', $project)
            // ->join('pa.User', 'user')
            // ->andWhere('user.status = :status')
            // ->setParameter('status', User::USER_STATUS_ACTIIVE)
        ;
    }
    // public function NeedAprovelEmployee()
    // {
    //     return ( $this->createQueryBuilder('p')
    //         ->addSelect('project_assign', 'task_with_project')
    //         ->from(TaskWithProject::class, 'task_with_project')
    //         ->andWhere('project_assign.Status = :val')
    //         ->setParameter('val', "yet to assign")
    //         ->join('task_with_project.ProjectAssignment',"project_assign")
    //         ->groupBy('task_with_project.ProjectAssignment')
    //         ->orderBy('project_assign.id', 'ASC')
    //         ->getQuery()
    //         ->getResult());
    // }


    // public function NeedAprovelEmployee()
    // {
    //     return ( $this->createQueryBuilder('twp')
    //         ->join('twp.ProjectAssignment',"pa")
    //         ->andWhere('pa.Status = :val')
    //         ->setParameter('val', ProjectAssignment::USER_TASK_STATUS_YET_TO_ASSIGN)
    //         ->orderBy('pa.id', 'ASC')
    //         ->getQuery()
    //         ->getResult());
    // }

    //    public function findOneBySomeField($value): ?TaskWithProject
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findTaskWithProjectAndUser($id, $user)
    {
        return ($this->createQueryBuilder('task')
            // ->select('pa')
            // ->join('task.ProjectAssignment', 'pa')
            ->andWhere('task.project = :project')
            ->setParameter('project', $id)
            ->andWhere(':user MEMBER OF task.users ')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        );
    }

    public function findAllTaskForEmployeeQueryBuilder($id)
    {
        return ($this->createQueryBuilder('task')
            ->andWhere(':user MEMBER OF task.users ')
            ->setParameter('user', $id)

        );
    }
    public function findAllTaskForUserWithStatusCountQueryBuilder($User)
    {
        return $this->createQueryBuilder('t')
            ->select("COUNT(t.id), t.Status")
            ->join('t.users', 'u')
            ->where('u = :user')
            ->setParameter('user', $User->getId())
            ->groupBy("t.Status");
    }


    public function changeTaskStatus($id, $type)
    {
        $queryBuilder = $this->createQueryBuilder('tp');
        $query = $queryBuilder->update('TaskWithProject', 'tp')
            ->set('tp.priority', ':priority')
            ->where('tp.id = :id')
            ->setParameter('priority', $type)
            ->setParameter('id', $id)
            ->getQuery();
        $result = $query->execute();
    }

    public function findTaskDetails($id)
    {
        return ($this->createQueryBuilder('task')
            ->where('task.id =:id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult()
        );
    }
}
