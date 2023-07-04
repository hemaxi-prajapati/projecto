<?php

namespace App\Entity;

use App\Repository\ProjectDetailsRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProjectDetailsRepository::class)]
class ProjectDetails
{
    use TimestampableEntity;
    const PROJECT_STATUS_OPEN = "Open";
    const PROJECT_STATUS_IN_PROGRESS = "In Progress";
    const PROJECT_STATUS_COMPLETED = "Completed";
    const PROJECT_STATUS_ON_HOLD = "On Hold";
    const PROJECT_REPORT_STATUS_INITIAL = "Initial";
    const PROJECT_REPORT_STATUS_IN_PROGRESS = "In Progress";
    const PROJECT_REPORT_STATUS_COMPLETED = "Completed";
    const PROJECT_REPORT_STATUS_FAIL = "Fail";

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $Description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $StartDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\GreaterThanOrEqual(propertyPath: 'StartDate')]
    private ?\DateTimeInterface $EndDate = null;

    #[ORM\ManyToOne(inversedBy: 'projectDetails')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $ProjectManager = null;

    #[ORM\OneToMany(mappedBy: 'Project', targetEntity: ProjectAssignment::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $projectAssignments;

    #[ORM\Column(length: 255)]
    private ?string $Status = null;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: TaskWithProject::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $taskWithProjects;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $attachment = null;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: ProjectReport::class, orphanRemoval: true)]
    private Collection $projectReports;

    public function __construct()
    {
        $this->projectAssignments = new ArrayCollection();
        $this->setCreatedAt(new DateTime());
        $this->setUpdatedAt(new DateTime());
        $this->taskWithProjects = new ArrayCollection();
        $this->projectReports = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): self
    {
        $this->Name = $Name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }
    public function getSmallDescription(): ?string
    {
        return (strlen($this->Description) > 5) ? substr($this->Description, 0, 5) . "..." : substr($this->Description, 0, 5) . "...";
    }

    public function setDescription(string $Description): self
    {
        $this->Description = $Description;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->StartDate;
    }

    public function setStartDate(\DateTimeInterface $StartDate): self
    {
        $this->StartDate = $StartDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->EndDate;
    }

    public function setEndDate(?\DateTimeInterface $EndDate): self
    {
        $this->EndDate = $EndDate;

        return $this;
    }

    public function getProjectManager(): ?User
    {
        return $this->ProjectManager;
    }

    public function setProjectManager(User $ProjectManager): self
    {
        $this->ProjectManager = $ProjectManager;

        return $this;
    }

    /**
     * @return Collection<int, ProjectAssignment>
     */
    public function getProjectAssignments(): Collection
    {
        return $this->projectAssignments;
    }

    public function addProjectAssignment(ProjectAssignment $projectAssignment): self
    {
        if (!$this->projectAssignments->contains($projectAssignment)) {
            $this->projectAssignments->add($projectAssignment);
            $projectAssignment->setProject($this);
        }

        return $this;
    }

    public function removeProjectAssignment(ProjectAssignment $projectAssignment): self
    {
        if ($this->projectAssignments->removeElement($projectAssignment)) {
            // set the owning side to null (unless already changed)
            if ($projectAssignment->getProject() === $this) {
                $projectAssignment->setProject(null);
            }
        }

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->Status;
    }

    public function setStatus(string $Status): self
    {
        $this->Status = $Status;

        return $this;
    }

    /**
     * @return Collection<int, TaskWithProject>
     */
    public function getTaskWithProjects(): Collection
    {
        return $this->taskWithProjects;
    }

    public function addTaskWithProject(TaskWithProject $taskWithProject): self
    {
        if (!$this->taskWithProjects->contains($taskWithProject)) {
            $this->taskWithProjects->add($taskWithProject);
            $taskWithProject->setProject($this);
        }

        return $this;
    }

    public function removeTaskWithProject(TaskWithProject $taskWithProject): self
    {
        if ($this->taskWithProjects->removeElement($taskWithProject)) {
            // set the owning side to null (unless already changed)
            if ($taskWithProject->getProject() === $this) {
                $taskWithProject->setProject(null);
            }
        }

        return $this;
    }

    public function getAttachment(): ?string
    {
        return $this->attachment;
    }

    public function setAttachment(?string $attachment): self
    {
        $this->attachment = $attachment;

        return $this;
    }
    public function getUrlAttachment()
    {
        if ($this->attachment)
            return ($this->attachment) ? "/upload/projects" . "/" . $this->getAttachment() : null;
    }
    /**
     * @return Collection<int, ProjectReport>
     */
    public function getProjectReports(): Collection
    {
        return $this->projectReports;
    }

    public function addProjectReport(ProjectReport $projectReport): static
    {
        if (!$this->projectReports->contains($projectReport)) {
            $this->projectReports->add($projectReport);
            $projectReport->setProject($this);
        }

        return $this;
    }

    public function removeProjectReport(ProjectReport $projectReport): static
    {
        if ($this->projectReports->removeElement($projectReport)) {
            // set the owning side to null (unless already changed)
            if ($projectReport->getProject() === $this) {
                $projectReport->setProject(null);
            }
        }

        return $this;
    }
    public function getAllDataArray(): array
    {
        $array['id'] = preg_replace("/,/", "\,", $this->id);
        $array['Name'] = preg_replace("/,/", " ", $this->Name);
        $array['Description'] = preg_replace("/,/", " ", $this->Description);
        $array['StartDate'] = preg_replace("/,/", " ", date_format($this->StartDate, "d-m-y"));
        $array['EndDate'] = preg_replace("/,/", " ", date_format($this->EndDate, "d-m-y"));
        $array['ProjectManager'] = preg_replace("/,/", " ", $this->ProjectManager->getName());
        $array['Status'] = preg_replace("/,/", " ", $this->Status);
        // $array['attachment']=preg_replace("/,/", " ", $this->attachment);
        $array['createdAt'] = preg_replace("/,/", " ", date_format($this->createdAt, "d-m-y"));
        $array['updatedAt'] = preg_replace("/,/", " ", date_format($this->updatedAt, "d-m-y"));
        return $array;
    }
}
