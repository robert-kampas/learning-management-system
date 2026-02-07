<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\CourseRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsCommand(name: 'app:list-course-report-urls')]
final class ListCourseReportsEndpointsCommand extends Command
{
    public function __construct(
        private CourseRepository $courseRepository,
        private UrlGeneratorInterface $urlGenerator
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $allCourses = $this->courseRepository->findAll();

        foreach ($allCourses as $course) {
            $courseReportUrl = sprintf('http://0.0.0.0:8889%s', $this->urlGenerator->generate('course_report', ['id' => $course->getId()]));
            $io->writeln($courseReportUrl);
        }

        return Command::SUCCESS;
    }
}
