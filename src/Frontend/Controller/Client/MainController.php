<?php

namespace App\Frontend\Controller\Client;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class MainController extends AbstractClientController
{
    public function index(
    ): ResponseInterface {
        return $this->redirectByName("client.searchCar");
    }

    public function session(
        ServerRequestInterface $request
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);

        return $this->viewRenderer->render("session", ['returnUrl' => $requestData['returnUrl']]);
    }

    public function wishlist(
    ): ResponseInterface {
        return $this->viewRenderer->render("wishlist");
    }
}
