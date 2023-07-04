<?php

namespace App\MessageHandler;

use App\Entity\ProjectDetails;
use App\Entity\ProjectReport;
use App\Message\ReportPdfGeneration;
use App\Repository\ProjectAssignmentRepository;
use App\Repository\ProjectDetailsRepository;
use App\Repository\ProjectReportRepository;
use App\Repository\TaskWithProjectRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Snappy\Pdf;
use Pusher\Pusher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Twig\Environment;

final class ReportPdfGenerationHandler implements MessageHandlerInterface

{
    public function __construct(
        private ProjectDetailsRepository $projectDetailsRepository,
        private TaskWithProjectRepository $taskWithProjectRepository,
        private ProjectAssignmentRepository $projectAssignmentRepository,
        private ProjectReportRepository $projectReportRepository,
        private Pdf $pdf,
        private Environment $environment,
        private EntityManagerInterface $entityManagerInterface
    ) {
    }
    public function __invoke(ReportPdfGeneration $message)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(3000);

        $project = $this->projectDetailsRepository->find($message->getProjectId());
        $tasks = $this->taskWithProjectRepository->findTaskWithAllDetailsQuerybuilder($project);
        $UserReletedToProject = $this->projectAssignmentRepository->findAllInThisProjectQueryBuilder($project)
            ->getQuery()
            ->getResult();

        $tasks = $tasks->getQuery()->getResult();
        $projectReport = $this->projectReportRepository->findOneBy(['user' => $message->getUser(), 'project' => $project]);

        $filename = 'Project Report ' . date_format(new DateTime('now'), "Y/m/d H:i:s") . '.pdf';

        $allProject = false;
        $IsReport = true;
        $auther = $message->getUser()->getName();
        $html = $this->environment->render("pdf/project_report.html.twig", [
            'taskArray' => $tasks, 'project' => $project, 'projectAssign' => $UserReletedToProject,
            'allprojects' => $allProject,
            'projectReport' => $IsReport,
            'ReportGenaratedBy' => $auther
        ]);
        // inline for Live pdf view and attachment for dawnload pdfj
        $option = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename=' . $filename
        ];
        // using wkhtmltopdf
        $newPdf = new Response(
            $this->pdf->getOutputFromHtml($html),
            200,
            $option
        );

        if (!file_exists("./public/upload/" . $message->getUser()->getId())) {
            mkdir("./public/upload/" . $message->getUser()->getId());
        }
        if (!file_exists("./public/upload/" . $message->getUser()->getId() . "/ProjectReport")) {
            mkdir("./public/upload/" . $message->getUser()->getId() . "/ProjectReport");
        }

        if (file_put_contents("./public/upload/" . $message->getUser()->getId() . "/ProjectReport" . '/Project Report' . date("d-m-Y h:i:s") . '.pdf', $newPdf)) {

            $projectReport->setStatus(ProjectDetails::PROJECT_REPORT_STATUS_COMPLETED);
            $projectReport->setFileName('Project Report' . date("d-m-Y h:i:s") . '.pdf');
        } else {
            $projectReport->setStatus(ProjectDetails::PROJECT_REPORT_STATUS_FAIL);
        }
        $this->entityManagerInterface->flush();

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
        $data['timeOut'] = 5;
        $data['UserId'] = $message->getUser()->getId();
        $pusher->trigger('my-channel', 'reload-page', $data);
    }
}
