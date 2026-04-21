<?php

namespace App\Services;

/**
 * Минимальный rate limiter для POST /login.
 * Хранит счётчик неудачных попыток по IP в файлах storage/cache.
 * После MAX_ATTEMPTS неудачных попыток блокирует IP на DECAY_SECONDS секунд.
 * Успешный вход сбрасывает счётчик.
 */
class LoginRateLimiter
{
    private const MAX_ATTEMPTS  = 5;
    private const DECAY_SECONDS = 300; // 5 минут

    private string $storageDir;

    public function __construct()
    {
        $this->storageDir = BASE_PATH . '/storage/cache';
    }

    public function tooManyAttempts(string $ip): bool
    {
        $data = $this->read($ip);

        return $data !== null && $data['locked_until'] > time();
    }

    /** Засчитать неудачную попытку и при необходимости выставить блокировку. */
    public function hit(string $ip): void
    {
        $data = $this->read($ip) ?? ['attempts' => 0, 'locked_until' => 0];

        $data['attempts']++;

        if ($data['attempts'] >= self::MAX_ATTEMPTS) {
            $data['locked_until'] = time() + self::DECAY_SECONDS;
        }

        $this->write($ip, $data);
    }

    /** Сбросить счётчик после успешного входа. */
    public function clear(string $ip): void
    {
        $file = $this->filePath($ip);

        if (is_file($file)) {
            @unlink($file);
        }
    }

    /** Секунды до снятия блокировки. */
    public function availableIn(string $ip): int
    {
        $data = $this->read($ip);

        return $data !== null ? max(0, $data['locked_until'] - time()) : 0;
    }

    private function filePath(string $ip): string
    {
        return $this->storageDir . '/rl_login_' . md5($ip) . '.json';
    }

    private function read(string $ip): ?array
    {
        $file = $this->filePath($ip);

        if (!is_file($file)) {
            return null;
        }

        $raw  = file_get_contents($file);
        $data = json_decode((string) $raw, true);

        if (!is_array($data)) {
            return null;
        }

        // Срок блокировки истёк — удалить файл и вернуть null
        if (isset($data['locked_until']) && $data['locked_until'] > 0 && $data['locked_until'] < time()) {
            @unlink($file);
            return null;
        }

        return $data;
    }

    private function write(string $ip, array $data): void
    {
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }

        file_put_contents($this->filePath($ip), json_encode($data), LOCK_EX);
    }
}




