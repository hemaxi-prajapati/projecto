<?php

namespace App\Controller\EmployeeController;

use App\Repository\ProjectAssignmentRepository;
use App\Repository\ProjectDetailsRepository;
use App\Repository\TaskWithProjectRepository;
use DateTime;
use Dompdf\Dompdf;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;


class PdfGeneratorController extends AbstractController
{
    #[Route('/pdf/generator', name: 'app_pdf_generator')]
    public function projectReportGenerator(Request $request, ProjectDetailsRepository $projectDetailsRepository, ProjectAssignmentRepository $projectAssignmentRepository, TaskWithProjectRepository $taskWithProjectRepository, Pdf $pdf)
    {

        $project = $projectDetailsRepository->find($request->query->get('ProjectId'));

        $tasks = $taskWithProjectRepository->findTaskWithAllDetailsQuerybuilder($project);
        $UserReletedToProject = $projectAssignmentRepository->findAllInThisProjectQueryBuilder($project)
            ->getQuery()
            ->getResult();

        $tasks = $tasks->getQuery()->getResult();

        $html = $this->renderView("pdf/project_report.html.twig", [
            'taskArray' => $tasks, 'project' => $project, 'projectAssign' => $UserReletedToProject,
            'allprojects' => false,
            'projectReport' => true,
            'ReportGenaratedBy' => $this->getUser()->getName(),
        ]);

        // inline for Live pdf view and attachment for dawnload pdf
        $option = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename=Project Report' . date_format(new DateTime('now'), "Y/m/d H:i:s") . '.pdf',
            // 'footer-html' => $footer,
            // 'header-html' => $header,
            // 'page-size' => 'A4',
            // 'margin-top' => 10,
            // 'margin-right' => 10,
            // 'margin-bottom' => 100,
            // 'margin-left' => 10,

        ];

        // return new Response(
        //     $pdf->getOutputFromHtml($html),
        //     200,
        //     $option
        // );

        //using dompdf
        $domPdf = new Dompdf();
        $domPdf->loadHtml($html);
        $domPdf->render();
        return new Response(
            $domPdf->stream("Project Report.Pdf", $option),
            Response::HTTP_OK,
        );
    }
}
