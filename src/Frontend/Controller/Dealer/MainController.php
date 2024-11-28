<?php

namespace App\Frontend\Controller\Dealer;

use Psr\Http\Message\ResponseInterface;

final class MainController extends AbstractDealerController
{
    /**
     * Pages
     */
    public function index(): ResponseInterface
    {
        return $this->redirectByName("dealer.searchCar");
    }
}
