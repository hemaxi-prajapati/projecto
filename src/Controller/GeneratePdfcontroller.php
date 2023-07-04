<?php

namespace App\Controller;

use App\Entity\ProjectDetails;
use App\Entity\ProjectReport;
use App\Entity\User;
use App\Message\ReportPdfGeneration;
use App\Repository\ProjectAssignmentRepository;
use App\Repository\ProjectDetailsRepository;
use App\Repository\ProjectReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Pusher\Pusher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;

class GeneratePdfcontroller extends AbstractController
{
    #[Route('/pdf/projectReportDownload', name: 'project_report_download')]
    public function projectReportDownload(Request $request, ProjectDetailsRepository $projectDetailsRepository, EntityManagerInterface $entityManagerInterface, ProjectReportRepository $projectReportRepository)
    {
        $project = $projectDetailsRepository->find($request->query->get('projectId'));
        $projectReport = $projectReportRepository->findOneBy(['user' => $this->getUser(), 'project' => $project]);

        $filename = $projectReport->getFileName();
        $filePath = "/var/www/symfony/projecto/public/upload/" . $this->getUser()->getId() . '/ProjectReport' . '/' . $filename;
        $returnFile = $this->file($filePath);
        // $projec(null);
        $projectReport->setFileName(null);
        $projectReport->setStatus(ProjectDetails::PROJECT_REPORT_STATUS_INITIAL);
        $project->removeProjectReport($projectReport);
        $entityManagerInterface->flush();
        $returnFile->deleteFileAfterSend(true);

        $options = array(
            'cluster' => 'ap2',
            'useTLS' => true
        );
        $pusher = new Pusher(
            '60aef47270465eb54b03',
            '7f166951b0b80291698e',
            '1624986',
            $options
        );
        $data['timeOut'] = 50;
        $data['UserId'] = $this->getUser()->getId();
        $pusher->trigger('my-channel', 'reload-page', $data);
        return  $returnFile;
    }
    #[Route('/pdf/projectReportGenerator', name: 'project_report_generator')]
    public function projectReportGenerator(Request $request, ProjectDetailsRepository $projectDetailsRepository, ProjectAssignmentRepository $projectAssignmentRepository,  MessageBusInterface $messageBusInterface, EntityManagerInterface $entityManagerInterface, ProjectReportRepository $projectReportRepository)
    {
        $user = $this->getUser();
        $isRealProject = false;
        if ($user->getMainRole() == User::ROLE_PROJECT_MANAGER) {
            $isRealProject = $projectDetailsRepository->findAllProjectsByAnyTypeUserQueryBuilder($user, $request->query->get('projectId'))
                ->getQuery()
                ->getResult();
        } else if ($user->getMainRole() == User::ROLE_TEAM_MANAGER) {
            $projectIds = $projectAssignmentRepository->findProjectByDepartmentQuerybuilder($user->getDepartment()->getId())
                ->getQuery()
                ->getResult();
            foreach ($projectIds as $projectId) {
                if ($projectId['projectId'] == $request->query->get('projectId')) {
                    $isRealProject = true;
                    break;
                }
            }
        }
        if ($isRealProject) {

            $project = $projectDetailsRepository->find($request->query->get('projectId'));
          
            $projectReport = $projectReportRepository->findOneBy(['user' => $this->getUser(), 'project' => $project]);
            if (!$projectReport) {
                $projectReport = new ProjectReport();
            }
            $projectReport->setStatus(ProjectDetails::PROJECT_REPORT_STATUS_IN_PROGRESS);
            $projectReport->setUser($this->getUser());
            $project->addProjectReport($projectReport);
            $entityManagerInterface->persist($projectReport);
            $entityManagerInterface->flush();
            $messageBusInterface->dispatch(new ReportPdfGeneration($request->query->get('projectId'), $this->getUser()));
            $this->addFlash('success', "Your Pdf Will Generate Soon Refresh this page in minut");

        } else {
            $this->addFlash("warning", "You are Not Allow to Download This File  ");
            return $this->redirect($request->server->get('HTTP_REFERER'));
        }
        return $this->redirect($request->server->get('HTTP_REFERER'));
    }
}
