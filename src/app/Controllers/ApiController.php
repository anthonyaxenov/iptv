<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Random\RandomException;

/**
 *
 */
class ApiController extends BasicController
{
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws RandomException
     */
    public function json(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $code = $request->getAttributes()['code'];
        $playlist = $this->getPlaylist($code, true);
        $playlist->fetchContent();
        $playlist->parse();

        $json = json_encode($playlist->toArray(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $response->getBody()->write($json);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Content-Length', strlen($json));
    }
}
