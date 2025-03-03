<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Playlist;
use App\Errors\PlaylistNotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 *
 */
class BasicController
{
    /**
     * Отправляет сообщение о том, что метод не найден с кодом страницы 404
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function notFound(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $response->withStatus(404);
        $this->view($request, $response, 'notfound.twig');

        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param string $template
     * @param array $data
     * @return ResponseInterface
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function view(
        ServerRequestInterface $request,
        ResponseInterface $response,
        string $template,
        array $data = [],
    ): ResponseInterface {
        $view = Twig::fromRequest($request);
        return $view->render($response, $template, $data);
    }

    /**
     * Возвращает плейлист по его ID для обработки
     *
     * @param string $id
     * @param bool $asJson
     * @return Playlist
     * @throws Exception
     */
    protected function getPlaylist(string $id, bool $asJson = false): Playlist
    {
        ini()->load();

        if (ini()->getRedirection($id)) {
            $redirectTo = base_url(ini()->getRedirection($id) . ($asJson ? '/json' : '/details'));
            Flight::redirect($redirectTo);
            die;
        }

        try {
            return ini()->getPlaylist($id);
        } catch (PlaylistNotFoundException) {
            $this->notFound($id, $asJson);
            die;
        }
    }
}
