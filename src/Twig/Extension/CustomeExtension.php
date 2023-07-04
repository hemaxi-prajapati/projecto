<?php

namespace App\Twig\Extension;

use App\Entity\ProjectDetails;
use App\Entity\TaskWithProject;
use App\Repository\ProjectReportRepository;
use App\Twig\Runtime\CustomeExtensionRuntime;
use Doctrine\ORM\Query\Expr\Func;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class CustomeExtension extends AbstractExtension
{
    public function __construct(private ProjectReportRepository $projectReportRepository)
    {
    }
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            new TwigFilter('get_small_string', [$this, 'getSmallString']),
            new TwigFilter('project_status', [$this, 'projectStatus']),
            new TwigFilter('project_report_status', [$this, 'projectReportStatus']),
            new TwigFilter('task_priority', [$this, 'taskPriority']),
            new TwigFilter('task_status', [$this, 'taskStatus'])
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('find_status', [$this, 'findStatus']),
        ];
    }

    public function getSmallString($string)
    {

        return (substr($string, 0, 4) . "...");
    }
    public function projectReportStatus($status)
    {
        if ($status == ProjectDetails::PROJECT_REPORT_STATUS_COMPLETED) {
            return "Download Report";
        } else if ($status == ProjectDetails::PROJECT_REPORT_STATUS_FAIL) {
            return "Fail";
        } else if ($status == ProjectDetails::PROJECT_REPORT_STATUS_IN_PROGRESS) {
            return "Report Generating";
        } else if ($status == ProjectDetails::PROJECT_REPORT_STATUS_INITIAL) {
            return "Generate Report";
        } else {
            return "Reload";
        }
    }
    public function projectStatus($status)
    {
        if ($status == ProjectDetails::PROJECT_STATUS_COMPLETED) {
            return "bg-danger";
        } else if ($status == ProjectDetails::PROJECT_STATUS_OPEN) {
            return "bg-secondary";
        } else if ($status == ProjectDetails::PROJECT_STATUS_ON_HOLD) {
            return "bg-warning";
        } else if ($status == ProjectDetails::PROJECT_STATUS_IN_PROGRESS) {
            return "bg-danger";
        } else {
            return "bg-primary";
        }
    }
    public function taskStatus($status)
    {
        if ($status == TaskWithProject::TASK_STATUS_ON_HOLD) {
            return "bg-danger";
        } else if ($status == TaskWithProject::TASK_STATUS_COMPLETED) {
            return "bg-secondary";
        } else if ($status == TaskWithProject::TASK_STATUS_IN_PROGRESS) {
            return "bg-warning";
        } else if ($status == TaskWithProject::TASK_STATUS_OPEN) {
            return "bg-success";
        } else {
            return "bg-primary";
        }
    }
    public function taskPriority($priority)
    {
        if ($priority == TaskWithProject::TASK_PRIORITY_HIGH) {
            return "bg-success";
        } else if ($priority == TaskWithProject::TASK_PRIORITY_LOW) {
            return "bg-danger";
        } else if ($priority ==  TaskWithProject::TASK_PRIORITY_MEDIUM) {
            return "bg-warning";
        } else {
            return "bg-success";
        }
    }
    public function findStatus($project, $user)
    {
        $projectReport = $this->projectReportRepository->findOneBy(['user' => $user, 'project' => $project]);
        return $projectReport ? $projectReport->getStatus() : ProjectDetails::PROJECT_REPORT_STATUS_INITIAL;
    }
}
// constant('App\\Entity