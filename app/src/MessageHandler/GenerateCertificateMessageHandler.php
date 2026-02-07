<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\GenerateCertificateMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GenerateCertificateMessageHandler
{
    public function __invoke(GenerateCertificateMessage $message): void
    {
        // do something with your message
    }
}
