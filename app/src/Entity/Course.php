<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CourseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CourseRepository::class)]
class Course
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.ulid_generator')]
    #[ORM\Column(type: 'string', length: 26, unique: true, options: ['fixed' => true, 'collation' => 'ascii_bin'])]
    private string $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $title;

    #[ORM\Column(type: Types::TEXT, length: 65535, nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, Enrolment>
     */
    #[ORM\OneToMany(targetEntity: Enrolment::class, mappedBy: 'enrolledAt', orphanRemoval: true)]
    private Collection $enrolments;

    public function __construct()
    {
        $this->enrolments = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Enrolment>
     */
    public function getEnrolments(): Collection
    {
        return $this->enrolments;
    }

    public function addEnrolment(Enrolment $enrolment): self
    {
        if (!$this->enrolments->contains($enrolment)) {
            $this->enrolments->add($enrolment);
            $enrolment->setEnrolledAt($this);
        }

        return $this;
    }

    public function removeEnrolment(Enrolment $enrolment): self
    {
        $this->enrolments->removeElement($enrolment);

        return $this;
    }
}
