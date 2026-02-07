<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Enrolment;
use App\Message\GenerateCertificateMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('/api/v1')]
final class EnrollmentsController extends AbstractController
{
    #[Route(
        '/enrollments/{id}/certificate',
        name: 'generate_enrollment_certificate',
        requirements: ['id' => Requirement::POSITIVE_INT],
        methods: 'POST'
    )]
    public function generateEnrollmentCertificate(Enrolment $enrolment, MessageBusInterface $messageBus): JsonResponse
    {
        $messageBus->dispatch(new GenerateCertificateMessage($enrolment->getId()));

        return $this->json([], Response::HTTP_ACCEPTED);
    }
}
