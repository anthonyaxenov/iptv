<?php
/**
 * Anthony Axenov (c) 2022
 * The MIT License:
 * https://github.com/anthonyaxenov/iptv/blob/master/LICENSE
 */

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

function response(array $content): void
{
    header("Content-Type: application/json; charset=utf-8");
    die(json_encode($content));
}

$updated_at = date('d.m.Y h:i', filemtime('playlists.ini'));
$my_url = $_SERVER['SERVER_NAME'] . '?';
$ini = parse_ini_file('playlists.ini', true);

// get playlist info (ajax)
if (!empty($_GET['getinfo'])) {
    $pls = $ini[$_GET['getinfo']];
    if (!empty($pls['redirect'])) {
        $pls = $ini[$pls['redirect']];
    }
    unset($ini);
    if (empty($pls)) { // no playlist in ini
        response([
            'is_online' => false,
            'count' => 0,
            'channels' => [],
        ]);
    }

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $pls['pls']);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 5);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    $response = curl_exec($curl);
    $code = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    curl_close($curl);
    unset($curl);
    if ($response === false) { // timed out
        response([
            'is_online' => false,
            'count' => '-',
            'channels' => [],
        ]);
    }

    $matches = [];
    preg_match_all("/^#EXTINF:-?\d.*,\s*(.*)/m", $response, $matches);
    $channels = array_map('trim', $matches[1]);
    unset($response, $matches);
    response([
        'is_online' => $is_online = $code < 400,
        'count' => $is_online ? count($channels) : '-',
        'channels' => $channels,
    ]);
}

// redirect to playlist
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
    <style>.cursor-pointer {cursor: pointer} </style>
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
                                  class="font-monospace cursor-pointer">
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
                <p>
                    На этой странице собраны ссылки на IPTV-плейлисты, которые находятся в открытом доступе.
                    Они отбираются мной вручную и проверяются здесь автоматически.
                </p>
                <p>
                    Ресурс <?=$_SERVER['SERVER_NAME']?> не занимается трансляцией видео- и аудиопотоков,
                    администрированием конечных плейлистов и программ телепередач или хранением всего указанного.
                    Подобными вопросами занимаются администраторы ресурсов, указанные как источник, и те, с чьих ресурсов
                    ведётся трансляция.
                </p>
                <p class="mb-5">
                    Ресурс <?=$_SERVER['SERVER_NAME']?> предоставляет только информацию об активности плейлистов, найденных
                    в открытом доступе, и короткие ссылки на них для удобства ввода с пульта на телевизоре.
                    Вопросы работоспособности плейлистов и каналов адресуйте тем, кто несёт за них ответственность.
                </p>

                <h2>Что означают статусы плейлистов?</h2>
                <ul class="mb-5">
                    <li>
                        <span class="badge small bg-warning text-dark">?</span> Загрузка данных, нужно немного подождать.
                    </li>
                    <li>
                        <span class="badge small text-dark bg-success">online</span> Плейлист, возможно, активен.
                    </li>
                    <li>
                        <span class="badge small text-dark bg-secondary">unknown</span> Состояние неизвестно.
                        Возможно, плейлист активен, но корректно его проверить не удалось.
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
                    Я не гарантирую корректность и актуальность информации, которую ты увидишь здесь.
                    Хотя я и стараюсь улучшать качество проверок, но всё же рекомендую проверять желаемые
                    плейлисты самостоятельно вручную, ибо нет никаких гарантий:
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

                <h2>Эти плейлисты и каналы в них -- бесплатны?</h2>
                <p class="mb-5">Возможно. По крайней мере, так утверждают источники.</p>

                <h2>Как подключить плейлист?</h2>
                <p class="mb-5">
                    <a href="https://www.google.com/search?q=%D0%BA%D0%B0%D0%BA%20%D0%BF%D0%BE%D0%B4%D0%BA%D0%BB%D1%8E%D1%87%D0%B8%D1%82%D1%8C%20iptv%20%D0%BF%D0%BB%D0%B5%D0%B9%D0%BB%D0%B8%D1%81%D1%82%20%D0%BF%D0%BE%20%D1%81%D1%81%D1%8B%D0%BB%D0%BA%D0%B5">
                        Добавь в свой IPTV-плеер</a> ссылку из последней колонки.
                </p>

                <h2>Какова гарантия, что я добавлю себе плейлист отсюда и он работать хоть сколько-нибудь долго?</h2>
                <p class="mb-5">
                    Никакова.
                    Мёртвые плейлисты я периодически вычищаю, реже -- добавляю новые.
                    ID плейлистов могут меняться, поэтому вполне может произойти внезапная подмена одного другим, однако намеренно я так не делаю.
                    Если один плейлист переезжает на новый адрес, то я ставлю временное перенаправление со старого ID на новый.
                    Плюс читай выше про доверие результатам проверки (проблема может быть не на этой стороне).
                </p>

                <h2>Где взять программу передач (EPG)?</h2>
                <ul class="mb-5">
                    <li><b>https://iptvx.one/viewtopic.php?f=12&t=4</b></li>
                    <li>https://iptvmaster.ru/epg-for-iptv</li>
                    <li>https://google.com</li>
                </ul>

                <h2>Как часто обновляется этот список?</h2>
                <p>
                    Время от времени.
                    Иногда я захожу сюда и проверяю всё ли на месте, иногда занимаюсь какими-то доработками.
                </p>
                <p class="mb-5">
                    Если есть кандидаты на добавление, то читай ниже.
                </p>

                <h2>Как часто обновляются сами плейлисты (каналы)?</h2>
                <p class="mb-5">
                    Зависит от источника. Я этим не занимаюсь.
                </p>

                <h2>Как пополнить этот список?</h2>
                <p class="mb-5">
                    Сделать pull-request в <a href="https://github.com/anthonyaxenov/iptv">репозиторий</a>.
                    Я проверю плейлист и добавлю его в общий список, если всё ок.
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
                            tr.querySelector('td.info').innerHTML += '<a class="small cursor-pointer" ' +
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
