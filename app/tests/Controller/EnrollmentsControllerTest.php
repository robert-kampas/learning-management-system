<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Course;
use App\Entity\Enrolment;
use App\Message\GenerateCertificateMessage;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

final class EnrollmentsControllerTest extends WebTestCase
{
    public function testGenerateEnrollmentCertificateDispatchesMessage(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        $course = new Course();
        $course->setTitle('Excepturi aut adipisci voluptates eius hic.');

        $enrolment = new Enrolment();
        $enrolment->setStudentEmail('ressie21@yahoo.com');
        $enrolment->setStudentName('Adeline Stehr');
        $enrolment->setEnrolledAt($course);

        $em->persist($course);
        $em->persist($enrolment);
        $em->flush();

        $requestUrl = sprintf('/api/v1/enrollments/%s/certificate', $enrolment->getId());
        dump("Testing $requestUrl");
        $client->request('POST', $requestUrl);

        // Assert response
        $this->assertResponseStatusCodeSame(202);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEmpty($data);

        // Assert message was dispatched
        /** @var InMemoryTransport $transport */
        $transport = static::getContainer()->get('messenger.transport.async');
        $messages = $transport->getSent();

        $this->assertCount(1, $messages);
        $this->assertInstanceOf(GenerateCertificateMessage::class, $messages[0]->getMessage());
        $this->assertSame($enrolment->getId(), $messages[0]->getMessage()->getEnrollmentId());
    }

    public function testGenerateEnrollmentCertificateReturns404ForInvalidId(): void
    {
        $client = static::createClient();

        $requestUrl = '/api/v1/enrollments/999999/certificate';
        dump("Testing $requestUrl");
        $client->request('POST', $requestUrl);

        $this->assertResponseStatusCodeSame(404);
    }
}
