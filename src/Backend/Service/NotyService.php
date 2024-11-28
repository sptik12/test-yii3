<?php

declare(strict_types=1);

namespace App\Backend\Service;

use InvalidArgumentException;

class NotyService
{
    private string $sessionKey = "noty";

    public function __construct(
        private readonly \Yiisoft\Session\SessionInterface $session,
    ) {
    }

    public function add(
        string $type,
        string $text,
        string $layout = "topRight",
        int $timeoutInMilliseconds = 5000,
        array $closeWith = ['click'],
    ): void {
        $this->checkArgumentTypeForAdd($type);
        $this->checkArgumentLayoutForAdd($layout);
        $timeout = $timeoutInMilliseconds;
        $newMessage = (object)compact("type", "text", "layout", "timeout", "closeWith");
        $messages = $this->getAll();
        $messages[] = $newMessage;
        $this->session->set($this->sessionKey, $messages);
    }

    public function getAll(): array
    {
        $default = [];

        return $this->session->get($this->sessionKey, $default);
    }

    public function flush(): void
    {
        $this->session->remove($this->sessionKey);
    }

    public function getAllAndFlush(): array
    {
        $messages = $this->getAll();
        $this->flush();

        return $messages;
    }





    private function checkArgumentTypeForAdd(string $type): void
    {
        $allowedTypes = ["alert", "success", "warning", "error", "info", "information"];

        if (!in_array($type, $allowedTypes)) {
            $allowedTypesString = implode("','", $allowedTypes);
            throw new InvalidArgumentException("Unexpected type. Allowed: '{$allowedTypesString}' but '{$type}' given");
        }
    }

    private function checkArgumentLayoutForAdd(string $layout): void
    {
        $allowedLayouts = ["top", "topLeft", "topCenter", "topRight", "center", "centerLeft", "centerRight", "bottom", "bottomLeft", "bottomCenter", "bottomRight"];

        if (!in_array($layout, $allowedLayouts)) {
            $allowedLayoutsString = implode("','", $allowedLayouts);
            throw new InvalidArgumentException("Unexpected layout. Allowed: '{$allowedLayoutsString}' but '{$layout}' given");
        }
    }
}
