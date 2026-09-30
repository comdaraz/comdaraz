<?php

namespace App\Core;

class Cache {
    private static string $storagePath = __DIR__ . '/../../storage/cache/';

    public static function get(string $key, mixed $default = null, int $ttl = 3600): mixed {
        $filePath = self::getFilePath($key);

        if (file_exists($filePath)) {
            $content = @file_get_contents($filePath);
            if ($content !== false) {
                $data = @unserialize($content);
                if (is_array($data) && isset($data['expires_at']) && time() <= $data['expires_at']) {
                    return $data['value'];
                }
            }
            @unlink($filePath);
        }

        if (is_callable($default)) {
            $value = call_user_func($default);
            if ($value !== null) {
                self::set($key, $value, $ttl);
            }
            return $value;
        }

        return $default;
    }

    public static function set(string $key, mixed $value, int $ttl = 3600): bool {
        $filePath = self::getFilePath($key);
        $data = [
            'expires_at' => time() + $ttl,
            'value' => $value
        ];

        return (bool)@file_put_contents($filePath, serialize($data), LOCK_EX);
    }

    public static function delete(string $key): bool {
        $filePath = self::getFilePath($key);
        if (file_exists($filePath)) {
            return @unlink($filePath);
        }
        return true;
    }

    public static function clear(): bool {
        $files = glob(self::$storagePath . '*.cache');
        if ($files) {
            foreach ($files as $file) {
                @unlink($file);
            }
        }
        return true;
    }

    private static function getFilePath(string $key): string {
        return self::$storagePath . md5($key) . '.cache';
    }
}
