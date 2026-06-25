<?php
declare(strict_types=1);

namespace App\Plugin\Bogeyman\Controller;

use App\Controller\Base\API\ManagePlugin;
use App\Interceptor\ManageSession;
use App\Interceptor\Waf;
use App\Util\Plugin;
use Kernel\Annotation\Interceptor;
use Kernel\Exception\JSONException;

#[Interceptor([Waf::class, ManageSession::class], Interceptor::TYPE_API)]
class Api extends ManagePlugin
{


    /**
     * @throws JSONException
     */
    public function add(): array
    {
        $content = (string)$_POST['content'];
        $type = (int)$_POST['type'];
        $config = Plugin::getConfig("Bogeyman");

        if ($type == 1) {
            $ip = trim(trim((string)$config['ip']), PHP_EOL);
            $ip .= PHP_EOL . $content;
            Plugin::setConfig("Bogeyman", "ip", $ip);
        } else {
            $contact = trim(trim((string)$config['contact']), PHP_EOL);
            $contact .= PHP_EOL . $content;
            Plugin::setConfig("Bogeyman", "contact", $contact);
        }

        return $this->json(200, "拉黑成功");
    }
}