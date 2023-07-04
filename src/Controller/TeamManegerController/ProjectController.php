<?php

namespace App\Controller\TeamManegerController;

use App\Entity\ProjectAssignment;
use App\Form\Project\FilterProjectType;
use App\Message\UserApprovalResponseEmail;
use App\Repository\ProjectDetailsRepository;
use App\Repository\TaskWithProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProjectAssignmentRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Throwable;

class  ProjectController extends AbstractController
{
    #[Route("/TeamManager/csvDownload", "csv_download")]
    public function csvDownload(Request $request, ProjectDetailsRepository $projectDetailsRepository, ProjectAssignmentRepository $projectAssignmentRepository)
    {
        $projectsIdArray = $projectAssignmentRepository->findProjectByDepartmentQuerybuilder($this->getUser()->getDepartment()->getId())->getQuery()->getResult();
        $projects = [];
        if ($projectsIdArray) {

            $st = "";

            foreach ($projectsIdArray as $projectId) {
                $temp = $projectDetailsRepository->find($projectId['projectId']);
                $out = $temp->getAllDataArray();
                $st .= implode(",", $out) . "\n";
            }

            $st = implode(",", array_keys($out))."\n".$st;
            $fileName = "upload/genrateddata.csv";
            $file = fopen($fileName, 'a');
            fwrite($file, $st);
            fclose($file);

            $filePath = "/var/www/symfony/projecto/public/" . $fileName;
            $returnFile = $this->file($filePath);
            $returnFile->deleteFileAfterSend(true);

            return $returnFile;
        }
        $this->addFlash('warning', 'Sorry No data available');
        $this->redirectToRoute("show_projects");
    }
    #[Route("/TeamManager/showProject", "show_projects")]
    public function showProject(Request $request, ProjectDetailsRepository $projectDetailsRepository, ProjectAssignmentRepository $projectAssignmentRepository)
    {
        $page = $request->query->get('page', 1);
        $status = $request->query->get('status');
        $form = $this->createForm(FilterProjectType::class, null, ['departmetId' => $this->getUser()->getDepartment()->getId()]);
        $form->handleRequest($request);
        $projectsIdArray = $projectAssignmentRepository->findProjectByDepartmentQuerybuilder($this->getUser()->getDepartment()->getId());
        if ($status) {
            $projectsIdArray = $projectsIdArray->andWhere("pd.Status = :status ")
                ->setParameter("status", $status);
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($data['ProjectName']) {
                $projectsIdArray = $projectsIdArray->andWhere("pd.Name LIKE :name ")
                    ->setParameter("name", '%' . $data['ProjectName'] . '%');
            }
            if ($data['ProjectManager'] != "all") {
                $projectsIdArray = $projectsIdArray->andWhere("pd.ProjectManager = :uId ")
                    ->setParameter("uId", $data['ProjectManager']);
            }
            // $page = 1;
        }
        $query = $projectsIdArray->getQuery();
        $projectIds = $query->getScalarResult();
        $adapter = new ArrayAdapter($projectIds);
        $pagerFantaIdArray = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $page,
            5
        );
        $projects = array();
        foreach ($pagerFantaIdArray->getCurrentPageResults() as $projectId) {
            $projects[] = $projectDetailsRepository->find($projectId['projectId']);
        }
        return $this->render("teamManager/projectOpration/showProject.html.twig", [
            'projects' => $projects,
            'filterForm' => $form,
            'pagerFanta' => $pagerFantaIdArray
        ]);
    }


    #[Route("/TeamManager/showOneProject", "show_one_project")]
    public function showOneProject(Request $request, ProjectAssignmentRepository $projectAssignmentRepository, ProjectDetailsRepository $projectDetailsRepository, TaskWithProjectRepository $taskWithProjectRepository)
    {
        $page = $request->query->get('page', 1);
        $projectsIdArray = $projectAssignmentRepository->findProjectByDepartmentQuerybuilder($this->getUser()->getDepartment()->getId())->getQuery()->getResult();
        $isTrueProject = false;
        foreach ($projectsIdArray as $projectId) {
            if ($projectId['projectId'] == $request->query->get('id')) {
                $isTrueProject = true;
                break;
            }
        }
        if (!$isTrueProject) {
            $this->addFlash('warning', 'Opps !! You Can See Only Yours Project');
            return $this->redirectToRoute('show_projects');
        }

        $project = $projectDetailsRepository->find($request->query->get('id'));
        $tasks = $taskWithProjectRepository->findTaskWithAllDetailsQuerybuilder($project);
        $UserReletedToProject = $projectAssignmentRepository->findAllInThisProjectQueryBuilder($project)
            ->getQuery()
            ->getResult();

        $adapter = new QueryAdapter($tasks);
        $tasksPagerFanta = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $page,
            10
        );

        return $this->render("teamManager/projectOpration/showOneProject.html.twig", [
            'taskArray' => $tasksPagerFanta, 'project' => $project, 'projectAssign' => $UserReletedToProject,
            'allprojects' => false,
            'pagerFanta' => $tasksPagerFanta
        ]);
    }


    #[Route("/TeamManager/employeeRequest", "employee_request")]
    public function employeeRequest(Request $request, ProjectAssignmentRepository $projectAssignmentRepository, TaskWithProjectRepository $taskWithProjectRepository)
    {

        $NeedAprovelEmployeeQueryBuilder = $projectAssignmentRepository->NeedAprovelEmployeeQueryBuilder($this->getUser()->getDepartment()->getId());

        $adapter = new QueryAdapter($NeedAprovelEmployeeQueryBuilder);
        $pagerFantaNeedAprovelEmployee = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $request->query->get('page', 1),
            10
        );
        return $this->render("teamManager/projectOpration/showRequestedEmployee.html.twig", [
            'projectAssign' => $pagerFantaNeedAprovelEmployee,
            'allprojects' => true,
            'pagerFanta' => $pagerFantaNeedAprovelEmployee

        ]);
    }


    #[Route("/TeamManager/needApprovedChangeStatus", "need_approved_change_status")]
    public function needApprovedChangeStatus(ProjectAssignmentRepository $projectAssignmentRepository, Request $request, EntityManagerInterface $entityManagerInterface, MessageBusInterface $messageBusInterface)
    {
        $projectAssign = $projectAssignmentRepository->find($request->query->get('id'));

        $Status = $request->query->get('opration') == 'Approved' ? ProjectAssignment::USER_TASK_STATUS_APPROVED : ProjectAssignment::USER_TASK_STATUS_REJECTED;
        $projectAssign->setStatus($Status);
        $projectAssign->setAssignAt(new DateTimeImmutable());
        try {
            $entityManagerInterface->flush();

            //send email to pm about status change
            $messageBusInterface->dispatch(new UserApprovalResponseEmail($projectAssign));
        } catch (Throwable $t) {
            $this->addFlash('warning', 'Opps Some Error Occurs :' . $t);
        }
        $this->addFlash($request->query->get('opration') == 'Approved' ? 'success' : 'warning', $projectAssign->getUser()->getName() . ' User Status : ' . $projectAssign->getStatus());


        return $this->redirect($request->headers->get('referer'));
    }
}
