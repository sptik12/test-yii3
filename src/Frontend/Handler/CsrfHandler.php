<?php

declare(strict_types=1);

namespace App\Frontend\Handler;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\DataResponse\Formatter\JsonDataResponseFormatter;
use Yiisoft\Http\Header;
use Yiisoft\Http\Status;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Translator\TranslatorInterface;

final class CsrfHandler implements RequestHandlerInterface
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private UrlGeneratorInterface $urlGenerator,
        private CurrentRoute $currentRoute,
        private TranslatorInterface $translator,
        private DataResponseFactoryInterface $dataResponseFactory
    ) {
    }

    public function handle(
        ServerRequestInterface $request
    ): ResponseInterface {
        $isAjaxRequest = strtolower($request->getHeaderLine("X-Requested-With")) == "xmlhttprequest";

        if (!$isAjaxRequest) {
            return $this->responseFactory
                ->createResponse(Status::FOUND)
                ->withHeader(
                    Header::LOCATION,
                    $this->urlGenerator->generateAbsolute(
                        name: "client.session",
                        queryParameters: ['returnUrl' => (string)$this->currentRoute->getUri()],
                    ),
                );
        } else {
            $message = $this->translator->translate("Your session is expired. Please, reload the page and fill the form again");
            $errors = [['source' => 'general', 'message' => $message]];

            return $this->dataResponseFactory
                ->createResponse(compact("errors", "message"), Status::UNPROCESSABLE_ENTITY)
                ->withResponseFormatter(new JsonDataResponseFormatter());
        }
    }
}
