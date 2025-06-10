<?php

namespace App\MessageHandler;

use App\Message\MailReminderMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class MailReminderMessageHandler
{
    public function __invoke(MailReminderMessage $message): void
    {
        // do something with your message
    }
}
