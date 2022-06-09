# Список самообновляемых плейлистов для IPTV

- [Список самообновляемых плейлистов для IPTV](#список-самообновляемых-плейлистов-для-iptv)
  - [Как использовать этот список?](#как-использовать-этот-список)
  - [Как добавить плейлист в этот список?](#как-добавить-плейлист-в-этот-список)
  - [API](#api)
  - [Формат `playlists.ini`](#формат-playlistsini)
  - [Дополнительные инструменты](#дополнительные-инструменты)
    - [tools/download-all.sh](#toolsdownload-allsh)
    - [tools/check-pls.sh](#toolscheck-plssh)
    - [tools/find-in-pls.sh](#toolsfind-in-plssh)
    - [tools/find-in-all.sh](#toolsfind-in-allsh)
    - [tools/make-pls.sh](#toolsmake-plssh)
  - [Как создать свой собственный плейлист](#как-создать-свой-собственный-плейлист)
  - [Лицензия](#лицензия)

---

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

где `ID` - один из идентификаторов, указанных в [`playlists.ini`](playlists.ini) в квадратных скобках.

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

где `ID` - один из идентификаторов, указанных в [`playlists.ini`](playlists.ini) в квадратных скобках.

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

### `tools/download-all.sh`

Скачивает все плейлисты из [`playlists.ini`](playlists.ini) в локальную директорию `./downloaded/`.

### `tools/check-pls.sh`

Проверяет каждый канал в плейлисте на доступность и выводит результат проверки.

Поддерживаются \*.m3u и \*.m3u8; как локальные файлы, так по прямым ссылкам.

Коды ошибок доступны [здесь](https://everything.curl.dev/usingcurl/returns).

Пример:

```
$ ./tools/check-pls.sh my.m3u8
Playlist: my.m3u8

Note 1: operation may take some time.
Note 2: press CTRL+C to skip current channel or CTRL+Z to kill process.
Note 3: results may be inaccurate, you should use proper IPTV software to re-check.
Note 4: error codes listed here - https://everything.curl.dev/usingcurl/returns
--------------------
[1] Канал Disney...
	- OK: "http://ott-cdn.ucom.am/s60/04.m3u8"
[2] Канал Disney...
	- ERROR 28 (-): "http://92.243.113.179:8080/Disney_Channel/index.m3u8?token=nts_tv"
[3] Disney канал ...
	- OK: "http://ott-cdn.ucom.am/s60/index.m3u8 "
[4] Канал Disney...
	- OK: "http://ott-cdn.ucom.am/s60/04.m3u8"
[5] Fox_Life_HD...
	- ERROR 6 (-): "http://live-ng-01.more.tv/hls/Fox_Life_HD/index_1.m3u8"
[6] FOX_HD...
	- ERROR 22 (404): "http://live-ng-01.more.tv/hls/FOX_HD/index_1.m3u8"
...
--------------------
Playlist: my.m3u8
Check stats
- Success:      995/999
- Failed:       4/999
```

### tools/find-in-pls.sh

Находит каналы по заданному регулярному выражению в указанном плейлисте.

Поддерживаются \*.m3u и \*.m3u8; как локальные файлы, так по прямым ссылкам.

Пример:

```
$ ./tools/find-in-pls.sh disney a.m3u8
--------------------
Playlist: a.m3u8
Channel to find: disney
--------------------

267 FOUND:	#EXTINF:-1 group-title="Disney" tvg-id="disney",Disney (1)
		http://zabava-htlive.cdn.ngenix.net/hls/CH_DISNEY/bw2000000/variant.m3u8?version=2

270 FOUND:	#EXTINF:-1 group-title="Disney" tvg-id="disney",Disney (2)
		https://okkotv-live.cdnvideo.ru/channel/Disney.m3u8
--------------------
Playlist: a.m3u8
Channel found: disney
Found: 2
```

### tools/find-in-all.sh

Находит каналы по заданному регулярному выражению в плейлистах, скачанных через `download-all.sh`.

Пример:

```
$ ./tools/find-in-all.sh (disney|СТС)
...
--------------------
Playlist: ./downloaded/kids.m3u.1
Channel to find: (disney|СТС)
--------------------

35 FOUND:	#EXTINF:-1 tvg-name="СТС Kids HD" group-title="Детские", СТС Kids HD
		https://okkotv-live.cdnvideo.ru/channel/CTC_Kids_HD.m3u8

59 FOUND:	#EXTINF:-1 tvg-name="Disney канал" group-title="Детские", Disney канал
		http://zabava-htlive.cdn.ngenix.net/hls/CH_DISNEY/bw2000000/variant.m3u8?version=2

83 FOUND:	#EXTINF:-1 tvg-name="Канал Disney (okko tv)" group-title="Детские", Канал Disney (okko tv)
		https://okkotv-live.cdnvideo.ru/channel/Disney.m3u8
--------------------
Playlist: ./downloaded/kids.m3u.1
Channel found: (disney|СТС)
Found: 3
--------------------
Playlist: ./downloaded/kz-all.m3u
Channel to find: (disney|СТС)
--------------------
Nothing found
...
```

### tools/make-pls.sh

Находит каналы по заданному регулярному выражению в плейлистах, скачанных через `download-all.sh`.

Отличается от `find-in-all.sh` тем, что тот выводит результат в человекочитаемом формате, а этот -- в готовом m3u формате для сохранения в файл.

Пример:

```
./tools/make-pls.sh "(fox|disney)"
#EXTM3U
# Autogenerated at 09.06.2022
# https://github.com/anthonyaxenov/iptv

#EXTINF:-1,Канал Disney
http://ott-cdn.ucom.am/s60/04.m3u8

#EXTINF:-1,Канал Disney
http://92.243.113.179:8080/Disney_Channel/index.m3u8?token=nts_tv

#EXTINF:-1 ,Fox HD
http://live02-cdn.tv.ti.ru:80/dtv/id376_NBN_SG--Fox_HD/04/plst.m3u8
...
```

## Как создать свой собственный плейлист

1. Скачать все плейлисты, указанные в [`playlists.ini`](playlists.ini):
   ```
   $ ./tools/download-all.sh
   ```
2. Вытащить из них нужные каналы и сохранить в отдельный файл:
   ```
   $ ./tools/make-pls.sh "(fox|disney)" > my.m3u8
   ```
   Так в плейлисте `./my.m3u8` окажутся все каналы из скачанных плейлистов, в названиях которых встрелись `fox` или `disney`.
3. Проверить доступность каналов в полученном плейлисте:
   ```
   $ ./tools/check-pls.sh my.m3u8
   ```
   > Результат `ОК` не значит, что канал действительно работает и отдаёт видео/аудио потоки.  
   > Результат `ERROR` с любыми кодами ошибок значит, что канал гарантированно не работает.
4. Вручную: удалить нерабочие, мусорные и продублировавшиеся (по ссылкам) каналы.
5. Вручную: добавить плейлист в IPTV-плеер и перепроверить результат.

## Лицензия

[The MIT License](LICENSE)
