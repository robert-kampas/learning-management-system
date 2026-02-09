<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Enrolment;
use App\Entity\ProgressLog;
use App\Enum\ProgressLogType;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Course;

final class CoursesControllerTest extends WebTestCase
{
    public function testGetCourseReportReturnsExpectedJsonStructure(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        // create test data
        $course1 = new Course();
        $course1->setTitle('Excepturi aut adipisci voluptates eius hic.');
        $entityManager->persist($course1);

        $enrolment1 = new Enrolment();
        $enrolment1->setStudentEmail('ressie21@yahoo.com');
        $enrolment1->setStudentName('Adeline Stehr');
        $enrolment1->setEnrolledAt($course1);
        $entityManager->persist($enrolment1);

        $enrolment2 = new Enrolment();
        $enrolment2->setStudentEmail('verda22@zemlak.com');
        $enrolment2->setStudentName('Bertram Kuhlman');
        $enrolment2->setEnrolledAt($course1);
        $entityManager->persist($enrolment2);

        $progressLog1 = new ProgressLog();
        $progressLog1->setModuleName('Eum quisquam reprehenderit sit odit vel.');
        $progressLog1->setScore(951);
        $progressLog1->setStatus(ProgressLogType::STARTED);
        $progressLog1->setEnrolment($enrolment2);
        $entityManager->persist($progressLog1);

        $progressLog2 = new ProgressLog();
        $progressLog2->setModuleName('Quae at et vero molestiae et aliquam.');
        $progressLog2->setScore(457);
        $progressLog2->setStatus(ProgressLogType::COMPLETED);
        $progressLog2->setEnrolment($enrolment2);
        $entityManager->persist($progressLog2);

        $entityManager->flush();

        // make request
        $client->request('GET', sprintf('/api/v1/courses/%s/report', $course1->getId()));

        // assert response
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $this->assertJson($client->getResponse()->getContent());

        // decode response data
        $data = json_decode($client->getResponse()->getContent(), true, 6, JSON_THROW_ON_ERROR);

        // assert response data structure
        $this->assertSame($course1->getTitle(), $data['course_title']);
        $this->assertCount(2, $data['enrolled_students']);

        $secondEnrollment = $data['enrolled_students'][1];

        $this->assertSame($secondEnrollment['id'], $enrolment2->getId());
        $this->assertSame($secondEnrollment['name'], $enrolment2->getStudentName());
        $this->assertSame($secondEnrollment['email'], $enrolment2->getStudentEmail());
        $this->assertCount(2, $secondEnrollment['progress_logs']);

        $secondEnrollmentFirstProgressLog = $secondEnrollment['progress_logs'][0];

        $this->assertSame($secondEnrollmentFirstProgressLog['id'], $progressLog1->getId());
        $this->assertSame($secondEnrollmentFirstProgressLog['module_name'], $progressLog1->getModuleName());
        $this->assertSame($secondEnrollmentFirstProgressLog['score'], $progressLog1->getScore());
        $this->assertSame($secondEnrollmentFirstProgressLog['status'], $progressLog1->getStatus()->value);

        $secondEnrollmentSecondProgressLog = $secondEnrollment['progress_logs'][1];

        $this->assertSame($secondEnrollmentSecondProgressLog['id'], $progressLog2->getId());
        $this->assertSame($secondEnrollmentSecondProgressLog['module_name'], $progressLog2->getModuleName());
        $this->assertSame($secondEnrollmentSecondProgressLog['score'], $progressLog2->getScore());
        $this->assertSame($secondEnrollmentSecondProgressLog['status'], $progressLog2->getStatus()->value);
    }

    public function testGetCourseReportReturnsExpectedJsonStructureWhenCourseHasNoEnrolments(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        // create test data
        $course = new Course();
        $course->setTitle('Excepturi aut adipisci voluptates eius hic.');
        $entityManager->persist($course);

        $entityManager->flush();

        // make request
        $client->request('GET', sprintf('/api/v1/courses/%s/report', $course->getId()));

        // decode response data
        $data = json_decode($client->getResponse()->getContent(), true, 3, JSON_THROW_ON_ERROR);

        // assert response data structure
        $this->assertSame($course->getTitle(), $data['course_title']);
        $this->assertEmpty($data['enrolled_students']);
    }

    public function testGetCourseReportReturnsExpectedJsonStructureWhenEnrollmentHasNoProgressLogs(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        // create test data
        $course = new Course();
        $course->setTitle('Excepturi aut adipisci voluptates eius hic.');
        $entityManager->persist($course);

        $enrolment = new Enrolment();
        $enrolment->setStudentEmail('ressie21@yahoo.com');
        $enrolment->setStudentName('Adeline Stehr');
        $enrolment->setEnrolledAt($course);
        $entityManager->persist($enrolment);

        $entityManager->flush();

        // make request
        $client->request('GET', sprintf('/api/v1/courses/%s/report', $course->getId()));

        // decode response data
        $data = json_decode($client->getResponse()->getContent(), true, 5, JSON_THROW_ON_ERROR);

        // assert response data structure
        $this->assertSame($course->getTitle(), $data['course_title']);
        $this->assertCount(1, $data['enrolled_students']);

        $enrollmentData = $data['enrolled_students'][0];

        $this->assertSame($enrollmentData['id'], $enrolment->getId());
        $this->assertSame($enrollmentData['name'], $enrolment->getStudentName());
        $this->assertSame($enrollmentData['email'], $enrolment->getStudentEmail());
        $this->assertEmpty($enrollmentData['progress_logs']);
    }

    public function testGetCourseReportReturns404ForInvalidUuid(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/v1/courses/019c3f6c-8350-7af5-808d-invalid/report');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetCourseReportReturns404ForNonExistingId(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/v1/courses/00000000-0000-0000-0000-000000000000/report');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetCourseReportReturns404ForUuidThatIsNotV7(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/v1/courses/d72cd76b-ffc2-44bc-81f1-f8e400bc1bc5/report');

        $this->assertResponseStatusCodeSame(404);
    }
}
