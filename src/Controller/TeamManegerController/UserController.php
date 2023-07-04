<?php

namespace App\Controller\TeamManegerController;

use App\Entity\User;
use App\Form\Employee\EditEmployeeType;
use App\Form\Employee\FilterEmployeeType as EmployeeFilterEmployeeType;
use App\Form\Employee\RegistrationFormType;
use App\Message\UserStatusActiveEmail;
use App\Repository\DepartmentRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class UserController extends AbstractController
{


    #[Route("/TeamManager/showEmployee", "show_employee")]
    public function showEmployee(UserRepository $userRepository, Request $request)
    {
        $page = $request->query->get('page', 1);
        $usersQueryBuilder = $userRepository->findAllRoleEmployeeQueryBuilder();
        // dd($usersQueryBuilder->getQuery()->getResult());

        $form = $this->createForm(EmployeeFilterEmployeeType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($data['value']) {
                if ($data['searchBy'] == 'name') {
                    $usersQueryBuilder->andWhere("u.Firstname LIKE :name ")
                        ->setParameter("name", '%' . $data['value'] . '%');
                } else {
                    $usersQueryBuilder->andWhere("u.email LIKE :email ")
                        ->setParameter("email", '%' . $data['value'] . '%');
                }
            }
            // $page=1;
        }
        $adapter = new QueryAdapter($usersQueryBuilder);
        $pagerfanta = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $page,
            10
        );
        return $this->render("teamManager/employeOpration/showEmployee.html.twig", [
            'users' => $pagerfanta,
            'filterForm' => $form->createView()

        ]);
    }
    #[Route('/TeamManager/createEmployee', "create_employee")]
    public function createEmployee(Request $request, UserPasswordHasherInterface $userPasswordHasher, DepartmentRepository $departmentRepository, EntityManagerInterface $entityManager)
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setDepartment($departmentRepository->find(4));
            try {
                $entityManager->persist($user);
                $entityManager->flush();
            } catch (Throwable $t) {
                $this->addFlash('warning', 'Opps Some Error Occurs :' . $t);
            }
            $this->addFlash('success', 'User Created');
            return $this->redirectToRoute('show_employee');
        }
        return $this->render("teamManager/employeOpration/createEmployee.html.twig", [
            'registrationForm' => $form->createView(),
        ]);
    }
    #[Route('/TeamManager/editEmployee', "edit_employee")]
    public function editEmployee(UserRepository $userRepository, Request $request, EntityManagerInterface $entityManager, MessageBusInterface $messageBusInterface)
    {
        $user = $userRepository->find($request->query->get("id"));

        $activestatus = $user->getStatus();
        $wasinactive = $activestatus == USER::USER_STATUS_INACTIIVE ? true : false;

        $user->setRoles([$user->getMainRole()]);
        $form = $this->createForm(EditEmployeeType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {

            $activestatus = $form->getData()->getStatus();
            if ($activestatus == USER::USER_STATUS_ACTIIVE && $wasinactive == true) {
                $messageBusInterface->dispatch(new UserStatusActiveEmail($user));
            }
            try {
                $entityManager->persist($user);
                $entityManager->flush();
            } catch (Throwable $t) {
                $this->addFlash('warning', 'Opps Some Error Occurs :' . $t);
            }
            $this->addFlash('success', 'User Updated');
            return $this->redirectToRoute('show_employee');
        }
        return $this->render("teamManager/employeOpration/editEmployee.html.twig", [
            'editForm' => $form->createView(),
            "userMainRole" => $user->getMainRole()
        ]);
    }
    #[Route("/TeamManager/deleteEmployee", "delete_employee")]
    public function deleteEmployee(UserRepository $userRepository, Request $request, EntityManagerInterface $entityManagerInterface)
    {
        $user = $userRepository->find($request->query->get("id"));
        $t = null;
        try {
            $user->setIsDeleted(true);
            $entityManagerInterface->flush();
            $this->addFlash('warning', 'User Deleted');
        } catch (Throwable $t) {
            $this->addFlash('warning', 'User Not Deleted Due To : ' . $t);
        }
        return $this->redirectToRoute("show_employee");
    }
}
