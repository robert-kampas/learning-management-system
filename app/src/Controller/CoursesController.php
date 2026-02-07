<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Course;
use App\Repository\EnrolmentRepository;
use App\Repository\ProgressLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('/api/v1')]
final class CoursesController extends AbstractController
{
    #[Route('/courses/{id}/report', name: 'course_report', requirements: ['id' => Requirement::UUID_V7], methods: 'GET')]
    public function getCourseReport(
        Course $course,
        EnrolmentRepository $enrolmentRepository,
        ProgressLogRepository $progressLogRepository
    ): JsonResponse
    {
        $courseEnrolments = $enrolmentRepository->findBy(['enrolledAt' => $course], ['id' => 'ASC']);
        $enrolmentsProgressLogs = $progressLogRepository->findBy(['enrolment' => $courseEnrolments]);

        // group progress logs by enrolment Id
        $progressLogsByEnrolment = [];

        foreach ($enrolmentsProgressLogs as $progressLog) {
            $progressLogsByEnrolment[$progressLog->getEnrolment()->getId()][] = [
                'id' => $progressLog->getId(),
                'module_name' => $progressLog->getModuleName(),
                'status' => $progressLog->getStatus()->value,
                'score' => $progressLog->getScore()
            ];
        }

        // build response data
        $courseEnrolmentsData = [];

        foreach ($courseEnrolments as $courseEnrolment) {
            $courseEnrolmentsData[] = [
                'id' => $courseEnrolment->getId(),
                'name' => $courseEnrolment->getStudentName(),
                'email' => $courseEnrolment->getStudentEmail(),
                'progress_logs' => $progressLogsByEnrolment[$courseEnrolment->getId()] ?? []
            ];
        }

        return $this->json([
            'course_title' => $course->getTitle(),
            'enrolled_students' => $courseEnrolmentsData
        ]);
    }
}
