<?php

namespace App\Entity;

use App\Repository\DepartmentRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: DepartmentRepository::class)]
class Department
{
    use TimestampableEntity;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Name = null;




    #[ORM\OneToMany(mappedBy: 'Department', targetEntity: User::class,cascade: ['persist'], orphanRemoval: true)]
    private Collection $users;

    #[ORM\OneToOne(inversedBy: 'Teammanager', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $Teammanager = null;



    public function __toString()
    {
     return $this->Name;   
    }
    public function __construct()
    {
        $this->users = new ArrayCollection();

            $this->setCreatedAt(new DateTime());
            $this->setUpdatedAt(new DateTime());
        
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
            $user->setDepartment($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getDepartment() === $this) {
                $user->setDepartment(null);
            }
        }

        return $this;
    }

    public function getTeammanager(): ?User
    {
        return $this->Teammanager;
    }

    public function setTeammanager(User $Teammanager): self
    {
        $this->Teammanager = $Teammanager;

        return $this;
    }

 

}
