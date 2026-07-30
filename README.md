# Bookmark-Page

clone the Repo and just run `docker compose up -d` and goto localhost:8080. enjoy...

This fork is updated to current dependencies (PHP 8.4 Apache image, MySQL 8.4) and adds an
Import / Export feature for all Bookmarks.

## Features of this fork

- **Updated stack**: `php:8.4-apache` and `mysql:8.4` (LTS), modernized `docker-compose.yml`
  (healthcheck on the DB, no legacy `links:`/`version:`)
- **Import / Export**: new tab in the Global-Configuration modal
  - Export all Groups and Bookmarks (incl. User-Columns) as a JSON file
  - Import a previously exported JSON file: existing Groups are reused, Bookmarks whose
    Link already exists are skipped
  - Danger Zone: delete all Bookmarks (optionally incl. all Groups) at once
- **Configurable DB connection** via environment variables:
  `DB_HOST`, `DB_USER`, `DB_PASSWORD`, `DB_NAME` (defaults match the compose file)

## Development

for Development you can use `docker compose -f docker-compose.dev.yml up -d` and the files
from the www Directorie will be synced to the Container

## License

 Copyright 2020 Adrian Hilber
 Licensed under MIT (https://github.com/LeeO86/Bookmark-Page/blob/master/LICENSE)
