<?php

namespace App\Core\Entity;

use App\Core\Contract\UserInterface;
use App\Core\Enum\TokenEarningMethodEnum;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Core\Repository\TokenEarningLogRepository")]
#[ORM\HasLifecycleCallbacks]
class TokenEarningLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private UserInterface $user;

    #[ORM\Column(type: "string", length: 50, enumType: TokenEarningMethodEnum::class)]
    private TokenEarningMethodEnum $method;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private float $amount;

    #[ORM\Column(type: "string", length: 255)]
    private string $ipAddress;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $details = null;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $createdAt;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getMethod(): TokenEarningMethodEnum
    {
        return $this->method;
    }

    public function setMethod(TokenEarningMethodEnum $method): self
    {
        $this->method = $method;
        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): self
    {
        $this->details = $details;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}
