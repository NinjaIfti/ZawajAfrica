#!/usr/bin/env php
<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use Symfony\Component\Process\Process;

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

Dotenv::createImmutable($root)->safeLoad();

$options = getopt('', ['output::']);
$connection = $_ENV['DB_CONNECTION'] ?? $_SERVER['DB_CONNECTION'] ?? 'sqlite';
$timestamp = gmdate('Ymd_His');
$outputDirectory = $options['output'] ?? $root.'/backups';

if (!is_dir($outputDirectory) && !mkdir($outputDirectory, 0700, true) && !is_dir($outputDirectory)) {
    fwrite(STDERR, "Unable to create backup directory: {$outputDirectory}\n");
    exit(1);
}

$outputDirectory = realpath($outputDirectory) ?: $outputDirectory;

if ($connection === 'sqlite') {
    $database = $_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? $root.'/database/database.sqlite';
    if ($database === ':memory:') {
        fwrite(STDERR, "An in-memory SQLite database cannot be backed up.\n");
        exit(1);
    }
    if (!str_starts_with($database, '/')) {
        $database = $root.'/'.ltrim($database, '/');
    }
    if (!is_file($database)) {
        fwrite(STDERR, "SQLite database not found: {$database}\n");
        exit(1);
    }

    $destination = $outputDirectory."/zawajafrica-sqlite-{$timestamp}.sqlite";
    if (!copy($database, $destination)) {
        fwrite(STDERR, "Unable to copy SQLite database.\n");
        exit(1);
    }
} elseif (in_array($connection, ['mysql', 'mariadb'], true)) {
    $database = $_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? '';
    $username = $_ENV['DB_USERNAME'] ?? $_SERVER['DB_USERNAME'] ?? '';
    $password = $_ENV['DB_PASSWORD'] ?? $_SERVER['DB_PASSWORD'] ?? '';
    $host = $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? '127.0.0.1';
    $port = $_ENV['DB_PORT'] ?? $_SERVER['DB_PORT'] ?? '3306';

    if ($database === '' || $username === '') {
        fwrite(STDERR, "DB_DATABASE and DB_USERNAME are required for MySQL/MariaDB backups.\n");
        exit(1);
    }

    $destination = $outputDirectory."/zawajafrica-{$connection}-{$timestamp}.sql";
    $process = new Process([
        'mysqldump',
        '--single-transaction',
        '--routines',
        '--triggers',
        '--host='.$host,
        '--port='.$port,
        '--user='.$username,
        '--result-file='.$destination,
        $database,
    ], $root, ['MYSQL_PWD' => $password]);
    $process->setTimeout(3600);
    $process->run();

    if (!$process->isSuccessful()) {
        @unlink($destination);
        fwrite(STDERR, $process->getErrorOutput() ?: $process->getOutput());
        exit($process->getExitCode() ?: 1);
    }
} else {
    fwrite(STDERR, "Unsupported DB_CONNECTION '{$connection}'. Supported: sqlite, mysql, mariadb.\n");
    exit(1);
}

@chmod($destination, 0600);
$checksum = hash_file('sha256', $destination);
file_put_contents($destination.'.sha256', $checksum.'  '.basename($destination).PHP_EOL);
@chmod($destination.'.sha256', 0600);

fwrite(STDOUT, $destination.PHP_EOL.$destination.'.sha256'.PHP_EOL);
