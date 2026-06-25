<?php
declare(strict_types=1);

namespace App\Controller\Base\View;


use App\Model\Business;
use App\Model\Config;
use App\Util\Client;
use App\Util\Theme;
use Kernel\Exception\ViewException;
use Kernel\Util\View;

/**
 * Class UserPlugin
 * @package App\Controller\Base\View
 */
abstract class UserPlugin extends \App\Controller\Base\User
{
    /**
     * @param string|null $title
     * @param string $template
     * @param array $data
     * @param bool $controller
     * @return string
     * @throws ViewException
     * @throws \ReflectionException
     */
    public function render(?string $title, string $template, array $data = [], bool $controller = false): string
    {
        try {
            $data['title'] = $title;
            $cfg = Config::list();
            foreach ($cfg as $k => $v) {
                $data["config"][$k] = $v;
            }

            if (Client::isMobile() && $data['config']['background_mobile_url']) {
                $data['config']['background_url'] = $data['config']['background_mobile_url'];
            }

            $domain = Client::getDomain();
            $business = Business::query()->where("subdomain", $domain)->first() ?? Business::query()->where("topdomain", $domain)->first();
            if ($business) {
                $data['config']['shop_name'] = $business->shop_name;
                $data['config']['title'] = $business->title;
                $data['config']['notice'] = $business->notice;
                
                // 智能判断客服链接类型
                $service_url = $business->service_url;
                if (empty($service_url) && !empty($business->service_qq)) {
                    // 如果只填了QQ号，判断是不是数字
                    if (is_numeric($business->service_qq)) {
                        $service_url = "https://wpa.qq.com/msgrd?v=1&uin={$business->service_qq}";
                    } else {
                        // 如果不是数字，直接作为链接使用
                        $service_url = $business->service_qq;
                    }
                }
                $data['config']['service_url'] = $service_url;
            }
            
            // 主站客服链接逻辑同上（如果没有商户）
            if (!isset($data['config']['service_url']) || empty($data['config']['service_url'])) {
                $main_service_qq = $data['config']['service_qq'] ?? '';
                if (!empty($main_service_qq)) {
                    if (is_numeric($main_service_qq)) {
                        $data['config']['service_url'] = "https://wpa.qq.com/msgrd?v=1&uin={$main_service_qq}";
                    } else {
                        $data['config']['service_url'] = $main_service_qq;
                    }
                }
            }
            $user = $this->getUser();
            if ($user) {
                $data['user'] = $user;
                $data['group'] = $this->getUserGroup()->toArray();
            }
            $data['setting'] = Theme::getConfig("Cartoon")["setting"];
            $data['default_view_path'] = BASE_PATH . '/app/View/User/Theme/Cartoon/';
            return View::render(
                $template,
                $data,
                BASE_PATH . "/app/Plugin/" . ($controller ? \Kernel\Util\Plugin::$currentControllerPluginName : \Kernel\Util\Plugin::$currentPluginName) . "/View",
                $controller
            );
        } catch (\SmartyException $e) {
            throw new ViewException($e->getMessage());
        }
    }
}