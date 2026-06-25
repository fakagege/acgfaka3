<?php
declare (strict_types=1);
# install symfony/var-dump to your project
# composer require symfony/var-dumper

// use namespace
use App\Util\Opcache;
use App\Util\Str;
use Kernel\Util\Plugin;
use Kernel\Util\View;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper as SymfonyHtmlDumper;

/**
 * Class HtmlDumper
 */
class HtmlDumper extends SymfonyHtmlDumper
{
    /**
     * Colour definitions for output.
     *
     * @var array
     */
    protected $styles = [
        'default' => 'background-color:#fff; color:#222; line-height:1.2em; font-weight:normal; font:12px Monaco, Consolas, monospace; word-wrap: break-word; white-space: pre-wrap; position:relative; z-index:100000',
        'num' => 'color:#a71d5d',
        'const' => 'color:#795da3',
        'str' => 'color:#df5000',
        'cchr' => 'color:#222',
        'note' => 'color:#a71d5d',
        'ref' => 'color:#a0a0a0',
        'public' => 'color:#795da3',
        'protected' => 'color:#795da3',
        'private' => 'color:#795da3',
        'meta' => 'color:#b729d9',
        'key' => 'color:#df5000',
        'index' => 'color:#a71d5d',
    ];
}

/**
 * Class Dumper
 */
class Dumper
{
    /**
     * Dump a value with elegance.
     *
     * @param mixed $value
     * @return void
     */
    public function dump($value)
    {
        if (class_exists(CliDumper::class)) {
            $dumper = 'cli' === PHP_SAPI ? new CliDumper : new HtmlDumper;
            $dumper->dump((new VarCloner)->cloneVar($value));
        } else {
            var_dump($value);
        }
    }
}

if (!function_exists('dd')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param mixed
     * @return void
     */
    function dd(...$args)
    {
        foreach ($args as $x) {
            (new Dumper)->dump($x);
        }
        die(1);
    }
}

if (!function_exists('dda')) {
    /**
     * Dump the passed array variables and end the script.
     *
     * @param mixed
     * @return void
     */
    function dda(...$args)
    {
        foreach ($args as $x) {
            (new Dumper)->dump($x->toArray());
        }
        die(1);
    }
}


if (!function_exists("config")) {
    /**
     * @param string $name
     * @return array
     */
    function config(string $name): array
    {
        $data = \Kernel\Util\Context::get("config_" . $name);
        if ($data) {
            return $data;
        }
        $file = BASE_PATH . '/config/' . $name . ".php";
        if (!file_exists($file)) {
            return [];
        }
        $data = require($file);
        \Kernel\Util\Context::set("config_" . $name, $data);
        return $data;
    }
}
if (!function_exists("setConfig")) {
    /**
     * @param array $data
     * @param string $file
     * @param bool $reset
     * @throws \Kernel\Exception\JSONException
     */
    function setConfig(array $data, string $file, bool $reset = false): void
    {
        if (file_exists($file) && !$reset) {
            $config = require($file);
        } else {
            $config = [];
        }
        foreach ($data as $x => $b) {
            $config[$x] = $b;
        }
        //写入到文件
        $ret = "<?php
declare (strict_types=1);\n\nreturn [\n";
        foreach ($config as $k => $v) {
            if (is_array($v)) {
                $akv = "[";
                foreach ($v as $av) {
                    $akv .= "'" . str_replace("'", "\\'", $av) . "'" . ",";
                }
                $akv = trim($akv, ",");
                $akv .= "]";
                $value = $akv;
            } else {
                $value = "'" . str_replace("'", "\\'", (string)$v) . "'";
            }
            $ret .= "    '{$k}' => $value,\n";
        }
        $ret .= '];';
        if (file_put_contents($file, $ret) === false) {
            throw new \Kernel\Exception\JSONException("没有文件写入权限");
        }

        Opcache::invalidate($file);
    }
}

if (!function_exists("di")) {
    /**
     * @param $object
     * @throws ReflectionException
     */
    function di(&$object)
    {
        $dependencies = config("dependencies");
        $ref = new \ReflectionClass($object);
        $reflectionProperties = $ref->getProperties();
        foreach ($reflectionProperties as $property) {
            $bs = $property->getAttributes();
            $bt = 0;
            foreach ($bs as $b) {
                if ($b->getName() == \Kernel\Annotation\Inject::class) {
                    $bt++;
                }
            }
            if ($bt == 0) {
                continue;
            }
            $reflectionProperty = new \ReflectionProperty($object, $property->getName());
            #拿到对象类型
            $type = $reflectionProperty->getType()->getName();
            $reflectionPropertiesAttributes = $reflectionProperty->getAttributes();
            foreach ($reflectionPropertiesAttributes as $propertiesAttribute) {
                $ins = $propertiesAttribute->newInstance();
                if ($ins instanceof \Kernel\Annotation\Inject) {
                    $service = $dependencies[$type];
                    if ($service) {
                        $obj = new $service;
                    } else {
                        $obj = new $type;
                    }
                    Closure::bind(function () use ($obj, $object, $property) {
                        $object->{$property->getName()} = $obj;
                    }, null, $object)();
                    di($obj);
                }
            }
        }
    }
}


if (!function_exists("dat")) {
    function dat(string $type, $value): float|object|int|bool|array|string
    {
        return match ($type) {
            "bool" => (boolean)$value,
            "int" => (integer)$value,
            "float" => (double)$value,
            "string" => (string)$value,
            "array" => (array)$value,
            "object" => (object)$value,
        };
    }
}
if (!function_exists("getLocalRouter")) {
    function getLocalRouter(): string
    {
        return \Kernel\Util\Context::get(\Kernel\Consts\Base::ROUTE);
    }
}

if (!function_exists("feedback")) {
    function feedback(string $value)
    {
        if ($value != "404 Not Found") {
            debug($value);
        }

        if (!DEBUG) {
            return View::render("404.html", ["msg" => "404 Not Found"]);
        }

        return "<!DOCTYPEhtml><htmllang='zh-CN'><head><meta name='viewport' content='width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no'><metacharset='utf-8'><title>{$value}</title></head><body style='margin: 0px;'><center style='color: #ffffff;background-color: #ff6f6f;padding-top: 18px;padding-bottom: 18px;font-size: 18px;'>{$value}</center></body></html>";
    }
}


if (!function_exists("hook")) {
    function hook(int $point, mixed &...$args)
    {
        $result = Plugin::hook($point, ...$args);
        if ($result) {
            return $result;
        }
    }
}

if (!function_exists("getHookNum")) {
    function getHookNum(int $point): int
    {
        return Plugin::getHookNum($point);
    }
}


if (!function_exists("debug")) {
    function debug(string $message): void
    {
        $path = BASE_PATH . '/runtime.log';
        file_put_contents($path, "[" . date("Y-m-d H:i:s", time()) . "]:" . $message . PHP_EOL, FILE_APPEND);
    }
}


if (!function_exists("getPluginConfig")) {
    function getPluginConfig(string $name)
    {
        return require(BASE_PATH . '/app/Plugin/' . $name . '/Config/Config.php');
    }
}

if (!function_exists("PluginView")) {
    function PluginView(string $src, bool $debug = false): string
    {
        $route = explode("/", trim($_GET['s'], "/"));
        if (strtolower($route[0]) == "plugin") {
            $pluginName = ucfirst($route[1]);
            return "/app/Plugin/{$pluginName}/View/{$src}?v=" . Plugin::getPlugin($pluginName)[\App\Consts\Plugin::VERSION] . (!$debug ?: "&debug=" . Str::generateRandStr(16));
        }

        return "";
    }
}

if (!function_exists("Plugin")) {
    function Plugin(string $pluginName, string $src, bool $debug = false): string
    {
        return "/app/Plugin/{$pluginName}/{$src}?v=" . Plugin::getPlugin($pluginName)[\App\Consts\Plugin::VERSION] . (!$debug ?: "&debug=" . Str::generateRandStr(16));
    }
}


if (!function_exists("css")) {
    function css(array|string $resource, array|string|null $backup = null, bool $cdn = true): string
    {
        if (DEBUG && $backup !== null) {
            $resource = $backup;
        }

        // 定义 CDN 映射表
        $cdnMap = [
            // 通用资源
            '/assets/common/css/bootstrap.min.css' => 'https://cdn.staticfile.org/twitter-bootstrap/4.6.1/css/bootstrap.min.css',
            '/assets/common/css/font.min.css' => 'https://cdn.staticfile.org/font-awesome/4.7.0/css/font-awesome.min.css',
            '/assets/common/js/layui/css/layui.css' => 'https://cdn.staticfile.org/layui/2.9.8/css/layui.css',
            '/assets/common/css/toastr.min.css' => 'https://cdn.staticfile.org/toastr.js/2.1.4/toastr.min.css',
            '/assets/common/css/select2.min.css' => 'https://cdn.staticfile.org/select2/4.0.13/css/select2.min.css',
            '/assets/static/bootstrap/css/bootstrap.min.css' => 'https://cdn.staticfile.org/twitter-bootstrap/4.6.1/css/bootstrap.min.css',
            '/assets/static/font/font-awesome-4.7.0/css/font-awesome.min.css' => 'https://cdn.staticfile.org/font-awesome/4.7.0/css/font-awesome.min.css',
            '/assets/static/css/jsoneditor.min.css' => 'https://cdn.staticfile.org/jsoneditor/9.10.0/jsoneditor.min.css',
            '/assets/static/bootstrap-table/css/bootstrap-table.css' => 'https://cdn.staticfile.org/bootstrap-table/1.22.1/bootstrap-table.min.css',
            '/assets/common/js/table/bootstrap-table.css' => 'https://cdn.staticfile.org/bootstrap-table/1.22.1/bootstrap-table.min.css',
        ];

        // 获取后台配置的 CDN 开关状态
        $enableCdn = \App\Model\Config::get('assets_cdn_status') == 1;

        $res = '';
        $debugRandom = DEBUG ? "&debug=" . Str::generateRandStr(8) : "";
        $cdnSupport = $cdn ? 'class="cdn-support"' : '';
        if (is_array($resource)) {
            foreach ($resource as $item) {
                $url = $item;
                // 如果开启了 CDN 且该文件在映射表中，则替换为 CDN 链接
                if ($enableCdn && isset($cdnMap[$item])) {
                    $url = $cdnMap[$item];
                } else {
                    $url = $item . '?v=' . APP_VERSION . $debugRandom;
                }
                $res .= sprintf('<link rel="stylesheet" href="%s" ' . $cdnSupport . '>', $url);
            }
        } else {
            $url = $resource;
            if ($enableCdn && isset($cdnMap[$resource])) {
                $url = $cdnMap[$resource];
            } else {
                $url = $resource . '?v=' . APP_VERSION . $debugRandom;
            }
            $res = sprintf('<link rel="stylesheet" href="%s" ' . $cdnSupport . '>', $url);
        }
        return $res;
    }
}

if (!function_exists("js")) {
    function js(array|string $resource, array|string|null $backup = null, bool $cdn = true): string
    {
        if (DEBUG && $backup !== null) {
            $resource = $backup;
        }

        // 定义 CDN 映射表
        $cdnMap = [
            // 通用资源
            '/assets/common/js/jquery.min.js' => 'https://cdn.staticfile.org/jquery/3.6.0/jquery.min.js',
            '/assets/common/js/bootstrap/bootstrap.bundle.min.js' => 'https://cdn.staticfile.org/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js',
            '/assets/common/js/layui/layui.js' => 'https://cdn.staticfile.org/layui/2.9.8/layui.js',
            '/assets/common/js/toastr.min.js' => 'https://cdn.staticfile.org/toastr.js/2.1.4/toastr.min.js',
            '/assets/common/js/component/select2.min.js' => 'https://cdn.staticfile.org/select2/4.0.13/js/select2.min.js',
            '/assets/static/jquery.min.js' => 'https://cdn.staticfile.org/jquery/3.6.0/jquery.min.js',
            '/assets/static/clipboard.js' => 'https://cdn.staticfile.org/clipboard.js/2.0.11/clipboard.min.js',
            '/assets/static/echarts.min.js' => 'https://cdn.staticfile.org/echarts/5.4.3/echarts.min.js',
            '/assets/static/wangEditor.min.js' => 'https://cdn.staticfile.org/wangEditor/10.0.13/wangEditor.min.js', // 注意：wangEditor版本可能需要根据实际兼容性调整
            '/assets/static/jquery.qrcode.min.js' => 'https://cdn.staticfile.org/jquery.qrcode/1.0/jquery.qrcode.min.js',
            '/assets/common/js/jquery.qrcode.min.js' => 'https://cdn.staticfile.org/jquery.qrcode/1.0/jquery.qrcode.min.js',
            '/assets/static/jsoneditor/jsoneditor.min.js' => 'https://cdn.staticfile.org/jsoneditor/9.10.0/jsoneditor.min.js',
            '/assets/static/bootstrap-table/js/bootstrap-table.min.js' => 'https://cdn.staticfile.org/bootstrap-table/1.22.1/bootstrap-table.min.js',
            '/assets/common/js/table/bootstrap-table.min.js' => 'https://cdn.staticfile.org/bootstrap-table/1.22.1/bootstrap-table.min.js',
            '/assets/common/js/table/bootstrap-table-treegrid.min.js' => 'https://cdn.staticfile.org/bootstrap-table/1.22.1/extensions/treegrid/bootstrap-table-treegrid.min.js',
        ];

        // 获取后台配置的 CDN 开关状态
        $enableCdn = \App\Model\Config::get('assets_cdn_status') == 1;

        $res = '';
        $debugRandom = DEBUG ? "&debug=" . Str::generateRandStr(8) : "";
        $cdnSupport = $cdn ? ' class="cdn-support"' : '';
        if (is_array($resource)) {
            foreach ($resource as $item) {
                $url = $item;
                if ($enableCdn && isset($cdnMap[$item])) {
                    $url = $cdnMap[$item];
                } else {
                    $url = $item . (str_contains($item, "?") ? "&" : "?") . 'v=' . APP_VERSION . $debugRandom;
                }
                $res .= sprintf('<script src="%s" ' . $cdnSupport . '></script>', $url);
            }
        } else {
            $url = $resource;
            if ($enableCdn && isset($cdnMap[$resource])) {
                $url = $cdnMap[$resource];
            } else {
                $url = $resource . (str_contains($resource, "?") ? "&" : "?") . 'v=' . APP_VERSION . $debugRandom;
            }
            $res = sprintf('<script src="%s" ' . $cdnSupport . '></script>', $url);
        }
        return $res;
    }
}


if (!function_exists('ready_get_value')) {

    /**
     * @param mixed $value
     * @return string|bool|null
     */
    function _ready_get_value(mixed $value): string|bool|null
    {
        if (is_numeric($value) || is_bool($value)) {
            // 对于数字和布尔值，不添加双引号
            $value = var_export($value, true);
        } elseif (is_array($value)) {
            // 如果是数组，转换为JSON
            $value = json_encode($value);
        } else {
            // 对于字符串，进行转义并添加双引号
            $value = addslashes((string)$value);
            $value = "\"$value\"";
        }
        return $value;
    }
}


if (!function_exists("ready")) {
    function ready(string $resource, array $variable = []): string
    {
        $var = '';
        foreach ($variable as $key => $value) {
            $var .= "setVar('{$key}' , " . _ready_get_value($value) . ");";
        }
        return '<script>' . $var . 'ready("' . $resource . (str_contains($resource, "?") ? "&" : "?") . 'v=' . APP_VERSION . (DEBUG ? "&debug=" . Str::generateRandStr(8) : '') . '");</script>';
    }
}


if (!function_exists("set_script_var")) {
    function set_script_var(array $vars): string
    {
        $str = "<script>";
        foreach ($vars as $name => $var) {
            $str .= "setVar(\"{$name}\"," . _ready_get_value($var) . ");";
        }
        return $str . "</script>";
    }
}

if (!function_exists("_plugin_get_hwid")) {
    function _plugin_get_hwid(): string
    {
        return "mock_hwid_123456";
    }
}

if (!function_exists("_plugin_hook_del")) {
    function _plugin_hook_del(string $name): void
    {
        // No-op
    }
}

if (!function_exists("_plugin_hook_add")) {
    function _plugin_hook_add(string $name): void
    {
        // No-op
    }
}

if (!function_exists("_plugin_hook_exist")) {
    function _plugin_hook_exist(string $name, int $point, string $namespace, string $method): bool
    {
        return true; 
    }
}

if (!function_exists("_plugin_start")) {
    function _plugin_start(string $name, bool $check = false): void
    {
        // Mock implementation
    }
}

if (!function_exists("_plugin_stop")) {
    function _plugin_stop(string $name): void
    {
        // Mock implementation
    }
}