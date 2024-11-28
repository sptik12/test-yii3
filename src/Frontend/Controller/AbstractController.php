<?php

namespace App\Frontend\Controller;

use App\Backend\Service\NotyService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Http\Header;
use Yiisoft\Http\Method;
use Yiisoft\Http\Status;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\ViewRenderer;
use Yiisoft\Config\ConfigInterface;

abstract class AbstractController
{
    public function __construct(
        ?string $layout = null,
        protected ViewRenderer $viewRenderer,
        protected ResponseFactoryInterface $responseFactory,
        protected UrlGeneratorInterface $urlGenerator,
    ) {
        $this->viewRenderer = $viewRenderer
            ->withController($this)
            ->withLayout("@views/layout/{$layout}");
    }

    public function showGeneralNotyError(NotyService $noty, TranslatorInterface $translator): void
    {
        $noty->add(
            "error",
            $this->getGeneralError($translator)
        );
    }

    public function getGeneralError(TranslatorInterface $translator): string
    {
        return $translator->translate("An unexpected error occurred during login. Please, try again or contact Administration of error persists");
    }





    protected function redirect(string $url): ResponseInterface
    {
        return $this->responseFactory
            ->createResponse(Status::FOUND)
            ->withHeader(Header::LOCATION, $url);
    }

    protected function redirectByName(string $name, array $arguments = [], array $queryParameters = []): ResponseInterface
    {
        return $this->responseFactory
            ->createResponse(Status::FOUND)
            ->withHeader(Header::LOCATION, $this->urlGenerator->generate(name: $name, arguments: $arguments, queryParameters: $queryParameters));
    }

    protected function redirectByNameAbsolute(string $name, array $arguments = [], array $queryParameters = []): ResponseInterface
    {
        return $this->responseFactory
            ->createResponse(Status::FOUND)
            ->withHeader(Header::LOCATION, $this->urlGenerator->generateAbsolute(name: $name, arguments: $arguments, queryParameters: $queryParameters));
    }

    protected function getRequestData(ServerRequestInterface $request): object|array|null
    {
        return ($request->getMethod() === Method::POST)
            ? $request->getParsedBody()
            : $request->getQueryParams();
    }

    protected function extractQuerySearchParameters(
        ServerRequestInterface|string $request,
        ConfigInterface $config,
        $default = []
    ): array {
        $sort = $default['sort'] ?? null;
        $sortOrder = $default['sortOrder'] ?? null;
        $page = $default['page'] ?? 1;
        $params = $config->get('params-web');
        $perPage = $default['perPage'] ?? $params['defaultPageParams']['perPage'];

        if (is_string($request)) {
            parse_str(parse_url($request, PHP_URL_QUERY), $query);
        } else {
            $query = $request->getQueryParams()  ;
        }

        if (array_key_exists("sort", $query)) {
            $sort = $query['sort'];
            unset($query['sort']);
        }

        if (array_key_exists("sortOrder", $query)) {
            $sortOrder = $query['sortOrder'];
            unset($query['sortOrder']);
        }

        if (array_key_exists("page", $query)) {
            $page = (int)$query['page'];
            unset($query['page']);
        }

        if (array_key_exists("perPage", $query)) {
            $perPage = (int)$query['perPage'];
            unset($query['perPage']);
        }

        $queryFilters = $query;

        return [$queryFilters, $sort, $sortOrder, $page, $perPage];
    }
}
