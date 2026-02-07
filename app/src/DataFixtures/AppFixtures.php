<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Course;
use App\Entity\Enrolment;
use App\Entity\ProgressLog;
use App\Enum\ProgressType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

final class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($c = 0; $c < 5; $c++) {
            $faker = FakerFactory::create();

            $course = new Course();
            $course->setTitle($faker->text(100));
            $course->setDescription($faker->text(800));

            for ($e = 0; $e < 50; $e++) {
                $enrolment = new Enrolment();
                $enrolment->setStudentName(sprintf('%s %s', $faker->firstName(), $faker->lastName()));
                $enrolment->setStudentEmail($faker->email());
                $enrolment->setEnrolledAt($course);

                $manager->persist($enrolment);

                for ($l = 0; $l < rand(0, 5); $l++) {
                    $progressLog = new ProgressLog();
                    $progressLog->setEnrolment($enrolment);
                    $progressLog->setModuleName($faker->text(50));
                    $progressLog->setStatus(rand(0, 1) ? ProgressType::STARTED : ProgressType::COMPLETED);
                    $progressLog->setScore(rand(0, 1000));
                    $progressLog->setEnrolment($enrolment);

                    $manager->persist($progressLog);
                }
            }

            $manager->persist($course);
        }

        $manager->flush();
    }
}
