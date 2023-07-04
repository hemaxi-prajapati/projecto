<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]


// define(ROLE_USER)="ROLE_USER"
// ROLE_ADMIN="ROLE_PROJECT_MANAGER"
// ROLE_MASTER_ADMIN="ROLE_TEAM_MANAGER"
class User  implements UserInterface, PasswordAuthenticatedUserInterface
{
    use TimestampableEntity;
    const ROLE_USER = "ROLE_USER";
    const ROLE_PROJECT_MANAGER = "ROLE_PROJECT_MANAGER";
    const ROLE_TEAM_MANAGER = "ROLE_TEAM_MANAGER";
    const USER_STATUS_INACTIIVE = "INACTIVE";
    const USER_STATUS_ACTIIVE = "ACTIVE";
    const USER_LOGIN_FROM_PROJECTO = "PROJECTO";
    const USER_LOGIN_FROM_MS_OFFICE = "MS OFFICE";
    const USER_LOGIN_FROM_GOOGLE = "GOOGLE";

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [User::ROLE_USER];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(nullable: true)]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $Firstname;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank]
    private ?string $LastName = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $ContactNumber = null;

    #[ORM\Column(nullable: true)]
    private ?int $exprience = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Department $Department = null;

    #[ORM\OneToOne(mappedBy: 'Teammanager', cascade: ['persist'], orphanRemoval: true)]
    private ?Department $Teammanager = null;

    #[ORM\OneToOne(mappedBy: 'ProjectManager', cascade: ['persist'], orphanRemoval: true)]
    private ?ProjectDetails $projectDetails = null;

    #[ORM\OneToMany(mappedBy: 'User', targetEntity: ProjectAssignment::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $projectAssignment;

    // INACTIIVE,ACTIVE
    #[ORM\Column(length: 255)]
    private ?string $status = User::USER_STATUS_INACTIIVE;

    #[ORM\Column]
    private ?bool $isVerified = false;

    private $plainPassword;

    #[ORM\Column(length: 255)]
    private ?string $loginFrom = User::USER_LOGIN_FROM_PROJECTO;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: DailyAttendance::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $user;

    #[ORM\ManyToMany(targetEntity: TaskWithProject::class, mappedBy: 'users', cascade: ['persist'], orphanRemoval: true)]
    private Collection $taskWithProjects;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist'], orphanRemoval: true)]
    private ?UserProfilePhoto $photoSource = null;

    #[ORM\OneToMany(mappedBy: 'User', targetEntity: OtpAuthentication::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $otpAuthentications;

    #[ORM\ManyToMany(targetEntity: Meetings::class, mappedBy: 'meetingAssignee')]
    private Collection $meetingAssignee;

    #[ORM\OneToMany(mappedBy: 'createdBy', targetEntity: Meetings::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $meetingCreatedBy;

    #[ORM\Column]
    private ?bool $isDeleted = False;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ProjectReport::class, orphanRemoval: true)]
    private Collection $projectReports;

    public function __construct()
    {
        $this->setCreatedAt(new DateTime());
        $this->setUpdatedAt(new DateTime());
        $this->user = new ArrayCollection();
        $this->otpAuthentications = new ArrayCollection();
        $this->taskWithProjects = new ArrayCollection();
        $this->meetingAssignee = new ArrayCollection();
        $this->meetingCreatedBy = new ArrayCollection();
        $this->projectAssignment = new ArrayCollection();
        $this->projectReports = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = User::ROLE_USER;

        return array_unique($roles);
    }

    // define(ROLE_USER)="ROLE_USER"
    // ROLE_ADMIN="ROLE_PROJECT_MANAGER"
    // ROLE_MASTER_ADMIN="ROLE_TEAM_MANAGER"
    public function getMainRole(): string
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        return (in_array(User::ROLE_TEAM_MANAGER, $roles)) ? User::ROLE_TEAM_MANAGER : ((in_array(User::ROLE_PROJECT_MANAGER, $roles)) ? User::ROLE_PROJECT_MANAGER : User::ROLE_USER);
    }

    public function setRoles(?array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }
    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $password): self
    {
        $this->plainPassword = $password;

        return $this;
    }
    // public function setPasswordFromPlainPassword(string $password): self
    // {
    //     $userPasswordHasher=$this->userPasswordHasherInterface;
    //     $this->setPassword(
    //         $userPasswordHasher->hashPassword(
    //             $this,
    //             $password
    //         )
    //     );
    //     return $this;
    // }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstname(): ?string
    {
        return $this->Firstname;
    }
    public function getname(): ?string
    {
        return $this->Firstname . " " . $this->LastName;
    }

    public function setFirstname(?string $Firstname): self
    {
        $this->Firstname = $Firstname;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->LastName;
    }

    public function setLastName(?string $LastName): self
    {
        $this->LastName = $LastName;

        return $this;
    }

    public function getContactNumber(): ?string
    {
        return $this->ContactNumber;
    }

    public function setContactNumber(?string $ContactNumber): self
    {
        $this->ContactNumber = $ContactNumber;

        return $this;
    }

    public function getExprience(): ?int
    {
        return $this->exprience;
    }

    public function setExprience(?int $exprience): self
    {
        $this->exprience = $exprience;

        return $this;
    }

    public function getDepartment(): ?Department
    {
        return $this->Department;
    }

    public function setDepartment(?Department $Department): self
    {
        $this->Department = $Department;

        return $this;
    }

    public function getTeammanager(): ?Department
    {
        return $this->Teammanager;
    }

    public function setTeammanager(Department $Teammanager): self
    {
        // set the owning side of the relation if necessary
        if ($Teammanager->getTeammanager() !== $this) {
            $Teammanager->setTeammanager($this);
        }

        $this->Teammanager = $Teammanager;

        return $this;
    }

    public function getProjectDetails(): ?ProjectDetails
    {
        return $this->projectDetails;
    }

    public function setProjectDetails(ProjectDetails $projectDetails): self
    {
        // set the owning side of the relation if necessary
        if ($projectDetails->getProjectManager() !== $this) {
            $projectDetails->setProjectManager($this);
        }

        $this->projectDetails = $projectDetails;

        return $this;
    }

    // public function getProjectAssignment(): ?ProjectAssignment
    // {
    //     return $this->projectAssignment;
    // }

    // public function setProjectAssignment(ProjectAssignment $projectAssignment): self
    // {
    //     // set the owning side of the relation if necessary
    //     if ($projectAssignment->getUser() !== $this) {
    //         $projectAssignment->setUser($this);
    //     }

    //     $this->projectAssignment = $projectAssignment;

    //     return $this;
    // }

    public function getStatus(): ?string
    {
        return $this->status;
    }
    public function getProfileUrl()
    {
        $url = null;
        if ($this->getPhotoSource()) {
            if ($this->getLoginFrom() == User::USER_LOGIN_FROM_MS_OFFICE) {
                $base64Source = $this->getPhotoSource()->getSource();
                $url = "data:image/jpeg;base64,$base64Source";

                // $html = "<img src='data:image/jpeg;base64,$responsee' alt='User Photo'>";
            } else if ($this->getLoginFrom() == User::USER_LOGIN_FROM_GOOGLE) {
                $url = $this->getPhotoSource()->getSource();
            } else {
                $url = "/upload/" . $this->getId() . "/" . $this->getPhotoSource()->getSource();
            }
        }
        return $url != null ? $url : "https://ui-avatars.com/api/?size=35&" . http_build_query(["name" => $this->getname()]);
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function isIsVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function getLoginFrom(): ?string
    {
        return $this->loginFrom;
    }

    public function setLoginFrom(string $loginFrom): self
    {
        $this->loginFrom = $loginFrom;

        return $this;
    }

    /**
     * @return Collection<int, DailyAttendance>
     */
    public function getUser(): Collection
    {
        return $this->user;
    }

    public function addUser(DailyAttendance $user): self
    {
        if (!$this->user->contains($user)) {
            $this->user->add($user);
            $user->setUser($this);
        }

        return $this;
    }

    public function removeUser(DailyAttendance $user): self
    {
        if ($this->user->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getUser() === $this) {
                $user->setUser(null);
            }
        }

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
            $taskWithProject->addUser($this);
        }

        return $this;
    }

    public function removeTaskWithProject(TaskWithProject $taskWithProject): self
    {
        if ($this->taskWithProjects->removeElement($taskWithProject)) {
            $taskWithProject->removeUser($this);
        }

        return $this;
    }

    public function getPhotoSource(): ?UserProfilePhoto
    {
        return $this->photoSource;
    }

    public function setPhotoSource(?UserProfilePhoto $photoSource): self
    {
        // unset the owning side of the relation if necessary
        if ($photoSource === null && $this->photoSource !== null) {
            $this->photoSource->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($photoSource !== null && $photoSource->getUser() !== $this) {
            $photoSource->setUser($this);
        }

        $this->photoSource = $photoSource;

        return $this;
    }

    /**
     * @return Collection<int, OtpAuthentication>
     */
    public function getOtpAuthentications(): Collection
    {
        return $this->otpAuthentications;
    }

    public function addOtpAuthentication(OtpAuthentication $otpAuthentication): self
    {
        if (!$this->otpAuthentications->contains($otpAuthentication)) {
            $this->otpAuthentications->add($otpAuthentication);
            $otpAuthentication->setUser($this);
        }

        return $this;
    }

    public function removeOtpAuthentication(OtpAuthentication $otpAuthentication): self
    {
        if ($this->otpAuthentications->removeElement($otpAuthentication)) {
            // set the owning side to null (unless already changed)
            if ($otpAuthentication->getUser() === $this) {
                $otpAuthentication->setUser(null);
            }
        }

        return $this;
    }

    // public function getMeetingCreatedBy(): ?Meetings
    // {
    //     return $this->meetingCreatedBy;
    // }

    // public function setMeetingCreatedBy(?Meetings $meetingCreatedBy): self
    // {
    //     $this->meetingCreatedBy = $meetingCreatedBy;

    //     return $this;
    // }

    /**
     * @return Collection<int, Meetings>
     */
    public function getMeetingAssignee(): Collection
    {
        return $this->meetingAssignee;
    }

    public function addMeetingAssignee(Meetings $meetingAssignee): self
    {
        if (!$this->meetingAssignee->contains($meetingAssignee)) {
            $this->meetingAssignee->add($meetingAssignee);
            $meetingAssignee->addMeetingAssignee($this);
        }

        return $this;
    }

    public function removeMeetingAssignee(Meetings $meetingAssignee): self
    {
        if ($this->meetingAssignee->removeElement($meetingAssignee)) {
            $meetingAssignee->removeMeetingAssignee($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Meetings>
     */
    public function getMeetingCreatedBy(): Collection
    {
        return $this->meetingCreatedBy;
    }

    public function addMeetingCreatedBy(Meetings $meetingCreatedBy): self
    {
        if (!$this->meetingCreatedBy->contains($meetingCreatedBy)) {
            $this->meetingCreatedBy->add($meetingCreatedBy);
            $meetingCreatedBy->setCreatedBy($this);
        }

        return $this;
    }

    public function removeMeetingCreatedBy(Meetings $meetingCreatedBy): self
    {
        if ($this->meetingCreatedBy->removeElement($meetingCreatedBy)) {
            // set the owning side to null (unless already changed)
            if ($meetingCreatedBy->getCreatedBy() === $this) {
                $meetingCreatedBy->setCreatedBy(null);
            }
        }

        return $this;
    }

    public function isIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): static
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * @return Collection<int, ProjectAssignment>
     */
    public function getProjectAssignment(): Collection
    {
        return $this->projectAssignment;
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
            $projectReport->setUser($this);
        }

        return $this;
    }

    public function removeProjectReport(ProjectReport $projectReport): static
    {
        if ($this->projectReports->removeElement($projectReport)) {
            // set the owning side to null (unless already changed)
            if ($projectReport->getUser() === $this) {
                $projectReport->setUser(null);
            }
        }

        return $this;
    }
}
