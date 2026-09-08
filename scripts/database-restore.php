#!/usr/bin/env php
<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use Symfony\Component\Process\Process;

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

Dotenv::createImmutable($root)->safeLoad();

$options = getopt('', ['input:', 'confirm:']);
$input = $options['input'] ?? null;
$confirmation = $options['confirm'] ?? null;

if (!$input || !is_file($input)) {
    fwrite(STDERR, "Usage: php scripts/database-restore.php --input=/secure/path/backup --confirm=RESTORE\n");
    exit(1);
}
if ($confirmation !== 'RESTORE') {
    fwrite(STDERR, "Restore refused. Pass --confirm=RESTORE after verifying the target and backup.\n");
    exit(1);
}

$checksumFile = $input.'.sha256';
if (is_file($checksumFile)) {
    $expected = strtok(trim((string) file_get_contents($checksumFile)), " \t");
    $actual = hash_file('sha256', $input);
    if (!$expected || !hash_equals($expected, $actual)) {
        fwrite(STDERR, "Backup checksum verification failed.\n");
        exit(1);
    }
}

$connection = $_ENV['DB_CONNECTION'] ?? $_SERVER['DB_CONNECTION'] ?? 'sqlite';

if ($connection === 'sqlite') {
    $database = $_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? $root.'/database/database.sqlite';
    if ($database === ':memory:') {
        fwrite(STDERR, "Cannot restore into an in-memory SQLite database.\n");
        exit(1);
    }
    if (!str_starts_with($database, '/')) {
        $database = $root.'/'.ltrim($database, '/');
    }
    if (!is_dir(dirname($database)) && !mkdir(dirname($database), 0700, true) && !is_dir(dirname($database))) {
        fwrite(STDERR, "Unable to create SQLite database directory.\n");
        exit(1);
    }
    if (!copy($input, $database)) {
        fwrite(STDERR, "Unable to restore SQLite database.\n");
        exit(1);
    }
} elseif (in_array($connection, ['mysql', 'mariadb'], true)) {
    $database = $_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? '';
    $username = $_ENV['DB_USERNAME'] ?? $_SERVER['DB_USERNAME'] ?? '';
    $password = $_ENV['DB_PASSWORD'] ?? $_SERVER['DB_PASSWORD'] ?? '';
    $host = $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? '127.0.0.1';
    $port = $_ENV['DB_PORT'] ?? $_SERVER['DB_PORT'] ?? '3306';

    if ($database === '' || $username === '') {
        fwrite(STDERR, "DB_DATABASE and DB_USERNAME are required for MySQL/MariaDB restores.\n");
        exit(1);
    }

    $process = new Process([
        'mysql',
        '--host='.$host,
        '--port='.$port,
        '--user='.$username,
        $database,
    ], $root, ['MYSQL_PWD' => $password], file_get_contents($input));
    $process->setTimeout(3600);
    $process->run();

    if (!$process->isSuccessful()) {
        fwrite(STDERR, $process->getErrorOutput() ?: $process->getOutput());
        exit($process->getExitCode() ?: 1);
    }
} else {
    fwrite(STDERR, "Unsupported DB_CONNECTION '{$connection}'. Supported: sqlite, mysql, mariadb.\n");
    exit(1);
}

fwrite(STDOUT, "Database restore completed. Run migrations and the full regression suite before use.\n");
