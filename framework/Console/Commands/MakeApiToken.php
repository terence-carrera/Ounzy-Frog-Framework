<?php

namespace Frog\Console\Commands;

use Frog\Console\Command;

class MakeApiToken extends Command
{
    protected string $signature = 'make:api-token';
    protected string $description = 'Generate a secure API token (argon2id hashed)';

    public function handle(array $arguments = []): int
    {
        $length = 32;
        foreach ($arguments as $arg) {
            if (str_starts_with($arg, '--length=')) {
                $length = max(16, (int)substr($arg, 9));
            }
        }
        // Raw random bytes -> base64url
        $raw = random_bytes($length);
        $token = rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
        $hash = password_hash($token, PASSWORD_ARGON2ID, [
            'memory_cost' => 1 << 17, // 128MB
            'time_cost'   => 3,
            'threads'     => 2,
        ]);
        $this->info('Plain token (store client-side):');
        $this->line($token);
        $this->info('Argon2id hash (stored in .env as API_TOKEN):');
        $this->line($hash);

        $envPath = getcwd() . DIRECTORY_SEPARATOR . '.env';
        if (is_file($envPath) && is_writable($envPath)) {
            $contents = file_get_contents($envPath);
            if ($contents === false) {
                $this->warn('Could not read .env to update');
                return 0;
            }
            $backup = $envPath . '.bak';
            @file_put_contents($backup, $contents);

            if (preg_match('/^API_TOKEN=.*$/m', $contents)) {
                $contents = preg_replace('/^API_TOKEN=.*$/m', 'API_TOKEN=' . $hash, $contents, 1);
            } else {
                $contents .= (str_ends_with($contents, "\n") ? '' : "\n") . 'API_TOKEN=' . $hash . "\n";
            }
            if (file_put_contents($envPath, $contents) === false) {
                $this->warn('Failed writing updated .env');
            } else {
                $this->info('.env updated (previous saved to .env.bak).');
            }
        } else {
            $this->line('To put into .env set API_TOKEN="' . $hash . '"');
        }
        return 0;
    }
}

