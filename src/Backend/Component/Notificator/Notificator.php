<?php

namespace App\Backend\Component\Notificator;

use Psr\Log\LoggerInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Query\Query;
use Yiisoft\Db\Expression\Expression;
use Yiisoft\Injector\Injector;

class Notificator
{
    public function __construct(
        protected Injector $injector,
        protected ConnectionInterface $db,
        protected LoggerInterface $logger
    ) {
    }

    public function push(
        string $from,
        string $to,
        string $subject,
        array|\stdClass $content,
        string $event,
        int $delay = 0,
        string $method = "email",
        array|\stdClass|null $related = null,
        string $status = "new",
        string $lang = "en"
    ): int {
        $notification = compact("from", "to", "subject", "content", "event", "delay", "method", "related", "status", "lang");

        return $this->add($notification);
    }

    public function fire(
        string $from,
        string $to,
        string $subject,
        array|\stdClass $content,
        string $event,
        int $delay = 0,
        string $method = "email",
        array|\stdClass|null $related = null
    ): void {
        $notification = compact("from", "to", "subject", "content", "event", "delay", "method", "related");
        $notificationId = $this->add($notification);
        $notification['id'] = $notificationId;
        $this->send($notification);
    }

    public function dispatch(): void
    {
        $notifications = (new Query($this->db))
            ->from("notification")
            ->where(['status' => Status::New->value])
            ->andWhere(new Expression("NOW() > DATE_ADD(`created`, INTERVAL `delay` SECOND)"))
            ->all();

        foreach ($notifications as $notification) {
            $this->send($notification);
        }
    }





    private function send(array $notification): void
    {
        try {
            $this->entrustToSender($notification);
            $this->markAs(Status::Done->value, $notification['id']);
        } catch (\Throwable $e) {
            $this->logger->error($e);
            $this->markAs(Status::Failed->value, $notification['id']);
        }
    }

    private function entrustToSender(array $notification): void
    {
        $class = "\App\Backend\Component\Notificator\\" . ucfirst($notification['method']) . "Sender";
        $sender = $this->injector->make($class);
        $sender->send($notification);
    }

    private function markAs(string $status, int $notificationId)
    {
        $finished = new Expression("NOW()");

        $this->db->createCommand()
            ->update("notification", compact("status", "finished"), ['id' => $notificationId])
            ->execute();
    }

    private function add(array $notification)
    {
        $this->db->createCommand()
            ->insert("notification", $notification)
            ->execute();
        $notificationId = $this->db->getLastInsertID();

        return $notificationId;
    }
}
