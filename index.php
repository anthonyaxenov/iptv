<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
$my_url = $_SERVER['SERVER_NAME'].'/iptv?s=';

// Чтение списка плейлистов из ini-файла
$data = parse_ini_file('playlists.ini', true);

if (!empty($_GET['s'])) {
    if (!empty($data[$_GET['s']]['redirect'])) {
        header('Location: '.$data[$data[$_GET['s']]['redirect']]['pls']);
    } elseif (!empty($data[$_GET['s']]['pls'])) {
        header('Location: '.$data[$_GET['s']]['pls']);
    } 
} else {
?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>IPTV Playlists</title>
        <style>
            .myurl {
                font-weight: bold;
                line-height: 2em;
                display: block;
            }
            .center {
                text-align: center;
            }
            .pointer {
                cursor:pointer;
            }
        </style>
    </head>
    <body>
        <h1>Список самообновляемых плейлистов для IPTV</h1>
        <p>
            Дата обновления списка: <strong>
                <?=date('d-m-Y h:i:s', filemtime('playlists.ini'))?>&nbsp;МСК<br>
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
                    <td class="center">ID</td>
                    <td>Информация о плейлисте</td>
                    <td class="center">Каналов</td>
                    <td title="Нажмите на ссылку, чтобы скопировать адрес">Ссылка на плейлист</td>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $id => $element) { 
                    if (empty($element['pls'])) {
                        continue;
                    } ?>
                    <tr>
                        <td class="center">
                            <strong><?=$id?></strong>
                        </td>
                        <td>
                            <strong><?=$element['name'] ?: "Плейлист #".$id?></strong>
                            <?php if (!empty($element['src'])) { ?>
                                <br><a href="<?=$element['src']?>" target="_blank" rel="noopener nofollow">Источник</a>        
                            <?php } ?>
                            <?php if (!empty($element['desc'])) { ?>
                                <br><?=$element['desc']?>        
                            <?php } ?>                            
                        </td>
                        <td class="center"><?=getChannelCount($element['pls'])?></td>
                        <td width="250">
                            <span onclick="prompt('Скопируйте адрес плейлиста', '<?=$my_url?><?=$id?>')"
                                  title="Нажмите, чтобы скопировать адрес"
                                  class="pointer myurl">
                                <?=$my_url?><?=$id?>
                            </span>
                            <span>
                                Прямая ссылка: <a href="<?=$element['pls']?>">M3U</a>
                            </span>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </body>
    </html>
<?php 
}
