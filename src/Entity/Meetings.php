<?php

namespace App\Entity;

use App\Repository\MeetingsRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\Timestampable;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MeetingsRepository::class)]
class Meetings
{
    use TimestampableEntity;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'meetingAssignee',cascade: ['persist'], orphanRemoval: true)]
    private Collection $meetingAssignee;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotNull()]
    private ?string $subject = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\NotNull()]
    private ?\DateTimeInterface $meetingStartTime = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\GreaterThanOrEqual(propertyPath: 'meetingStartTime')]
    private ?\DateTimeInterface $meetingEndTime = null;


    #[ORM\ManyToOne(inversedBy: 'meetingCreatedBy')]
    private ?User $createdBy = null;

    public function __construct()
    {
        // $this->createdBy = new ArrayCollection();
        $this->meetingAssignee = new ArrayCollection();
        $this->setCreatedAt(new DateTime());
        $this->setUpdatedAt(new DateTime());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, User>
     */
    public function getMeetingAssignee(): Collection
    {
        return $this->meetingAssignee;
    }

    public function addMeetingAssignee(User $meetingAssignee): self
    {
        if (!$this->meetingAssignee->contains($meetingAssignee)) {
            $this->meetingAssignee->add($meetingAssignee);
        }

        return $this;
    }

    public function removeMeetingAssignee(User $meetingAssignee): self
    {
        $this->meetingAssignee->removeElement($meetingAssignee);

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getMeetingStartTime(): ?\DateTimeInterface
    {
        return $this->meetingStartTime;
    }

    public function setMeetingStartTime(?\DateTimeInterface $meetingStartTime): self
    {
        $this->meetingStartTime = $meetingStartTime;

        return $this;
    }

    public function getMeetingEndTime(): ?\DateTimeInterface
    {
        return $this->meetingEndTime;
    }

    public function setMeetingEndTime(?\DateTimeInterface $meetingEndTime): self
    {
        $this->meetingEndTime = $meetingEndTime;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }
}
