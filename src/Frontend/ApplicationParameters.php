<?php

declare(strict_types=1);

namespace App\Frontend;

final class ApplicationParameters
{
    private string $charset = "UTF-8";
    private string $name = "Carwow";
    private string $phone = "1-800-Carwow";
    private string $url = "http:/www.carwow.com";
    private string $supportEmail = "support@carwow.com";

    public function getCharset(): string
    {
        return $this->charset;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getSupportEmail(): string
    {
        return $this->supportEmail;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function charset(string $value): self
    {
        $new = clone $this;
        $new->charset = $value;

        return $new;
    }

    public function name(string $value): self
    {
        $new = clone $this;
        $new->name = $value;

        return $new;
    }

    public function phone(string $value): self
    {
        $new = clone $this;
        $new->phone = $value;

        return $new;
    }

    public function supportEmail(string $value): self
    {
        $new = clone $this;
        $new->supportEmail = $value;

        return $new;
    }

    public function url(string $value): self
    {
        $new = clone $this;
        $new->url = $value;

        return $new;
    }
}
