<?php

declare(strict_types=1);

namespace App\Frontend\Controller;

use Psr\Http\Message\ResponseInterface;

final class SiteController extends AbstractController
{
    public function index(): ResponseInterface
    {
        return $this->viewRenderer->render("index");
    }

    public function wishlist(): ResponseInterface
    {
        return $this->viewRenderer->render("wishlist");
    }
}
