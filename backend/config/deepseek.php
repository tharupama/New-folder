<?php
// Minimal .env loader for local development.
function loadEnvFile(string $envPath): void
{
    if (!file_exists($envPath)) {
        return;
    }

    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#')) {
            continue;
        }

        $parts = explode('=', $trimmed, 2);
        if (count($parts) !== 2) {
            continue;
        }

        $key = trim($parts[0]);
        $value = trim($parts[1]);
        if ($key === '') {
            continue;
        }

        // Do not overwrite existing environment variables.
        if (getenv($key) === false) {
            putenv($key . '=' . $value);
        }
    }
}

loadEnvFile(__DIR__ . '/../../.env');

return [
    // Read the key from environment variables.
    'api_key' => getenv('DEEPSEEK_API_KEY') ?: '',
    // Model name
    'model' => 'deepseek-chat'
];
