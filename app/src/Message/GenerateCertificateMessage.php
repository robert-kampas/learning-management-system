<?php

declare(strict_types=1);

namespace App\Message;

final readonly class GenerateCertificateMessage
{
     public function __construct(
         public int $enrollmentId
     ) {
     }

    public function getEnrollmentId(): int
    {
        return $this->enrollmentId;
    }
}
