<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ChannelLogo;
use App\Errors\PlaylistNotFoundException;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 *
 */
class WebController extends BasicController
{
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function home(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        ini()->load();

        $playlists = ini()->playlists(false);
        $count = count($playlists);
        $page = (int)($request->getAttributes()['page'] ?? $request->getQueryParams()['page'] ?? 1);
        $pageSize = config('app.page_size');
        $pageCount = ceil($count / $pageSize);
        $offset = max(0, ($page - 1) * $pageSize);
        $list = array_slice($playlists, $offset, $pageSize, true);

        return $this->view($request, $response, 'list.twig', [
            'updated_at' => ini()->updatedAt(),
            'playlists' => $list,
            'count' => $count,
            'pageCount' => $pageCount,
            'pageCurrent' => $page,
        ]);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function faq(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->view($request, $response, 'faq.twig');
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function redirect(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        ini()->load();
        $code = $request->getAttributes()['code'];
        try {
            $playlist = ini()->getPlaylist($code);
            return $response->withHeader('Location', $playlist->pls);
        } catch (PlaylistNotFoundException) {
            return $this->notFound($request, $response);
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws \Random\RandomException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function details(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        ini()->load();
        $code = $request->getAttributes()['code'];
        try {
            $playlist = ini()->getPlaylist($code);
            $response->withHeader('Location', $playlist->pls);
        } catch (PlaylistNotFoundException) {
            return $this->notFound($request, $response);
        }

        $playlist->fetchContent();
        $playlist->parse();

        return $this->view($request, $response, 'details.twig', $playlist->toArray());
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function logo(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $input = $request->getQueryParams()['url'] ?? null;

        $logo = new ChannelLogo($input);
        $logo->readFile() || $logo->fetch();
        $logo->size() === 0 && $logo->setDefault();
        $logo->store();
        $body = $logo->raw();
        $size = $logo->size();
        $mime = $logo->mimeType();

        $response->getBody()->write($body);
        return $response->withHeader('Content-Type', $mime)
            ->withHeader('Content-Length', $size);
    }
}
