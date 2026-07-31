# Bookmark-Page

All-in-one Docker image (Apache/PHP 8.4 + MariaDB in a single container), built automatically
for linux/amd64 + linux/arm64 and published at https://hub.docker.com/r/gemini2350/bookmark-page

![Bookmark-Page](docs/screenshot-main.png)

## Run it

```
docker run -d --name bookmark-page --restart unless-stopped -p 8080:80 -v bookmark-db:/var/lib/mysql gemini2350/bookmark-page:latest
```

then goto localhost:8080. enjoy...

The database is initialized automatically on first start and persisted in the
`bookmark-db` volume, so your bookmarks survive container restarts and image updates.
`--restart unless-stopped` brings the container back up after crashes and host reboots,
unless you stopped it yourself.

Update to a new version:

```
docker pull gemini2350/bookmark-page:latest && docker rm -f bookmark-page && docker run -d --name bookmark-page --restart unless-stopped -p 8080:80 -v bookmark-db:/var/lib/mysql gemini2350/bookmark-page:latest
```

Alternatively clone the Repo and use `docker compose up -d`.

## Features of this fork

- **Single container**: no separate MySQL container needed anymore
- **Updated stack**: `php:8.4-apache` base image with MariaDB
- **Import / Export**: new tab in the Global-Configuration modal
  - Export all Groups and Bookmarks (incl. User-Columns) as a JSON file
  - Import a previously exported JSON file: existing Groups are reused, Bookmarks whose
    Link already exists are skipped
  - Danger Zone: delete all Bookmarks (optionally incl. all Groups) at once
- **Configurable DB credentials** via environment variables:
  `DB_USER`, `DB_PASSWORD`, `DB_NAME` (sensible defaults built in)

![Import / Export](docs/screenshot-import-export.png)

## Development

build locally with `docker build -t bookmark-page .` and run it the same way as above.

## License

 Copyright 2020 Adrian Hilber
 Licensed under MIT (https://github.com/LeeO86/Bookmark-Page/blob/master/LICENSE)
