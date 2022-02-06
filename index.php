<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

$updated_at = date('d.m.Y h:i', filemtime('playlists.ini'));
$my_url = $_SERVER['SERVER_NAME'] . '?';
$ini = parse_ini_file('playlists.ini', true);

// получение инфы о плейлисте
if (!empty($_GET['getinfo'])) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $ini[$_GET['getinfo']]['pls']);
    unset($ini);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    $response = curl_exec($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = explode("\r\n", substr($response, 0, $header_size));
    $content = substr($response, $header_size);
    unset($response);
    unset($header_size);
    curl_close($ch);
    unset($ch);
    $matches = [];
    preg_match_all("/^#EXTINF:-?\d[\s]?,[\s]?(.*$)/m", $content, $matches);
    unset($content);
    $channels = $matches[1];
    unset($matches);
    $is_online = is_array($headers) && !empty($headers) && strpos($headers[0], ' 200') !== false;
    unset($headers);
    array_walk($channels, function (&$str) { $str = trim($str); });
    die(json_encode([
        'is_online' => $is_online,
        'count' => $is_online ? count($channels) : '-',
        'channels' => $channels,
    ]));
}

if (array_intersect(array_keys($_GET), array_keys($ini))) {
    $id = array_keys($_GET)[0];
    if (!empty($ini[$id]['redirect'])) {
        header('Location: ' . $ini[$ini[$id]['redirect']]['pls']);
        die;
    } elseif (!empty($ini[$id]['pls'])) {
        header('Location: ' . $ini[$id]['pls']);
        die;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IPTV Playlists</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-dark text-light">
<div class="col-lg-8 mx-auto p-3 py-md-5">
    <header class="pb-3 mb-5 border-bottom">
        <a href="/" class="text-light text-decoration-none">
            <h1>Самообновляемые плейлисты IPTV</h1>
        </a>
        <p class="small text-muted">
            <a href="https://github.com/anthonyaxenov/iptv">GitHub</a> | <a href="https://axenov.dev">axenov.dev</a><br/>
            Обновлено: <?=$updated_at?>&nbsp;МСК<br/>
            Плейлистов в списке:&nbsp;<strong><?=count($ini)?></strong>
        </p>
    </header>

    <main>
        <div class="container">
            <p>
                На этой странице собраны ссылки на IPTV-плейлисты, которые находятся в открытом доступе.
                Они бесплатны для использования. Список проверяется и обновляется мной вручную.
                Поддержкой этих плейлистов занимаются сервисы и ресурсы, указанные как источник.
                Вопросы работоспособности плейлистов адресуйте тем, кто несёт за них ответственность.
            </p>
            <p>Чтобы подключить плейлист, нужно в настройках IPTV-плеера указать ссылку из последней колонки.</p>
        </div>

        <div class="container py-5">
            <h2>Пояснение статусов проверки плейлистов</h2>
            <ui>
                <li>
                    <span class="badge small bg-warning text-dark">?</span> Загрузка данных.
                </li>
                <li>
                    <span class="badge small text-dark bg-success">online</span> Плейлист активен. В этом случае
                    могут даже подгрузиться список и количество каналов. А может и нет - тогда следует проверить вручную.
                </li>
                <li>
                    <span class="badge small text-dark bg-secondary">unknown</span> Состояние неизвестно.
                    Скорее всего, плейлист активен, но получить данные о нём не удалось. Следует проверить вручную.
                </li>
                <li>
                    <span class="badge small text-dark bg-secondary">timeout</span> Не удалось вовремя проверить плейлист.
                </li>
                <li>
                    <span class="badge small text-dark bg-danger">offline</span> Плейлист неактивен.
                </li>
                <li>
                    <span class="badge small text-dark bg-danger">error</span> Ошибка при проверке плейлиста.
                </li>
            </ui>
        </div>

        <div class="container py-5">
            <h2>Список плейлистов</h2>
            <table class="table table-dark table-hover">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Информация о плейлисте</th>
                    <th>Каналов</th>
                    <th title="Нажмите на ссылку, чтобы скопировать её в буфер обмена">Ссылка</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($ini as $id => $element) {
                    if (empty($element['pls'])) {
                        continue;
                    }
                    ?>
                    <tr class="pls" data-playlist-id="<?=$id?>">
                        <td class="text-center id">
                            <strong><?=$id?></strong>
                        </td>
                        <td class="info">
                            <strong><?=$element['name'] ?: "Плейлист #" . $id?></strong>
                            <span class="badge small bg-warning text-dark status">?</span>
                            <div class="small">
                                <a href="<?=$element['pls']?>"
                                   target="_blank"
                                   rel="noopener nofollow">M3U</a>
                                <?php
                                if (!empty($element['src'])) { ?>
                                    | <a href="<?=$element['src']?>"
                                         target="_blank"
                                         rel="noopener nofollow">Источник</a>
                                    <?php
                                } ?>
                                <?php
                                if (!empty($element['desc'])) { ?>
                                    <br/><p class="my-1"><?=$element['desc']?></p>
                                    <?php
                                } ?>
                            </div>
                        </td>
                        <td class="text-center count">
                            <div class="spinner-border text-success" role="status">
                                <span class="visually-hidden">загрузка...</span>
                            </div>
                        </td>
                        <td class="col-3">
                            <span onclick="prompt('Скопируйте адрес плейлиста', '<?=$my_url?><?=$id?>')"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="Нажмите на ссылку, чтобы скопировать её в буфер обмена"
                                    class="font-monospace">
                                <?=$my_url?><?=$id?>
                            </span>
                        </td>
                    </tr>
                    <?php
                } ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer class="py-4 text-center">
        <a href="https://github.com/anthonyaxenov/iptv">GitHub</a> | <a href="https://axenov.dev">axenov.dev</a>
    </footer>
</div>
<script>
    document.querySelectorAll('tr.pls').forEach((tr) => {
        const id = tr.attributes['data-playlist-id'].value
        const xhr = new XMLHttpRequest()
        xhr.responseType = 'json'
        xhr.timeout = 60000 // ms = 1 min
        let st_el = tr.querySelector('span.status')
        xhr.onreadystatechange = () => {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                console.log('[' + id + '] DONE', xhr.response)
                st_el.classList.remove('bg-warning')
                if (xhr.response) {
                    tr.querySelector('td.count').innerHTML = xhr.response.count
                    if (xhr.response.is_online === true) {
                        st_el.innerHTML = 'online'
                        st_el.classList.add('bg-success')
                        if (xhr.response.channels.length > 0) {
                            tr.querySelector('td.info').innerHTML += '<a class="small" ' +
                                'data-bs-toggle="collapse" data-bs-target="#channels-' + id + '" aria-expanded="false" ' +
                                'aria-controls="channels-' + id + '">Список каналов</a><div class="collapse" id="channels-' + id +
                                '"><p class="card card-body bg-dark small" style="max-height:250px;overflow-y:auto;">' +
                                xhr.response.channels.join('<br />') + '</p></div>'
                        }
                    } else {
                        st_el.innerHTML = 'offline'
                        st_el.classList.add('bg-danger')
                    }
                } else {
                    tr.querySelector('td.count').innerHTML = '-'
                    st_el.classList.add('bg-secondary')
                    st_el.innerHTML = 'unknown'
                }
            }
        }
        xhr.onerror = () => {
            console.log('[' + id + '] ERROR', xhr.response)
            st_el.classList.add('bg-danger')
            st_el.innerHTML = 'error'
            tr.querySelector('td.count').innerHTML = '-'
        }
        xhr.onabort = () => {
            console.log('[' + id + '] ABORTED', xhr.response)
            st_el.classList.add('bg-secondary')
            tr.querySelector('td.count').innerHTML = '-'
        }
        xhr.ontimeout = () => {
            console.log('[' + id + '] TIMEOUT', xhr.response)
            st_el.classList.add('bg-secondary')
            st_el.innerHTML = 'timeout'
            tr.querySelector('td.count').innerHTML = '-'
        }
        xhr.open('GET', '/?getinfo=' + id)
        xhr.send()
    })
</script>
</body>
</html>
