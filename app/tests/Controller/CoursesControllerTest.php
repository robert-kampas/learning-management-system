<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Repository\CourseRepository;
use App\Repository\ProgressLogRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Course;
use App\Repository\EnrolmentRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CoursesControllerTest extends WebTestCase
{
    private readonly CourseRepository $courseRepository;
    private readonly EnrolmentRepository $enrolmentRepository;
    private readonly ProgressLogRepository $progressLogRepository;
    private readonly UrlGeneratorInterface $urlGenerator;
    private readonly KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();

        $this->courseRepository = self::getContainer()->get(CourseRepository::class);
        $this->enrolmentRepository = self::getContainer()->get(EnrolmentRepository::class);
        $this->progressLogRepository = self::getContainer()->get(ProgressLogRepository::class);
        $this->urlGenerator = self::getContainer()->get(UrlGeneratorInterface::class);
    }

    public function testGetCourseReportReturnsExpectedJsonStructure(): void
    {
        /** @var Course|null $randomCourse */
        $randomCourse = $this->courseRepository->findOneBy([], ['id' => 'ASC']);
        $courseEnrollments = $this->enrolmentRepository->findBy(['enrolledAt' => $randomCourse]);

        $this->assertGreaterThan(0, count($courseEnrollments), 'This course has no enrollments. Cannot proceed with the unit test. Please check your data and try again.');
        $this->assertInstanceOf(Course::class, $randomCourse);

        $courseReportUrl = $this->urlGenerator->generate('course_report', ['id' => $randomCourse->getId()]);
        dump("Running integration test for: $courseReportUrl");
        $this->client->request('GET', $courseReportUrl);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $this->assertJson($this->client->getResponse()->getContent());

        $data = json_decode($this->client->getResponse()->getContent(), true, 6, JSON_THROW_ON_ERROR);

        $this->assertEquals($randomCourse->getTitle(), $data['course_title']);
        $this->assertIsArray($data['enrolled_students']);

        $actualEnrollmentObject = null;
        $actualFirstEnrollment = $data['enrolled_students'][0];
        $actualEnrollmentId = $actualFirstEnrollment['id'] ?? false;
        $actualEnrollmentName = $actualFirstEnrollment['name'] ?? false;
        $actualEnrollmentEmail = $actualFirstEnrollment['email'] ?? false;

        foreach ($courseEnrollments as $courseEnrollment) {
            if ($courseEnrollment->getId() === $actualEnrollmentId) {
                $actualEnrollmentObject = $courseEnrollment;
            }
        }

        $this->assertNotNull($actualEnrollmentObject, 'Unable to find matching first enrolment entity.');
        $this->assertEquals($actualEnrollmentId, $actualEnrollmentObject->getId());
        $this->assertEquals($actualEnrollmentName, $actualEnrollmentObject->getStudentName());
        $this->assertEquals($actualEnrollmentEmail, $actualEnrollmentObject->getStudentEmail());

        $enrolmentProgressLogs = $this->progressLogRepository->findBy(['enrolment' => $actualEnrollmentObject]);
        $this->assertGreaterThan(0, count($enrolmentProgressLogs), 'This enrolment has no progress logs. Cannot proceed with the unit test. Please check your data and try again.');

        $this->assertIsArray($actualFirstEnrollment['progress_logs']);
        $actualFirstProgressLogObject = null;
        $actualFirstProgressLog = $actualFirstEnrollment['progress_logs'][0];
        $actualFirstProgressLogId = $actualFirstProgressLog['id'] ?? false;
        $actualFirstProgressLogModuleName = $actualFirstProgressLog['module_name'] ?? false;
        $actualFirstProgressLogScore = $actualFirstProgressLog['score'] ?? false;
        $actualFirstProgressLogStatus = $actualFirstProgressLog['status'] ?? false;

        foreach ($enrolmentProgressLogs as $enrolmentProgressLog) {
            if ($enrolmentProgressLog->getId() === $actualFirstProgressLogId) {
                $actualFirstProgressLogObject = $enrolmentProgressLog;
            }
        }

        $this->assertNotNull($actualFirstProgressLogObject, 'Unable to find matching first progress log entity.');
        $this->assertSameSize($actualFirstEnrollment['progress_logs'], $enrolmentProgressLogs);
        $this->assertEquals($actualFirstProgressLogId, $actualFirstProgressLogObject->getId());
        $this->assertEquals($actualFirstProgressLogModuleName, $actualFirstProgressLogObject->getModuleName());
        $this->assertEquals($actualFirstProgressLogScore, $actualFirstProgressLogObject->getScore());
        $this->assertEquals($actualFirstProgressLogStatus, $actualFirstProgressLogObject->getStatus()->value);
    }
}
