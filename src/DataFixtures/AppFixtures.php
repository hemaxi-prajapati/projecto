<?php

namespace App\DataFixtures;

use App\Entity\Department;
use App\Entity\User;
use App\Factory\DailyAttendanceFactory;
use App\Factory\DepartmentFactory;
use App\Factory\ProjectAssignmentFactory;
use App\Factory\ProjectDetailsFactory;
use App\Factory\TaskWithProjectFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $userPasswordHasherInterface;

    public function __construct(UserPasswordHasherInterface $userPasswordHasherInterface)
    {
        $this->userPasswordHasherInterface = $userPasswordHasherInterface;
    }
    public function load(ObjectManager $manager): void
    {

        //create  user
        $user = new User();
        $user->setFirstname("team");
        $user->setLastName("manager");
        $user->setRoles([User::ROLE_TEAM_MANAGER]);
        $user->setIsVerified(true);
        $user->setEmail("tm@gmail.com");
        $user->setPlainPassword("123456");
        $user->setPassword($this->userPasswordHasherInterface->hashPassword($user, $user->getPlainPassword()));
        $manager->persist($user);

        //create Department
        $phpDepartment = new Department();
        $phpDepartment->setName("PHP");
        $phpDepartment->setTeammanager($user);
        $manager->persist($phpDepartment);

        //set user department 
        $user->setDepartment($phpDepartment);

        $manager->flush();

        $pmUsers=UserFactory::createMany(4, ['roles' => [User::ROLE_PROJECT_MANAGER]]);
        $employeeUsers=UserFactory::createMany(10, ['roles' => [User::ROLE_USER]]);

        ProjectDetailsFactory::createMany(20, function () use($pmUsers) {
            return ['ProjectManager' =>$pmUsers[array_rand($pmUsers)]];
        });

        TaskWithProjectFactory::createMany(30, function () use($employeeUsers)  {
            return [
                'addUser' => $employeeUsers[array_rand($employeeUsers)],
            ];
        });
    
        DailyAttendanceFactory::createMany(20, function () use($employeeUsers) {
            return [
                'user' => $employeeUsers[array_rand($employeeUsers)],
            ];
    });

        $manager->flush();
    }
}