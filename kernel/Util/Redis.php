<?php
declare(strict_types=1);

namespace Kernel\Util;

use Kernel\Component\Singleton;

class Redis
{
    use Singleton;

    private ?\Redis $redis = null;
    private bool $connected = false;
    private array $config = [];

    public function __construct()
    {
        $this->config = config('redis');
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        if (!$this->config['enable']) {
            return false;
        }
        if (!extension_loaded('redis')) {
            return false;
        }
        if (!$this->connected) {
            $this->connect();
        }
        return $this->connected;
    }

    private function connect(): void
    {
        try {
            $this->redis = new \Redis();
            $host = $this->config['host'] ?? '127.0.0.1';
            $port = (int)($this->config['port'] ?? 6379);
            $timeout = (float)($this->config['timeout'] ?? 1.0);
            
            if ($this->redis->connect($host, $port, $timeout)) {
                if (!empty($this->config['password'])) {
                    if (!$this->redis->auth($this->config['password'])) {
                        $this->connected = false;
                        return;
                    }
                }
                if (isset($this->config['db'])) {
                    $this->redis->select((int)$this->config['db']);
                }
                $this->connected = true;
            }
        } catch (\Throwable $e) {
            $this->connected = false;
        }
    }

    /**
     * @param string $key
     * @return string|false
     */
    public function get(string $key): string|false
    {
        if (!$this->isAvailable()) {
            return false;
        }
        try {
            return $this->redis->get($key);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int|null $ttl
     * @return bool
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }
        try {
            if ($ttl) {
                return $this->redis->setex($key, $ttl, (string)$value);
            }
            return $this->redis->set($key, (string)$value);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @param string $key
     * @return int
     */
    public function del(string $key): int
    {
        if (!$this->isAvailable()) {
            return 0;
        }
        try {
            return $this->redis->del($key);
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
