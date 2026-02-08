<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Course;
use App\Repository\EnrolmentRepository;
use App\Repository\ProgressLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

final class CoursesControllerTest extends WebTestCase
{
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->em->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->em->rollback();
        parent::tearDown();
    }

    public function testGetCourseReportReturnsExpectedJsonStructure(): void
    {
        $course = new Course();
        $course->setTitle('PHP for Beginners');

        $enrolment1 = new Enrolment();
        $enrolment1->setStudentName('Alice Smith');
        $enrolment1->setStudentEmail('alice@example.com');
        $enrolment1->setCourse($course);           // assuming setter exists

        $enrolment2 = new Enrolment();
        $enrolment2->setStudentName('Bob Jones');
        $enrolment2->setStudentEmail('bob@example.com');
        $enrolment2->setCourse($course);

        $log1 = new ProgressLog();
        $log1->setEnrolment($enrolment1);
        $log1->setModuleName('Introduction');
        $log1->setStatus(ProgressStatus::COMPLETED);
        $log1->setScore(92);

        $log2 = new ProgressLog();
        $log2->setEnrolment($enrolment1);
        $log2->setModuleName('Basics');
        $log2->setStatus(ProgressStatus::IN_PROGRESS);
        $log2->setScore(45);

        $this->em->persist($course);
        $this->em->persist($enrolment1);
        $this->em->persist($enrolment2);
        $this->em->persist($log1);
        $this->em->persist($log2);
        $this->em->flush();

        $client = static::createClient();
        $client->request(
            'GET',
            '/api/v1/courses/' . $course->getId() . '/report'
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $this->assertJson($client->getResponse()->getContent());

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals('PHP for Beginners', $data['course_title']);
        $this->assertCount(2, $data['enrolled_students']);

        // Check first student (ordered by id ASC)
        $student1 = $data['enrolled_students'][0];
        $this->assertEquals('Alice Smith', $student1['name']);
        $this->assertEquals('alice@example.com', $student1['email']);
        $this->assertCount(2, $student1['progress_logs']);

        $this->assertEquals('Introduction', $student1['progress_logs'][0]['module_name']);
        $this->assertEquals('COMPLETED',   $student1['progress_logs'][0]['status']);
        $this->assertEquals(92,            $student1['progress_logs'][0]['score']);

        // Second student → no logs
        $student2 = $data['enrolled_students'][1];
        $this->assertEquals('Bob Jones', $student2['name']);
        $this->assertCount(0, $student2['progress_logs']);
    }
}
