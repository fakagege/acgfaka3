<?php
declare(strict_types=1);

namespace App\Plugin\PopupNotice\Hook;

use App\Controller\Base\View\UserPlugin;
use App\Util\Plugin;
use Kernel\Annotation\Hook;
use Kernel\Exception\ViewException;

class Main extends UserPlugin
{
    /**
     * @throws \ReflectionException
     * @throws ViewException
     */
    #[Hook(point: \App\Consts\Hook::USER_VIEW_INDEX_FOOTER)]
    public function test()
    {


        $cfg = Plugin::getConfig("PopupNotice");


        if ($cfg['life'] == 1) {
            if ($_SESSION['PopupNotice'] === true) {
                return;
            }
            $_SESSION['PopupNotice'] = true;
        } elseif ($cfg['life'] == 2) {
            if ($_COOKIE['PopupNotice'] === "1") {
                return;
            }

            setcookie("PopupNotice", "1", time() + 86400, "/");
        }


        $links = explode(PHP_EOL, trim((string)$cfg['link'], PHP_EOL));

        $linkTemp = [];

        foreach ($links as $str) {
            $str = trim(trim($str), PHP_EOL);

            if (!$str) {
                continue;
            }

            $link = explode("|", $str);

            $linkTemp[] = [
                "title" => $link[0],
                "url" => $link[1],
                "type" => $link[2],
                "target" => "_blank"
            ];
        }

        $jsConfig = [
            'icon' => $cfg['avatar'],
            'name' => $cfg['nickname'],
            'info' => $cfg['message'],
            'z_index' => 999999999,
            'lang' => "zh-CN",
            'mini' => true,
            'darkmode' => (bool)$cfg['darkmode'],
            'maxWidth' => $cfg['width'],
            'fontFamily' => "",
            'links' => $linkTemp
        ];

        $configBase64 = base64_encode(json_encode($jsConfig));

        echo $this->render("bt", "Notice.html", ["configBase64" => $configBase64]);
    }

}
