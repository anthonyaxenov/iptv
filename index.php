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
    preg_match_all("/^#EXTINF:-?[\d](?:(\s?url-tvg=\".*\")?(\stvg-logo=\".*\")?(\stvg-name=\".*\")?(\stvg-id=\".*\")?(\sgroup-title=\".*\")?)\s?,\s?(.*)/m", $content, $matches);
    unset($content);
    $channels = $matches[5];
    unset($matches);
    $is_online = is_array($headers) && !empty($headers) && strpos($headers[0], ' 200') !== false;
    unset($headers);
    array_walk($channels, function (&$str) { $str = trim($str); });
    header("Content-Type: text/plain; charset=utf-8");
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
    <header class="pb-3 mb-3">
        <a href="/" class="text-light text-decoration-none">
            <h1>Самообновляемые плейлисты IPTV</h1>
        </a>
        <p class="small text-muted">
            <a href="https://github.com/anthonyaxenov/iptv">GitHub</a> | <a href="https://axenov.dev">axenov.dev</a><br/>
            Обновлено:&nbsp;<?=$updated_at?>МСК<br/>
            Плейлистов в списке:&nbsp;<strong><?=count($ini)?></strong>
        </p>
    </header>

    <main>
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#list" type="button"
                        role="tab" aria-controls="list" aria-selected="true">Список</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="faq-tab" data-bs-toggle="tab" data-bs-target="#faq" type="button"
                        role="tab" aria-controls="faq" aria-selected="false">FAQ</button>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active pt-5" id="list" role="tabpanel" aria-labelledby="list-tab">
                <table class="table table-dark table-hover small">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Информация о плейлисте</th>
                        <th>Каналов</th>
                        <th title="Нажми на ссылку, чтобы скопировать её в буфер обмена">Ссылка</th>
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
                            <span onclick="prompt('Скопируй адрес плейлиста', '<?=$my_url?><?=$id?>')"
                                  data-bs-toggle="tooltip"
                                  data-bs-placement="top"
                                  title="Нажми на ссылку, чтобы скопировать её в буфер обмена"
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
            <div class="tab-pane fade p-3 pt-5" id="faq" role="tabpanel" aria-labelledby="profile-tab">
                <h2>Что здесь происходит?</h2>
                <p class="mb-5">
                    На этой странице собраны ссылки на IPTV-плейлисты, которые находятся в открытом доступе.
                    Они отбираются мной вручную и проверяются здесь автоматически.
                    Поддержкой этих плейлистов занимаются администраторы ресурсов, указанные как источник.
                    Вопросы работоспособности плейлистов адресуйте тем, кто несёт за них ответственность.
                </p>

                <h2>Эти плейлисты бесплатны?</h2>
                <p class="mb-5">Да, но в любой момент могут перестать таковыми быть.</p>

                <h2>Как подключить плейлист?</h2>
                <p class="mb-5">
                    <a href="https://www.google.com/search?q=%D0%BA%D0%B0%D0%BA%20%D0%BF%D0%BE%D0%B4%D0%BA%D0%BB%D1%8E%D1%87%D0%B8%D1%82%D1%8C%20iptv%20%D0%BF%D0%BB%D0%B5%D0%B9%D0%BB%D0%B8%D1%81%D1%82%20%D0%BF%D0%BE%20%D1%81%D1%81%D1%8B%D0%BB%D0%BA%D0%B5">
                        Добавить в твой IPTV-плеер</a> ссылку из последней колонки.
                </p>

                <h2>Что означают статусы плейлистов?</h2>
                <ul class="mb-5">
                    <li>
                        <span class="badge small bg-warning text-dark">?</span> Загрузка данных.
                    </li>
                    <li>
                        <span class="badge small text-dark bg-success">online</span> Плейлист активен. Фактически
                        означает, что удалённый файл успешно скачивается.
                    </li>
                    <li>
                        <span class="badge small text-dark bg-secondary">unknown</span> Состояние неизвестно. Скорее всего, плейлист активен, но получить данные о нём не удалось.
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
                </ul>

                <h2>Почему нельзя доверять результатам проверки?</h2>
                <p>
                    Я не гарантирую корректность информации, которую ты увидишь здесь.
                    Рекомендую проверять желаемые плейлисты вручную, ибо нет никаких гарантий:
                </p>
                <ul class="mb-5">
                    <li>
                        что это вообще плейлисты, а не чьи-то архивы с мокрыми кисками;
                    </li>
                    <li>
                        что плейлисты по разным ссылкам не дублируют друг друга и отличаются каналами хотя бы на четверть;
                    </li>
                    <li>
                        что плейлист работоспособен (каналы работают, корректно названы, имеют аудио, etc.);
                    </li>
                    <li>
                        что подгрузится корректное количество каналов и их список (хотя на это я ещё могу влиять и
                        стараюсь как-то улучшить).
                    </li>
                </ul>

                <h2>Как пополнить этот список?</h2>
                <p class="mb-5">
                    Сделать pull-request в <a href="https://github.com/anthonyaxenov/iptv">репозиторий</a>.
                </p>
            </div>
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
