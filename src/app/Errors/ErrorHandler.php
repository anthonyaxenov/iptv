<?php

declare(strict_types=1);

namespace App\Errors;

use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface};
use Psr\Log\LoggerInterface;
use Slim\Handlers\ErrorHandler as SlimErrorHandler;
use Throwable;

/**
 * Обработчик ошибок
 */
class ErrorHandler extends SlimErrorHandler
{
    /**
     * Логирует ошибку и отдаёт JSON-ответ с необходимым содержимым
     *
     * @param ServerRequestInterface $request
     * @param Throwable $exception
     * @param bool $displayErrorDetails
     * @param bool $logErrors
     * @param bool $logErrorDetails
     * @param LoggerInterface|null $logger
     * @return ResponseInterface
     */
    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails,
        ?LoggerInterface $logger = null
    ): ResponseInterface {
        $payload = $this->payload($exception, $displayErrorDetails);

        $response = app()->getResponseFactory()->createResponse();
        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE));

        return $response;
    }

    /**
     * Возвращает структуру исключения для контекста
     *
     * @param Throwable $e Исключение
     * @param bool $logErrorDetails Признак дополнения деталями
     * @return array
     */
    protected function context(Throwable $e, bool $logErrorDetails): array
    {
        $result = ['code' => $e->getCode()];

        $logErrorDetails && $result += [
            'class' => $e::class,
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTrace()
        ];

        return $result;
    }

    /**
     * Возвращает структуру исключения для передачи в ответе
     *
     * @param Throwable $e Исключение
     * @param bool $displayErrorDetails Признак дополнения деталями
     * @return array
     */
    protected function payload(Throwable $e, bool $displayErrorDetails): array
    {
        $result = [
            'error' => [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ],
        ];

        $displayErrorDetails && $result['error'] += [
            'class' => $e::class,
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTrace(),
        ];

        return $result;
    }
}
