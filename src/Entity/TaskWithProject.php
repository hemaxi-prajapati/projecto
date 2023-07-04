<?php

namespace App\Entity;

use App\Repository\TaskWithProjectRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;


// #[ORM\HasLifecycleCallbacks]$eventDispatcher->dispatch(

#[ORM\Entity(repositoryClass: TaskWithProjectRepository::class)]
class TaskWithProject
{
    use TimestampableEntity;
    const TASK_STATUS_OPEN = "Open";
    const TASK_STATUS_IN_PROGRESS = "In Progress";
    const TASK_STATUS_COMPLETED = "Completed";
    const TASK_STATUS_ON_HOLD = "On Hold";
    const TASK_STATUS_ARRAY = [TaskWithProject::TASK_STATUS_COMPLETED, TaskWithProject::TASK_STATUS_ON_HOLD, TaskWithProject::TASK_STATUS_OPEN, TaskWithProject::TASK_STATUS_IN_PROGRESS];

    const TASK_PRIORITY_HIGH = "High";
    const TASK_PRIORITY_LOW = "Low";
    const TASK_PRIORITY_MEDIUM = "Medium";


    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Title = null;

    #[ORM\Column(length: 255)]
    private ?string $Description = null;

    #[ORM\Column(length: 255)]
    private ?string $Priority = null;

    #[ORM\Column(length: 255)]
    private ?string $Status = null;


    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $ActualStartDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\GreaterThanOrEqual(propertyPath: 'ActualStartDate')]
    private ?\DateTimeInterface $ActualEndDate = null;



    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $timer;

    #[ORM\Column]
    private ?int $progress = 0;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'taskWithProjects')]
    private Collection $users;

    #[ORM\ManyToOne(inversedBy: 'taskWithProjects')]
    private ?ProjectDetails $project = null;

    public function __construct()
    {
        $this->timer = new \DateTime('00:00:00');
        $this->setCreatedAt(new DateTime());
        $this->setUpdatedAt(new DateTime());
        $this->users = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->Title;
    }

    public function setTitle(string $Title): self
    {
        $this->Title = $Title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(string $Description): self
    {
        $this->Description = $Description;

        return $this;
    }

    public function getPriority(): ?string
    {
        return $this->Priority;
    }

    public function setPriority(string $Priority): self
    {
        $this->Priority = $Priority;

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

    public function getActualStartDate(): ?\DateTimeInterface
    {
        return $this->ActualStartDate;
    }

    public function setActualStartDate(\DateTimeInterface $ActualStartDate): self
    {
        $this->ActualStartDate = $ActualStartDate;

        return $this;
    }

    public function getActualEndDate(): ?\DateTimeInterface
    {
        return $this->ActualEndDate;
    }

    public function setActualEndDate(?\DateTimeInterface $ActualEndDate): self
    {
        $this->ActualEndDate = $ActualEndDate;

        return $this;
    }



    public function getTimer(): ?\DateTimeInterface
    {
        return $this->timer;
    }

    public function setTimer(?\DateTimeInterface $timer): self
    {
        $this->timer = $timer;

        return $this;
    }

    public function getProgress(): ?int
    {
        return $this->progress;
    }

    public function setProgress(int $progress): self
    {
        $this->progress = $progress;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        $this->users->removeElement($user);

        return $this;
    }

    public function getProject(): ?ProjectDetails
    {
        return $this->project;
    }

    public function setProject(?ProjectDetails $project): self
    {
        $this->project = $project;

        return $this;
    }
    // #[ORM\PreFlush]
    // public function onPrePersist()
    // {
    //  dd($this);   /* write your logic here */
    // }
}
