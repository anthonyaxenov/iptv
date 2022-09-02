# Автообновляемые IPTV-плейлисты

> **Web-версия**: [https://iptv.axenov.dev/](https://iptv.axenov.dev/)  
> **FAQ**: [https://iptv.axenov.dev/faq](https://iptv.axenov.dev/faq)  
> **Зеркало репозитория**: https://git.axenov.dev/anthony/iptv

Проект, содержащий в себе инструменты для работы с IPTV-плейлистами:

* список автообновляемых плейлистов, которые найдены в открытых источниках;
* скрипты для поиска каналов в этом списке, создания своего плейлиста;
* веб-сервис, предоставляющий короткие ссылки на эти плейлисты и отображающий список каналов.

Плейлисты подбираются преимущественно для РФ и любых стран бывшего СНГ, но этими странами список не ограничивается.

Поддержкой этих плейлистов занимаются сервисы и ресурсы, указанные как источник.
Вопросы работоспособности плейлистов адресуйте тем, кто несёт за них ответственность.

Они бесплатны для использования.
Список проверяется и обновляется мной вручную.
Гарантию работоспособности никто не даёт.

## English description

> **Mirrored repo**: https://git.axenov.dev/anthony/iptv

This repo contains IPTV-playlists free to use with your media-player.

Most of them are in russian or CIS languages but you can find something interesting here for yourself.

Also there are some handy tools to make your own playlist or find channels you want in playlists listed here.

You can use this repo according to [LICENSE](LICENSE) conditions.

I'm too lazy to translate and support the whole project in ru and en, sorry, guys.

---

## Документация

- [Как использовать этот список?](#howtouse)
- [Формат playlists.ini](#iniformat)
- [API](#api)
- [Развёртывание проекта](#deploy)
- [Дополнительные инструменты](#tools)
- [Как создать свой собственный плейлист](#howtomake)
- [Использованный стек](#stack)
- [Лицензия](#license)

---

<a id="howtouse"></a>

## Как использовать этот список?

Чтобы подключить плейлист, нужно в настройках медиаплеера указать ссылку в следующем формате:

```
iptv.axenov.dev/<ID>
iptv.axenov.dev?<ID>  (устаревший формат)
iptv.axenov.dev/?<ID> (устаревший формат)
```

где `<ID>` - один из идентификаторов, указанных в [`playlists.ini`](playlists.ini) в квадратных скобках.

Либо провернуть всё то же самое через браузер.

<a id="iniformat"></a>

## Формат `playlists.ini`

```ini
# ID плейлиста в рамках этого конфига (обязательно)
[1]

# Название плейлиста (необязательно)
name = 'Рабочий и актуальный IPTV плейлист M3U'

# Краткое описание из источника или от себя (необязательно)
desc = 'В этом IPTV плейлисте вы найдете очень много каналов в HD качестве'

# Прямая ссылка на m3u/m3u8 плейлист (обязательно)
pls = 'https://example.com/pls.m3u'

# Ссылка на источник, откуда взят плейлист (необязательно)
src = 'https://example.com/super-duper-playlist'

[2]

# ID другого плейлиста в этом списке, на который
# произойдёт редирект. Нужен для мягкой смены ID.
redirect = 1
```

В описании любого плейлиста обязательны:
* ID в квадратных скобках  
  > Для удобства ввода с пульта, рекомендуется задавать числом или короткой строкой без пробелов и др. спецсимволов.
* параметр `pls` или `redirect`
  > Если указаны оба, то `redirect` приоритетен.

Плейлистов с редиректами может быть сколько угодно, но они не должны быть цикличными.

<a id="api"></a>

## API

Можно получать состояние плейлистов из этого сборника при помощи метода:

```
GET https://iptv.axenov.dev/<ID>/json
```

где `ID` -- один из идентификаторов, указанных в [`playlists.ini`](playlists.ini) в квадратных скобках.

В случае успеха вернётся JSON следующего содержания:

```json
{
    "id": "p1",
    "url": "localhost:8080/p1",
    "name": "Каналы в SD и HD качестве (smarttvnews.ru)",
    "desc": "Рабочий и актуальный IPTV плейлист M3U — на июнь 2022 года",
    "pls": "https://smarttvnews.ru/apps/iptvchannels.m3u",
    "src": "https://smarttvnews.ru/rabochiy-i-aktualnyiy-iptv-pleylist-m3u-kanalyi-v-sd-i-hd-kachestve/",
    "status": "online",
    "encoding": {
        "name": "UTF-8",
        "alert": false
    },
    "channels": [
        "Channel1",
        "Channel2",
        "ChannelX"
    ],
    "count": 3
}
```

где:

* `id` -- идентификатор плейлиста
* `name` -- название плейлиста
* `url` -- короткая ссылка, которую можно использовать для добавления плейлиста в плеер
* `desc` -- краткое описание
* `pls` -- прямая ссылка на m3u/m3u8 плейлист
* `src` -- ссылка на источник, откуда взят плейлист
* `status` -- статус плейлиста (`"online"|"timeout"|"offline"|"error"`)
* `encoding` -- данные о кодировке файла плейлиста
    * `name` -- название кодировки (`"UTF-8"|"Windows-1251"`)
    * `alert` -- признак отличия кодировки от `UTF-8`, названия каналов сконвертированы в `UTF-8`, могут быть ошибки
    в отображении
* `channels` -- массив названий каналов
* `count` -- количество каналов >= 0

> Название кодировки `encoding.name` может определяться неточно!

В случае ошибки вернётся JSON в следующем формате:

```json
{
    "id": "p1",
    "url": "localhost:8080/p1",
    "name": "Каналы в SD и HD качестве (smarttvnews.ru)",
    "desc": "Рабочий и актуальный IPTV плейлист M3U — на июнь 2022 года",
    "pls": "https://smarttvnews.ru/apps/iptvchannels.m3u",
    "src": "https://smarttvnews.ru/rabochiy-i-aktualnyiy-iptv-pleylist-m3u-kanalyi-v-sd-i-hd-kachestve/",
    "status": "offline",
    "error": {
        "code": 22,
        "message": "The requested URL returned error: 404 Not Found"
    }
}
```

где:

* `id` -- идентификатор плейлиста
* `name` -- название плейлиста
* `url` -- короткая ссылка, которую можно использовать для добавления плейлиста в плеер
* `desc` -- краткое описание
* `pls` -- прямая ссылка на m3u/m3u8 плейлист
* `src` -- ссылка на источник, откуда взят плейлист
* `status` -- статус плейлиста (`"online"|"timeout"|"offline"|"error"`)
* `error` -- данные об ошибке при проверке плейлиста
    * `code` -- [код ошибки curl](https://curl.se/libcurl/c/libcurl-errors.html)
    * `message` -- текст ошибки curl

<a id="deploy"></a>

## Развёртывание проекта

### Aвтоматически

Выполнить `./iptv init`

### Вручную

1. Выполнить `cp ./src/.env.example ./src/.env`, установить необходимые параметры в файле `./src/.env`
2. Выполнить `docker compose up -d --build` (или `./iptv up`)
3. Открыть `http://<APP_URL>:8080` в браузере (или `./iptv open`)

Если на сервере, на котором запускаются контейнеры, стоит apache2, то его можно использовать как реверс прокси:

Для этого следует:

```
$ sudo a2enmod proxy proxy_http proxy_balancer lbmethod_byrequests
$ sudo nano /etc/apache2/sites-available/iptv.conf
```
```apacheconf
# example.com заменить на свой адрес

<VirtualHost example.com:80>
    ServerName example.com
    # обязательно без SSL:
    ProxyPreserveHost On
    ProxyPass / http://localhost:8080/
    ProxyPassReverse / http://localhost:8080/
    # обязательно для SSL:
    # RewriteEngine on
    # RewriteCond %{SERVER_NAME} =example.com
    # RewriteRule ^ https://%{SERVER_NAME}%{REQUEST_URI} [END,NE,R=permanent]
</VirtualHost>

# обязательно для SSL:
#<IfModule mod_ssl.c>
#<VirtualHost example.com:443>
#    ServerName example.com
#    ProxyPreserveHost On
#    ProxyPass / http://localhost:8080/
#    ProxyPassReverse / http://localhost:8080/
#    сертификаты можно получить через certbot
#    SSLCertificateFile /etc/letsencrypt/live/example.com/fullchain.pem
#    SSLCertificateKeyFile /etc/letsencrypt/live/example.com/privkey.pem
#    Include /etc/letsencrypt/options-ssl-apache.conf
#</VirtualHost>
#</IfModule>

# Ctrl+O
# Enter
# Ctrl+X
```
```shell
$ sudo a2ensite iptv
$ # для подгрузки включенных модулей выполнить именно restart, а не reload
$ sudo systemctl restart apache2
```

<a id="tools"></a>

## Дополнительные инструменты

### Очистка кеша Twig

Если в файле `./src/.env` параметр `TWIG_CACHE=1`, то макеты страниц компилируются однажды и потом переиспользуются.
Изменённые макеты не будут перекомпилироваться пока не будет очищен кеш прежних.

Для этого следует выполнить:

```
./iptv composer clear-views
```

### Скачать все плейлисты

Команда: `./tools/download-all.sh`

Скачивает все плейлисты из [`playlists.ini`](playlists.ini) в локальную директорию `./downloaded/`.

### Проверить каналы плейлиста

Команда: `./tools/check-pls.sh`

Проверяет каждый канал в плейлисте на доступность и выводит результат проверки.

Поддерживаются `*.m3u` и `*.m3u8`; как локальные файлы, так по прямым ссылкам.

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

### Поиск каналов в одном плейлисте

Команда: `./tools/find-in-pls.sh`

Находит каналы по заданному регулярному выражению в одном указанном плейлисте.

Поддерживаются `*.m3u` и `*.m3u8`; как локальные файлы, так по прямым ссылкам.

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

### Поиск каналов во всех плейлистах

Команда: `./tools/find-in-all.sh`

Находит каналы по заданному регулярному выражению во всех плейлистах, скачанных через `download-all.sh`.

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

### Создать плейлист из нужных каналов

Команда: `./tools/make-pls.sh`

Находит каналы по заданному регулярному выражению во всех плейлистах, скачанных через `download-all.sh`.

Отличается от `find-in-all.sh` тем, что тот выводит результат в человекочитаемом формате, а этот -- в готовом m3u
формате для сохранения в файл.

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

<a id="howtomake"></a>

## Как создать свой собственный плейлист?

1. Скачать все плейлисты, указанные в [`playlists.ini`](playlists.ini):
   ```
   $ ./tools/download-all.sh
   ```
2. Вытащить из них нужные каналы и сохранить в отдельный файл:
   ```
   $ ./tools/make-pls.sh "(fox|disney)" > my.m3u8
   ```
   Так в плейлисте `./my.m3u8` окажутся все каналы из скачанных плейлистов, в названиях которых встретились `fox`
   или `disney`.
3. Проверить доступность каналов в полученном плейлисте:
   ```
   $ ./tools/check-pls.sh my.m3u8
   ```
   > Результат `ОК` не означает, что канал действительно работает и отдаёт видео/аудио потоки.  
   > Результат `ERROR` с любыми кодами ошибок гарантированно означает, что канал не работает.
4. Вручную: удалить нерабочие, мусорные и продублировавшиеся (по ссылкам) каналы.
5. Вручную: добавить плейлист в IPTV-плеер и перепроверить результат.

<a id="stack"></a>

## Использованный стек

* [docker compose](https://docs.docker.com/compose/)
* [php8.1-fpm](https://www.php.net/releases/8.1/en.php)
* [FlightPHP](https://flightphp.com/learn)
* [Bootstrap 5](https://getbootstrap.com/docs/5.0/getting-started/introduction/)
* [nginx](https://nginx.org/ru/)
* bash

<a id="license"></a>

## Лицензия

[The MIT License](LICENSE)
