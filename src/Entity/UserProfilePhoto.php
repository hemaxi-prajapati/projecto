<?php

namespace App\Entity;

use App\Repository\UserProfilePhotoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserProfilePhotoRepository::class)]
class UserProfilePhoto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'photoSource', cascade: ['persist', 'remove'],orphanRemoval: true)]
    private ?User $user = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $source = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $AccessToken = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->AccessToken;
    }

    public function setAccessToken(?string $AccessToken): self
    {
        $this->AccessToken = $AccessToken;

        return $this;
    }
}
