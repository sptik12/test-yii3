<?php

namespace App\Backend\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Http\Method;

final class TrimRequestData implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $isAjaxRequest = strtolower($request->getHeaderLine("X-Requested-With")) == "xmlhttprequest";
        $isPostRequest = $request->getMethod() == Method::POST;

        if ($isAjaxRequest || $isPostRequest) {
            $queryParams = $request->getQueryParams();
            $parsedBody = $request->getParsedBody();

            if (!empty($queryParams)) {
                $request->withQueryParams($this->trimAll($queryParams));
            }

            if (!empty($parsedBody)) {
                $request->withParsedBody($this->trimAll($parsedBody));
            }
        }

        return $handler->handle($request);
    }





    public function trimAll(array $data, array $except = []): array
    {
        $trimmed = [];

        foreach ($data as $field => $value) {
            if (is_array($value)) {
                $value = $this->trimAll($value, $except);
            } elseif (!in_array($field, $except) && !is_object($value)) {
                $value = trim($value);
            }

            $trimmed[$field] = $value;
        }

        return $trimmed;
    }
}
