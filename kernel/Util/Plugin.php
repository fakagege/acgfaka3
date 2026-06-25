<?php
declare(strict_types=1);

namespace Kernel\Util;


use App\Util\Opcache;
use Kernel\Consts\Base;
use Kernel\Container\Di;
use Kernel\Plugin\Entity\Stock;

class Plugin
{
    /**
     * @var array
     */
    public static array $container = [];

    /**
     * @var string|null
     */
    public static ?string $currentPluginName = null;

    /**
     * @var string|null
     */
    public static ?string $currentControllerPluginName = null;

    /**
     * @param string $name
     * @param bool $cache
     * @return array|null
     */
    public static function getPlugin(string $name, bool $cache = true): ?array
    {
        $path = BASE_PATH . "/app/Plugin/{$name}";

        $infoPath = $path . '/Config/Info.php';
        $submitPath = $path . '/Config/Submit.php';
        $configPath = $path . '/Config/Config.php';
        if (!file_exists($infoPath) || !file_exists($submitPath) || !file_exists($configPath)) {
            return null;
        }

        if (!$cache) {
            Opcache::invalidate($infoPath, $submitPath, $configPath);
        }

        $info = (array)require($infoPath);
        $submit = (array)require($submitPath);
        $config = (array)require($configPath);

        //submit
        if (is_array($submit)) {
            foreach ($submit as $index => $item) {
                if (isset($config[$item['name']])) {
                    $submit[$index]['default'] = ($item['name'] == \App\Consts\Plugin::STATUS ? (int)$config[$item['name']] : $config[$item['name']]);
                }
            }
        }
        $info[\App\Consts\Plugin::PLUGIN_SUBMIT] = $submit;
        $info[\App\Consts\Plugin::PLUGIN_CONFIG] = $config;
        return $info;
    }

    /**
     * @param bool $cache
     * @return array|null
     */
    public static function getPlugins(bool $cache = true): ?array
    {
        $path = BASE_PATH . "/app/Plugin/";
        $scan = File::scan($path);
        $plugins = [];
        foreach ($scan as $item) {
            $plugin = self::getPlugin($item, $cache);
            if ($plugin) {
                $plugin[\App\Consts\Plugin::PLUGIN_NAME] = $item;
                $plugins[] = $plugin;
            }
        }
        return $plugins;
    }

    /**
     * @param string $pluginName
     * @param int $state
     * @param mixed ...$args
     * @throws \ReflectionException
     */
    public static function runHookState(string $pluginName, int $state, mixed ...$args): void
    {
        // Lifecycle debug log (写入全局 hook_debug，不进入业务日志)
        $stateName = match ($state) {
            \Kernel\Annotation\Plugin::START => 'START',
            \Kernel\Annotation\Plugin::STOP => 'STOP',
            \Kernel\Annotation\Plugin::UNINSTALL => 'UNINSTALL',
            \Kernel\Annotation\Plugin::INSTALL => 'INSTALL',
            \Kernel\Annotation\Plugin::UPGRADE => 'UPGRADE',
            \Kernel\Annotation\Plugin::SAVE_CONFIG => 'SAVE_CONFIG',
            default => (string)$state,
        };
        // 专属业务日志：仅记录启用/停用到插件自身 runtime.log
        if ($state === \Kernel\Annotation\Plugin::START || $state === \Kernel\Annotation\Plugin::STOP) {
            $cn = $state === \Kernel\Annotation\Plugin::START ? '插件启用' : '插件停用';
            try { \App\Util\Plugin::log($pluginName, $cn . '开始'); } catch (\Throwable $e) {}
        }
        // @file_put_contents(BASE_PATH . '/runtime/hook_debug.log', '[' . date('Y-m-d H:i:s') . "] Lifecycle plugin={$pluginName} state={$stateName} begin\n", FILE_APPEND);

        //扫描目标目录文件
        $path = BASE_PATH . "/app/Plugin/{$pluginName}/Hook/";
        //扫描插件的hook
        $hookScan = File::scan($path, true);
        foreach ($hookScan as $class) {
            $_class = explode(".", $class);
            $_className = trim((string)$_class[0]);
            $namespace = "\\App\\Plugin\\{$pluginName}\\Hook\\{$_className}";
            if (class_exists($namespace)) {
                $reflectionClass = new \ReflectionClass(objectOrClass: $namespace);
                foreach ($reflectionClass->getMethods() as $method) {
                    $reflectionMethod = new \ReflectionMethod($namespace, $method->getName());
                    $reflectionAttributes = $reflectionMethod->getAttributes();
                    foreach ($reflectionAttributes as $attribute) {
                        $arguments = $attribute->getArguments();
                        if ($attribute->newInstance() instanceof \Kernel\Annotation\Plugin) {
                            if ($arguments['state'] == $state) {
                                // @file_put_contents(BASE_PATH . '/runtime/hook_debug.log', '[' . date('Y-m-d H:i:s') . "] Lifecycle plugin={$pluginName} exec {$namespace}::{$method->getName()} state={$stateName}\n", FILE_APPEND);
                                call_user_func_array([new $namespace, $method->getName()], $args);
                            }
                        }
                    }
                }
            }
        }
        // @file_put_contents(BASE_PATH . '/runtime/hook_debug.log', '[' . date('Y-m-d H:i:s') . "] Lifecycle plugin={$pluginName} state={$stateName} end\n", FILE_APPEND);
        if ($state === \Kernel\Annotation\Plugin::START || $state === \Kernel\Annotation\Plugin::STOP) {
            $cn = $state === \Kernel\Annotation\Plugin::START ? '插件启用' : '插件停用';
            try { \App\Util\Plugin::log($pluginName, $cn . '完成'); } catch (\Throwable $e) {}
        }
    }

    /**
     * @param int $point
     * @param mixed ...$args
     * @return array|Stock|string|void
     * @throws \ReflectionException
     */
    public static function hook(int $point, mixed &...$args)
    {
        if (Context::get(Base::IS_INSTALL)) {
            $list = (Plugin::$container['hook'] ?? [])[$point] ?? [];
            $results = "";

            foreach ($list as $item) {
                if (!is_dir(BASE_PATH . "/app/Plugin/{$item['pluginName']}")) continue;
                if (!class_exists($item['namespace'])) continue;
                Plugin::$currentPluginName = $item['pluginName'];
                $instance = new $item['namespace'];
                Di::inst()->inject($instance);
                // Debug hook log → 写入全局 hook_debug，不进入插件业务日志
                // @file_put_contents(BASE_PATH . '/runtime/hook_debug.log', '[' . date('Y-m-d H:i:s') . "] Hook plugin=" . $item['pluginName'] . " point={$point} begin " . $item['namespace'] . '::' . $item['method'] . "\n", FILE_APPEND);
                try {
                    $result = call_user_func_array([$instance, $item['method']], $args);
                } catch (\Throwable $e) {
                    // @file_put_contents(BASE_PATH . '/runtime/hook_debug.log', '[' . date('Y-m-d H:i:s') . "] Hook plugin=" . $item['pluginName'] . " point={$point} error " . $item['namespace'] . '::' . $item['method'] . ' - ' . $e->getMessage() . "\n", FILE_APPEND);
                    throw $e;
                }
                $rtype = is_object($result) ? get_class($result) : gettype($result);
                $snippet = '';
                if (is_string($result)) {
                    $snippet = substr($result, 0, 256);
                } elseif (is_array($result)) {
                    $encoded = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    $snippet = substr((string)$encoded, 0, 256);
                }
                // @file_put_contents(BASE_PATH . '/runtime/hook_debug.log', '[' . date('Y-m-d H:i:s') . "] Hook plugin=" . $item['pluginName'] . " point={$point} done " . $item['namespace'] . '::' . $item['method'] . " -> {$rtype}" . ($snippet !== '' ? ", snippet=" . $snippet : '') . "\n", FILE_APPEND);
                if (is_string($result)) {
                    $results .= $result;
                } elseif (is_array($result)) {
                    if ($results === "") {
                        $results = [];
                    }
                    $results[] = $result;
                } elseif ($result instanceof Stock) {
                    return $result;
                }
            }

            return $results;
        }
    }

    /**
     * @param int $point
     * @return int
     */
    public static function getHookNum(int $point): int
    {
        return (int)count((array)self::$container['hook'][$point]);
    }
}
