<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\EnrolmentRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsCommand(name: 'app:list-enrollment-certificate-urls')]
final class ListEnrollmentCertificateEndpointsCommand extends Command
{
    public function __construct(
        private readonly EnrolmentRepository   $enrolmentRepository,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $someEnrollments = $this->enrolmentRepository->findBy([], [], 5);

        foreach ($someEnrollments as $enrollment) {
            $courseReportUrl = sprintf('http://0.0.0.0:8889%s', $this->urlGenerator->generate('generate_enrollment_certificate', ['id' => $enrollment->getId()]));
            $io->block($courseReportUrl, 'POST', 'fg=green', '');
        }

        return Command::SUCCESS;
    }
}
