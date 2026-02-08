<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\GenerateCertificateMessage;
use App\Repository\EnrolmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GenerateCertificateMessageHandler
{
    public function __construct(
        private EnrolmentRepository $enrolmentRepository,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {}

    public function __invoke(GenerateCertificateMessage $message): void
    {
        $enrollment = $this->enrolmentRepository->findOneBy(['id' => $message->getEnrollmentId()]);

        if ($enrollment === null) {
            $this->logger->error(sprintf('Enrollment with id "%d" not found.', $message->getEnrollmentId()));
        }

        $this->generatePdf();
        $certificationHash = hash('sha256', $enrollment->getId() . microtime());

        $enrollment->setCertificationHash($certificationHash);
        $this->entityManager->flush();

        $this->logger->info('Certificate generated', [
            'enrollment_id' => $enrollment->getId(),
            'certificate_hash' => $certificationHash
        ]);
    }

    private function generatePdf(): void
    {
        sleep(10);
    }
}
