<?php

namespace App\Entity;

use App\Repository\ProjectAssignmentRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\Timestampable;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: ProjectAssignmentRepository::class)]
class ProjectAssignment
{
    use TimestampableEntity;

    const USER_TASK_STATUS_YET_TO_ASSIGN = "Yet To Assign";
    const USER_TASK_STATUS_APPROVED = "Approved";
    const USER_TASK_STATUS_REJECTED = "Rejected";

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'projectAssignments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProjectDetails $Project = null;

    #[ORM\ManyToOne(inversedBy: 'projectAssignment')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $User;

    #[ORM\Column]
    private ?\DateTimeImmutable $AssignAt = null;

    #[ORM\Column(length: 255)]
    private ?string $Status = ProjectAssignment::USER_TASK_STATUS_YET_TO_ASSIGN;


    public function __construct()
    {
        $this->setCreatedAt(new DateTime());
        $this->setUpdatedAt(new DateTime());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProject(): ?ProjectDetails
    {
        return $this->Project;
    }

    public function setProject(?ProjectDetails $Project): self
    {
        $this->Project = $Project;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(User $User): self
    {
        $this->User = $User;

        return $this;
    }

    public function getAssignAt(): ?\DateTimeImmutable
    {
        return $this->AssignAt;
    }

    public function setAssignAt(\DateTimeImmutable $AssignAt): self
    {
        $this->AssignAt = $AssignAt;

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
}
