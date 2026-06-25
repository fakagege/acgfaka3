<?php
declare (strict_types=1);

namespace Kernel\Plugin;

use App\Util\Client;
use Kernel\Component\Singleton;
use Kernel\Consts\Base;
use Kernel\Util\Binary;
use Kernel\Util\Context;
use Kernel\Util\File;
use Kernel\Util\Plugin;

class Hook
{

    use Singleton;

    public const CACHE_FILE = BASE_PATH . "/runtime/plugin/hook";


    /**
     * @return void
     */
    public function load(bool $force = false): void
    {
        if (!$force && !empty($this->hook)) {
            return;
        }

        /*
        // Original Code (Commented out)
        if (!is_writable(self::CACHE_FILE)) {
             return;
        }
 
        if (file_exists(self::CACHE_FILE)) {
             $content = @file_get_contents(self::CACHE_FILE);
             $content = _plugin_aes_decrypt($content, _plugin_get_hwid());
             $json = json_decode($content, true);
             if (is_array($json)) {
                 $this->hook = $json;
                 return;
             }
        }
        */

        // Custom Hook Loading Mechanism to bypass encryption
        $hooks = [];
        $pluginDir = BASE_PATH . '/app/Plugin';

        // Add debug logging
        // $logFile = BASE_PATH . '/runtime/hook_debug.log';
        // file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Starting hook loading...\n", FILE_APPEND);

        if (is_dir($pluginDir)) {
            $plugins = scandir($pluginDir);
            foreach ($plugins as $plugin) {
                if ($plugin === '.' || $plugin === '..') {
                    continue;
                }

                // Respect plugin STATUS (only enable when STATUS == 1)
                $configPath = $pluginDir . '/' . $plugin . '/Config/Config.php';
                $enabled = true;
                if (file_exists($configPath)) {
                    try {
                        $cfg = (array)require($configPath);
                        if (isset($cfg['STATUS'])) {
                            $enabled = ((int)$cfg['STATUS'] === 1);
                        }
                    } catch (\Throwable $e) {
                        // If config fails to load, skip status check
                        $enabled = true;
                    }
                }

                if (!$enabled) {
                    // file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Plugin '$plugin' disabled by STATUS. Skipping.\n", FILE_APPEND);
                    continue;
                }

                $hookDir = $pluginDir . '/' . $plugin . '/Hook';
                if (!is_dir($hookDir)) {
                    continue;
                }

                // Scan all PHP files under Hook directory
                $hookFiles = scandir($hookDir);
                foreach ($hookFiles as $file) {
                    if ($file === '.' || $file === '..') {
                        continue;
                    }
                    if (!str_ends_with($file, '.php')) {
                        continue;
                    }
                    $hookFile = $hookDir . '/' . $file;
                    // file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Found hook file: $hookFile\n", FILE_APPEND);

                    // Build namespace from filename
                    $classBase = pathinfo($file, PATHINFO_FILENAME);
                    $namespace = "\\App\\Plugin\\{$plugin}\\Hook\\{$classBase}";
                    if (!class_exists($namespace)) {
                        // file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Class not found: $namespace\n", FILE_APPEND);
                        continue;
                    }

                    try {
                        $reflectionClass = new \ReflectionClass($namespace);
                        foreach ($reflectionClass->getMethods() as $method) {
                            $rm = new \ReflectionMethod($namespace, $method->getName());
                            // Only parse our Hook attribute
                            $attributes = $rm->getAttributes(\Kernel\Annotation\Hook::class);
                            foreach ($attributes as $attribute) {
                                $args = $attribute->getArguments();
                                $point = $args['point'] ?? ($args[0] ?? null);
                                if ($point === null) {
                                    continue;
                                }
                                $hooks[$point][] = [
                                    'pluginName' => $plugin,
                                    'namespace' => $namespace,
                                    'method' => $method->getName()
                                ];
                                /*
                                file_put_contents(
                                    $logFile,
                                    "[" . date('Y-m-d H:i:s') . "] Registered hook: plugin={$plugin}, class={$namespace}, method=" . $method->getName() . ", point=" . $point . "\n",
                                    FILE_APPEND
                                );
                                */
                            }
                        }
                    } catch (\Throwable $e) {
                        // file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Reflection error in $namespace: " . $e->getMessage() . "\n", FILE_APPEND);
                        continue;
                    }
                }
            }
        }

        $this->hook = $hooks;
        // Also propagate to the global plugin container so Kernel\\Util\\Plugin::hook() can execute
        \Kernel\Util\Plugin::$container['hook'] = $hooks;
        // file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Hook loading complete. Hooks: " . json_encode($this->hook) . "\n", FILE_APPEND);
    }

    /**
     * @param string $name
     * @return void
     */
    public function del(string $name): void
    {
        _plugin_hook_del($name);
    }


    /**
     * @param string $name
     * @return void
     */
    public function add(string $name): void
    {
        _plugin_hook_add($name);
    }


    /**
     * @param string $name
     * @param int $point
     * @param string $namespace
     * @param string $method
     * @return bool
     */
    public function exist(string $name, int $point, string $namespace, string $method): bool
    {
        return _plugin_hook_exist($name, $point, $namespace, $method);
    }
}
