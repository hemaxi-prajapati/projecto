<?php

namespace App\Entity;

use App\Repository\TrasactionRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: TrasactionRepository::class)]
class Trasaction
{
     use TimestampableEntity;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $paymentId = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column]
    private ?int $amount = null;

    #[ORM\Column]
    private ?int $received_amount = null;

    #[ORM\Column]
    private ?int $created_timestamp = null;

    #[ORM\Column(length: 255)]
    private ?string $payment_method = null;

    #[ORM\Column(length: 255)]
    private ?string $customer_id = null;

    public function __construct()
    {
        
        $this->setCreatedAt(new DateTime());
        $this->setUpdatedAt(new DateTime());
     
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPaymentId(): ?string
    {
        return $this->paymentId;
    }

    public function setPaymentId(string $paymentId): self
    {
        $this->paymentId = $paymentId;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getReceivedAmount(): ?int
    {
        return $this->received_amount;
    }

    public function setReceivedAmount(int $received_amount): self
    {
        $this->received_amount = $received_amount;

        return $this;
    }

    public function getCreatedTimestamp(): ?int
    {
        return $this->created_timestamp;
    }

    public function setCreatedTimestamp(int $created_timestamp): self
    {
        $this->created_timestamp = $created_timestamp;

        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->payment_method;
    }

    public function setPaymentMethod(string $payment_method): self
    {
        $this->payment_method = $payment_method;

        return $this;
    }

    public function getCustomerId(): ?string
    {
        return $this->customer_id;
    }

    public function setCustomerId(string $customer_id): self
    {
        $this->customer_id = $customer_id;

        return $this;
    }
}
