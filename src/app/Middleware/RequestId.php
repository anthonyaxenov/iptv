<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware для добавления запросу заголовка X-Request-ID
 */
class RequestId
{
    /**
     * Добавляет запросу заголовок X-Request-ID
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $request->withHeader('X-Request-ID', uniqid());

        return $handler->handle($request);
    }
}
