<?php

namespace App\Backend\Middleware;

use App\Backend\Exception\ValidationException;
use App\Backend\Exception\CarApiException;
use App\Backend\Exception\GeoCodeApiException;
use App\Backend\Service\NotyService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\DataResponse\Formatter\JsonDataResponseFormatter;
use Yiisoft\Http\Header;
use Yiisoft\Http\Method;
use Yiisoft\Http\Status;
use Yiisoft\Session\Flash\FlashInterface;
use Yiisoft\Translator\TranslatorInterface;

final class ExceptionFormatter implements MiddlewareInterface
{
    public function __construct(
        private FlashInterface $flashSession,
        private NotyService $noty,
        private LoggerInterface $logger,
        private ResponseFactoryInterface $responseFactory,
        private DataResponseFactoryInterface $dataResponseFactory,
        private TranslatorInterface $translator,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $isAjaxRequest = strtolower($request->getHeaderLine("X-Requested-With")) == "xmlhttprequest";
        $isPostRequest = $request->getMethod() == Method::POST;

        if ($isAjaxRequest) {
            return $this->handleAjaxRequest($request, $handler);
        } elseif ($isPostRequest) {
            return $this->handlePostRequest($request, $handler);
        }

        return $handler->handle($request);
    }





    private function handleAjaxRequest(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (ValidationException $e) {
            $errors = $e->getErrors();
            $message = $e->getMessage();

            return $this->dataResponseFactory
                ->createResponse(compact("errors", "message"), $e->getCode() ?: Status::BAD_REQUEST)
                ->withResponseFormatter(new JsonDataResponseFormatter());
        } catch (CarApiException $e) {
            $errors = [['source' => 'vinCode', 'message' => $e->getMessage()]];
            $message = $e->getMessage();

            return $this->dataResponseFactory
                ->createResponse(compact("errors", "message"), $e->getCode() ?: Status::BAD_REQUEST)
                ->withResponseFormatter(new JsonDataResponseFormatter());
        } catch (GeoCodeApiException $e) {
            $errors = [['source' => 'general', 'message' => $e->getMessage()]];
            $message = $e->getMessage();

            return $this->dataResponseFactory
                ->createResponse(compact("errors", "message"), $e->getCode() ?: Status::BAD_REQUEST)
                ->withResponseFormatter(new JsonDataResponseFormatter());
        } catch (\Throwable $e) {
            $this->logger->error($e);
            $errors = [['source' => 'general', 'message' => $e->getMessage()]];
            $message = $this->translator->translate("An unexpected error occurred. Please, try again or contact Administration of error persists");

            return $this->dataResponseFactory
                ->createResponse(compact("errors", "message"), Status::INTERNAL_SERVER_ERROR)
                ->withResponseFormatter(new JsonDataResponseFormatter());
        }
    }

    private function handlePostRequest(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (ValidationException $e) {
            $this->saveRequestBodyToFlashSession($request);
            $this->saveErrorsToFlashSession($e->getErrors());

            return $this->responseFactory
                ->createResponse(Status::FOUND)
                ->withHeader(Header::LOCATION, $request->getHeader("Referer"));
        } catch (CarApiException $e) {
            $this->saveRequestBodyToFlashSession($request);
            $this->saveErrorsToFlashSession([["message" => $e->getMessage(), "source" => "vinCode"]]);

            return $this->responseFactory
                ->createResponse(Status::FOUND)
                ->withHeader(Header::LOCATION, $request->getHeader("Referer"));
        } catch (GeoCodeApiException $e) {
            $this->saveRequestBodyToFlashSession($request);
            $this->noty->add(
                type: "error",
                text: $e->getMessage()
            );

            return $this->responseFactory
                ->createResponse(Status::FOUND)
                ->withHeader(Header::LOCATION, $request->getHeader("Referer"));
        } catch (\Throwable $e) {
            $this->logger->error($e);
            $this->saveRequestBodyToFlashSession($request);
            $this->noty->add(
                type: "error",
                text: $this->translator->translate("An unexpected error occurred. Please, try again or contact Administration of error persists"),
            );

            return $this->responseFactory
                ->createResponse(Status::FOUND)
                ->withHeader(Header::LOCATION, $request->getHeader("Referer"));
        }
    }

    private function saveRequestBodyToFlashSession(RequestInterface $request): void
    {
        $excludeKeys = ["password" => true, "_csrf" => true];
        $requestToSave = array_diff_key($request->getParsedBody(), $excludeKeys);

        $this->flashSession->set(
            "savedRequestBody",
            (object)$requestToSave
        );
    }

    private function saveErrorsToFlashSession(array $errors): void
    {
        $this->flashSession->set(
            "responseErrors",
            $errors
        );
    }
}
