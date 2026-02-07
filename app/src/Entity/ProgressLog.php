<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\ProgressType;
use App\Repository\ProgressLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProgressLogRepository::class)]
class ProgressLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['unsigned' => true])]
    private int $id;

    #[ORM\Column(type: Types::TEXT, length: 65535)]
    private string $moduleName;

    #[ORM\Column(enumType: ProgressType::class)]
    private ProgressType $status;

    #[ORM\Column(type: Types::INTEGER, options: ['unsigned' => true])]
    private int $score = 0;

    #[ORM\ManyToOne(targetEntity: Enrolment::class, inversedBy: 'progressLogs')]
    #[ORM\JoinColumn(nullable: false)]
    private Enrolment $enrolment;

    public function getId(): int
    {
        return $this->id;
    }

    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    public function setModuleName(string $moduleName): self
    {
        $this->moduleName = $moduleName;

        return $this;
    }

    public function getStatus(): ProgressType
    {
        return $this->status;
    }

    public function setStatus(ProgressType $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function setScore(int $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getEnrolment(): Enrolment
    {
        return $this->enrolment;
    }

    public function setEnrolment(Enrolment $enrolment): self
    {
        $this->enrolment = $enrolment;

        return $this;
    }
}
