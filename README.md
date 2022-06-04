# Список самообновляемых плейлистов для IPTV

> **[Перейти на актуальную сраницу](https://iptv.axenov.dev/)**

Здесь собраны ссылки на IPTV-плейлисты, которые находятся в открытом доступе.

Они бесплатны для использования. Список проверяется и обновляется мной вручную.

Поддержкой этих плейлистов занимаются сервисы и ресурсы, указанные как источник.

Вопросы работоспособности плейлистов адресуйте тем, кто несёт за них ответственность.

## Как использовать этот список?

Чтобы подключить плейлист, нужно в настройках IPTV-плеера указать ссылку в следующем формате:

```
iptv.axenov.dev?ID
```

где `ID` - один из идентификаторов, указанных в `playlists.ini` в квадратных скобках.

## Как добавить плейлист в этот список?

1) Склонировать себе репозиторий, создать ветку
2) Внести изменения в файл [`playlists.ini`](playlists.ini) как описано ниже
3) Сделать коммит, отправить изменения в свой репозиторий и создать merge-request

Либо провернуть всё то же самое через браузер.

## API

Можно получать состояние плейлистов из этого сборника при помощи метода:

```
GET https://iptv.axenov.dev/?getinfo=<ID>
```

где `ID` - один из идентификаторов, указанных в `playlists.ini` в квадратных скобках.

Ответом может быть JSON следующего содержания:

```json
{
    "is_online": true,
    "count": 123,
    "channels": [ ... ]
}
```

где:
* `is_online` - `bool`, доступность плейлиста
* `count` - `uint|char[1]`, количество каналов >=0 либо `'-'` при `is_online === false`
* `channels` - `string[]`, массив строк с названиями каналов, может быть пустым.

Также ответ может быть пустым (вообще пустым, даже не `null`).
Такое я встречал с одним конкретном плейлисте с поехавшей кодировкой.
Лень разбираться, пофиг.

## Формат `playlists.ini`

```ini
; В квадратных скобках - ID плейлиста в рамках этого
; конфига (обязателен). Для удобства ввода с пульта, 
; для ID рекомендуется число или короткая строка без
; пробелов и др. спецсимволов.
[p1]
; Название плейлиста (необязательно)
name='webarmen.com 18+'
; Краткое описание из источника или от себя (необязательно)
desc=''
; Прямая ссылка на m3u/m3u8 плейлист (обязательно)
pls='https://webarmen.com/my/iptv/auto.xxx.m3u'
; Ссылка на источник, откуда взят плейлист (необязательно)
src='https://webarmen.com/my/iptv/xxx.php'

[p2]
; ID другого плейлиста в этом списке, на который 
; произойдёт редирект (нужно для мягкой смены ID).
; Необязателен, но если указан, то приоритетнее, чем pls.
redirect=p1
```

## Дополнительные инструменты

### tools/download-all.sh

Скачивает все плейлисты из `playlists.ini` в локальную директорию `./flies/...`.

### tools/check-pls.sh

Проверяет каждый канал в плейлисте и выводит результат проверки.

Поддерживаются *.m3u и *.m3u8, как локальные файлы, так по прямым ссылкам.

Коды ошибок доступны [здесь](https://everything.curl.dev/usingcurl/returns).

Пример:

```
$ ./tools/check-pls.sh https://smarttvapp.ru/app/iptvfull.m3u                                                                                                  TSTP ✘  4s  ≡  16:47:00 
Playlist: https://smarttvapp.ru/app/iptvfull.m3u
Saved in /tmp/iptvfull.m3u

Note 1: operation may take some time.
Note 2: press CTRL+C to skip current channel or CTRL+Z to kill process.
Note 3: results may be inaccurate, you should use proper IPTV software to re-check.
Note 4: error codes listed here - https://everything.curl.dev/usingcurl/returns
--------------------
[1] - 1.06.2022 - smarttvapp.ru -...
        - OK: "https://smarttvapp.ru/wp-content/uploads/2017/02/smartTVradar_logo_405x127kkk12.png"
[2] Первый канал Евразия...
        - OK: "http://stream.euroasia.lfstrm.tv/perviy_evrasia/1/index.m3u8"
[3] Первый канал. Всемирная сеть...
        - OK: "https://sc.id-tv.kz:443/1KanalVsemSet_36_37.m3u8"
[4] Россия К +2...
        - OK: "https://sc.id-tv.kz:443/RossiyaK_34_35.m3u8"
[5] НТВ Мир...
        - OK: "http://92.46.127.146:8080/ntv-L3-TRANS/index.m3u8"
[6] НТВ Мир...
        - ERROR (28): "https://sc.id-tv.kz:443/NTV_34_35.m3u8"
...

--------------------
Playlist: https://smarttvapp.ru/app/iptvfull.m3u
Check stats
- Success:      995/999
- Failed:       4/999
```
