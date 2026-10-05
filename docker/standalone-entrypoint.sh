#!/bin/bash
# All-in-one entrypoint: MariaDB + Apache/PHP in a single container.
set -e

DATADIR=/var/lib/mysql
DB_NAME="${DB_NAME:-bookmark-db}"
DB_USER="${DB_USER:-bookmark}"
DB_PASSWORD="${DB_PASSWORD:-bookpass}"

mkdir -p /run/mysqld
chown -R mysql:mysql /run/mysqld "$DATADIR"

if [ ! -d "$DATADIR/mysql" ]; then
    echo "[standalone] Initializing MariaDB data directory..."
    mariadb-install-db --user=mysql --datadir="$DATADIR" --skip-test-db > /dev/null
fi

echo "[standalone] Starting MariaDB..."
mariadbd --user=mysql --datadir="$DATADIR" --skip-networking=0 --bind-address=127.0.0.1 &
MARIADB_PID=$!

for i in $(seq 1 60); do
    if mariadb-admin ping --silent 2>/dev/null; then
        break
    fi
    if ! kill -0 "$MARIADB_PID" 2>/dev/null; then
        echo "[standalone] MariaDB died during startup" >&2
        exit 1
    fi
    sleep 1
done

echo "[standalone] Ensuring database, user and schema..."
mariadb <<SQL
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
CREATE USER IF NOT EXISTS '$DB_USER'@'127.0.0.1' IDENTIFIED BY '$DB_PASSWORD';
ALTER USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
ALTER USER '$DB_USER'@'127.0.0.1' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL

if ! mariadb "$DB_NAME" -e "SELECT 1 FROM \`global\` LIMIT 1;" >/dev/null 2>&1; then
    echo "[standalone] Importing initial schema..."
    mariadb "$DB_NAME" < /docker-init/myDb.sql
else
    # idempotent migrations for existing data volumes
    mariadb "$DB_NAME" <<'MIGRATE'
ALTER TABLE `groups` ADD COLUMN IF NOT EXISTS `variable` varchar(255) NOT NULL DEFAULT '';
UPDATE `global` SET `value` = '1.3.0' WHERE `key` = 'version' AND `value` LIKE '1.2%';
MIGRATE
fi

shutdown_all() {
    echo "[standalone] Shutting down..."
    apache2ctl -k graceful-stop 2>/dev/null || true
    mariadb-admin shutdown 2>/dev/null || true
    wait
    exit 0
}
trap shutdown_all TERM INT

echo "[standalone] Starting Apache..."
apache2-foreground &
APACHE_PID=$!

# exit if either service dies; otherwise wait for signals
wait -n "$MARIADB_PID" "$APACHE_PID"
exit $?
