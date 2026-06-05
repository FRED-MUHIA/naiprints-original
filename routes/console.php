<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\Console\Command\Command;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('admin:password {password? : The new admin password} {--username=Admin : The admin username}', function (?string $password = null) {
    $username = trim((string) $this->option('username'));
    $password = $password ?: $this->secret('New admin password');

    if ($username === '') {
        $this->error('The admin username cannot be empty.');

        return Command::FAILURE;
    }

    if (!is_string($password) || strlen($password) < 8) {
        $this->error('The admin password must be at least 8 characters.');

        return Command::FAILURE;
    }

    $envPath = base_path('.env');
    if (!is_file($envPath) || !is_writable($envPath)) {
        $this->error('The .env file could not be found or is not writable.');

        return Command::FAILURE;
    }

    $contents = file_get_contents($envPath);
    if ($contents === false) {
        $this->error('Unable to read the .env file.');

        return Command::FAILURE;
    }

    $contents = setEnvValue($contents, 'ADMIN_USERNAME', quoteEnvValue($username));
    $contents = setEnvValue($contents, 'ADMIN_PASSWORD_HASH', quoteEnvValue(Hash::make($password)));

    if (file_put_contents($envPath, $contents) === false) {
        $this->error('Unable to write the .env file.');

        return Command::FAILURE;
    }

    Artisan::call('config:clear');

    $this->info("Admin login updated for [{$username}].");

    return Command::SUCCESS;
})->purpose('Set the configured admin username and password hash');

function setEnvValue(string $contents, string $key, string $value): string
{
    $line = "{$key}={$value}";
    $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';

    if (preg_match($pattern, $contents) === 1) {
        return preg_replace_callback($pattern, fn () => $line, $contents) ?? $contents;
    }

    return rtrim($contents) . PHP_EOL . $line . PHP_EOL;
}

function quoteEnvValue(string $value): string
{
    return "'" . str_replace(['\\', "'"], ['\\\\', "\\'"], $value) . "'";
}
