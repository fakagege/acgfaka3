<?php
declare(strict_types=1);

namespace App\Plugin\Entrance\Hook;


use App\Controller\Base\View\ManagePlugin;
use App\Util\Client;
use App\Util\Plugin;
use App\Util\Str;
use Kernel\Annotation\Hook;
use Kernel\Util\Session;


class Main extends ManagePlugin
{

    /**
     * 安全认证状态
     */
    const ENTRANCE_SESSION = "entrance_status";


    #[Hook(point: \App\Consts\Hook::KERNEL_INIT)]
    public function entrance(): void
    {
        $config = Plugin::getConfig("Entrance");
        Plugin::log("Entrance", "Entrance hook running. Config: " . json_encode($config));
        
        if (!isset($config['location'])) {
            return;
        }
        if (trim(trim((string)$config['location']), "/") == "") {
            return;
        }
        $entrance = strtolower(trim(trim($config['location']), "/"));
        $location = explode("/", trim((string)$_GET['s'], "/"));
        $route = strtolower($location[0]);
        $ip = Client::getAddress();
        
        Plugin::log("Entrance", "Route: {$route}, Entrance: {$entrance}, IP: {$ip}");

        if ($config['white'] == 1 && $route == "admin") {
            if (!str_contains((string)$config['whitelist'], $ip)) {
                echo $this->render("您的IP被阻挡", "error.html", [
                    "ip" => '<span style="color:red;">' . htmlspecialchars($ip) . '</span>',
                    "ua" => htmlspecialchars((string)$_SERVER['HTTP_USER_AGENT']),
                    "server_ip" => mt_rand(20, 245) . "." . mt_rand(20, 245) . "." . mt_rand(20, 245) . "." . mt_rand(20, 245),
                    "id" => Str::generateRandStr(16)
                ]);
                exit;
            }
        }

        if ($route == $entrance) {
            //安全通过认证
            Session::set(self::ENTRANCE_SESSION, true);
            Plugin::log("Entrance", "Entrance matched. Redirecting to /admin.");
            Client::redirect("/admin", "安全认证成功，请稍后..", 1);
            return;
        }

        if ($route == "admin") {
            $hasSession = Session::has(self::ENTRANCE_SESSION);
            $sessionValue = Session::get(self::ENTRANCE_SESSION);
            Plugin::log("Entrance", "Checking admin access. Session has: " . ($hasSession ? 'yes' : 'no') . ", Value: " . json_encode($sessionValue));
            
            if (!$hasSession || $sessionValue !== true) {
                Plugin::log("Entrance", "Access denied. Rendering error page.");
                echo $this->render("没有使用正确入口访问后台", "error.html", [
                    "ip" => '<span style="color:red;">' . htmlspecialchars($ip) . '</span>',
                    "ua" => htmlspecialchars((string)$_SERVER['HTTP_USER_AGENT']),
                    "server_ip" => mt_rand(20, 245) . "." . mt_rand(20, 245) . "." . mt_rand(20, 245) . "." . mt_rand(20, 245),
                    "id" => Str::generateRandStr(16)
                ]);
                exit;
            }
        }
    }
}
