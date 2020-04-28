<?php

if ($_GET['dbg']) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

/**
 * Возвращает количество каналов в плейлисте
 * 
 * @param string $pls_url URL плейлиста
 * @return int
 */
function getChannelCount($pls_url) {
    $content = file_get_contents($pls_url);
    $matches = [];
    preg_match_all('[EXTINF]', $content, $matches);
    return count($matches[0]);
}

// Шаблон короткой ссылки на плейлист
// $my_url = $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'].'?s=';
$my_url = $_SERVER['SERVER_NAME'].'/'.basename(__DIR__).'?s=';

// Чтение списка плейлистов из ini-файла
$data = parse_ini_file('playlists.ini', true);

if (empty($_GET['s'])) { ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>IPTV Playlists</title>
    </head>
    <body>
        <h1>Список самообновляемых плейлистов для IPTV</h1>
        <p>
            Дата обновления списка: <strong>
                <?=date('d-m-Y h:i:s', filemtime('playlists.ini'))?>
                <br>
                <a href="https://github.com/anthonyaxenov/iptv">github.com/anthonyaxenov/iptv</a>
            </strong>
        <p>
            Поддержкой этих плейлистов занимаются сервисы и ресурсы, указанные как источник (если таковые имеются).<br>
            Эти плейлисты собраны здесь вручную и бесплатны.
        </p>
        <p>Чтобы подключить плейлист, нужно в настройках IPTV-плеера добавить ссылку в следующем формате:</p>
        <pre><?=$my_url?><strong>ID</strong></pre>
        <p>где <strong>ID</strong> - один из указанных ниже идентификаторов.</p>
        <table width="100%" border="1" cellpadding="1">
            <thead>
                <tr>
                    <td style="text-align: center">ID</td>
                    <td>Название, источник</td>
                    <td style="text-align: center">Каналов</td>
                    <td>Плейлист</td>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $id => $element) { 
                    if (empty($element['pls'])) {
                        continue;
                    } ?>
                    <tr>
                        <td style="text-align: center">
                            <strong><?=$id?></strong>
                        </td>
                        <td>
                            <strong>
                                <?php if (empty($element['src'])) { ?>
                                    <?=$element['name']?>
                                <?php } else { ?>
                                    <a href="<?=$element['src']?>" target="_blank" rel="noopener nofollow"><?=$element['name']?></a>
                                <?php } ?>
                            </strong>
                        </td>
                        <td style="text-align: center"><?=getChannelCount($element['pls'])?></td>
                        <td onclick="prompt('Скопируйте адрес плейлиста', '<?=$my_url?><?=$id?>')"
                            title="Нажмите, чтобы скопировать адрес"
                            style="cursor:pointer">
                            <strong>
                                <pre><?=$my_url?><?=$id?></pre>
                            </strong>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <!-- <h2>Как этим пользоваться?</h2> -->
    </body>
    </html>
<?php } else {
    header('Location: '.$data[$_GET['s']]['pls']);
}
