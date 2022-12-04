<?php

declare(strict_types=1);

namespace App\Controllers;

use Exception;
use Flight;

/**
 * Абстрактный контроллер для расширения
 */
abstract class Controller
{
    /**
     * Перебрасывает на страницу 404 при ненайденном плейлисте
     *
     * @param string $id
     * @return void
     * @throws Exception
     */
    public function notFound(string $id): void
    {
        Flight::response()->status(404)->sendHeaders();
        view('notfound', ['id' => $id]);
    }
}
