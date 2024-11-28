<?php

namespace App\Backend\Component\Notificator;

use App\Frontend\ApplicationParameters;
use Yiisoft\Mailer\MailerInterface;

class EmailSender
{
    public function __construct(
        protected MailerInterface $mailer,
        protected ApplicationParameters $applicationParameters
    ) {
    }

    public function send(array $notification): void
    {
        $content = json_decode($notification['content']);
        $vars = property_exists($content, "vars") ? (array)$content->vars : [];
        $emailParts = $this->emailSplit($notification['from']);
        $from = $emailParts['name'] ? [$emailParts['email'] => $emailParts['name']] : $emailParts['email'];
        $vars['applicationParameters'] = $this->applicationParameters;

        $message = $this->mailer
            ->compose("{$notification['lang']}/{$content->template}", $vars)
            ->withFrom($from)
            ->withTo($notification['to'])
            ->withSubject($notification['subject']);

        $this->mailer->send($message);
    }





    private function emailSplit($email): array
    {
        preg_match('/(.*)<(.*)>/', $email, $matches);

        if (count($matches) == 3) {
            return [
                'name' => trim($matches[1]),
                'email' => trim($matches[2])
            ];
        } else {
            return [
                'name' => null,
                'email' => trim($email)
            ];
        }
    }
}
