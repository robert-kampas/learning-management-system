<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\EnrolmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EnrolmentRepository::class)]
class Enrolment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['unsigned' => true])]
    private int $id;

    #[ORM\Column(type: Types::TEXT, length: 65535)]
    private string $studentName;

    #[ORM\Column(type: Types::TEXT, length: 65535)]
    private string $studentEmail;

    #[ORM\ManyToOne(targetEntity: Course::class, fetch: 'EXTRA_LAZY', inversedBy: 'enrolments')]
    #[ORM\JoinColumn(nullable: false)]
    private Course $enrolledAt;

    /**
     * @var Collection<int, ProgressLog>
     */
    #[ORM\OneToMany(targetEntity: ProgressLog::class, mappedBy: 'enrolment', fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    private Collection $progressLogs;

    public function __construct()
    {
        $this->progressLogs = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getStudentName(): string
    {
        return $this->studentName;
    }

    public function setStudentName(string $studentName): self
    {
        $this->studentName = $studentName;

        return $this;
    }

    public function getStudentEmail(): string
    {
        return $this->studentEmail;
    }

    public function setStudentEmail(string $studentEmail): self
    {
        $this->studentEmail = $studentEmail;

        return $this;
    }

    public function getEnrolledAt(): Course
    {
        return $this->enrolledAt;
    }

    public function setEnrolledAt(Course $enrolledAt): self
    {
        $this->enrolledAt = $enrolledAt;

        return $this;
    }

    /**
     * @return Collection<int, ProgressLog>
     */
    public function getProgressLogs(): Collection
    {
        return $this->progressLogs;
    }

    public function addProgressLog(ProgressLog $progressLog): self
    {
        if (!$this->progressLogs->contains($progressLog)) {
            $this->progressLogs->add($progressLog);
            $progressLog->setEnrolment($this);
        }

        return $this;
    }

    public function removeProgressLog(ProgressLog $progressLog): self
    {
        $this->progressLogs->removeElement($progressLog);

        return $this;
    }
}
