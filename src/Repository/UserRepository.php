<?php

namespace App\Repository;

use App\Entity\ProjectAssignment;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

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
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->save($user, true);
    }

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

    public function findAllEmployeeQueryBuilder(): ?QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->Where("u.isDeleted = :isDelete")
            ->setParameter('isDelete', false)
            ->orderBy('u.id', 'DESC');
            return $queryBuilder;
        }
        
        public function findAllRoleEmployeeQueryBuilder(): ?QueryBuilder
        {
            $queryBuilder = $this->findAllEmployeeQueryBuilder()
            ->andWhere('u.roles  LIKE :userroles')
            ->setParameter('userroles', '%"' . User::ROLE_USER . '"%');
            return $queryBuilder;
        }
        public function findAllRoleEmployeeWithCountStatusQueryBuilder(): ?QueryBuilder
        {
        $queryBuilder = $this->createQueryBuilder('u')
            ->Where("u.isDeleted = :isDelete")
            ->setParameter('isDelete', false)
            ->select("count(u.id),u.status")
            ->andWhere('u.roles  LIKE :userroles')
            ->setParameter('userroles', '%"' . User::ROLE_USER . '"%')
            ->groupBy("u.status")
            ;
        return $queryBuilder;
    }



    public function findByEmail($email)
    {

        $queryBuilder = $this->findAllEmployeeQueryBuilder();
        return ($queryBuilder
            ->andWhere(("u.email LIKE :email"))
            ->setParameter("email", $email)
            ->getQuery()
            ->getResult()

        );
    }
    public function findAllActiveUser()
    {

        $queryBuilder = $this->findAllRoleEmployeeQueryBuilder();
        return ($queryBuilder
            ->getQuery()
            ->execute());
    }


    public function findAllNotInThisProjectQueryBuilder($projectID)
    {

        $unAssignedUsers = $this->findAllRoleEmployeeQueryBuilder()
            ->andWhere('u.status = :status')
            ->setParameter('status', User::USER_STATUS_ACTIIVE)
            ->leftJoin('u.projectAssignment', 'pa', 'WITH', 'pa.Project =:project')
            ->andWhere('pa.User IS NULL')
            ->setParameter('project', $projectID);


        return $unAssignedUsers;
    }
    public function findAllUserToAssignTask($projectId): array
    {
        $queryBuilder = $this->findAllEmployeeQueryBuilder()
            ->join('u.projectAssignment', 'pa')
            ->andWhere('pa.Status = :status')
            ->setParameter('status', ProjectAssignment::USER_TASK_STATUS_APPROVED)
            ->andWhere('pa.Project = :project')
            ->setParameter('project', $projectId)
            ->getQuery()
            ->getResult();
        return $queryBuilder;
    }
    public function findAllUserToJoinMeeting()
    {
        return $this->findAllEmployeeQueryBuilder()
            ->andWhere('u.loginFrom = :loginFrom')
            ->setParameter('loginFrom', User::USER_LOGIN_FROM_MS_OFFICE)
            ->getQuery()
            ->getResult();
    }
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
