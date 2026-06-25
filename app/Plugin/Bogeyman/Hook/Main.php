<?php
declare (strict_types=1);

namespace App\Plugin\Bogeyman\Hook;

use App\Controller\Base\View\ManagePlugin;
use App\Util\Client;
use App\Util\Plugin;
use Kernel\Annotation\Hook;
use Kernel\Exception\JSONException;
use Kernel\Exception\ViewException;

class Main extends ManagePlugin
{

    /**
     * @throws JSONException
     */
    #[Hook(point: \App\Consts\Hook::USER_API_ORDER_TRADE_BEGIN)]
    public function trade(): void
    {
        $config = Plugin::getConfig("Bogeyman");

        $ip = explode(PHP_EOL, trim(trim((string)$config['ip']), PHP_EOL));
        $contact = explode(PHP_EOL, trim(trim((string)$config['contact']), PHP_EOL));

        foreach ($ip as $item) {
            if (!$item) {
                continue;
            }

            if (trim($item) == Client::getAddress()) {
                throw new JSONException($config['msg']);
            }
        }

        foreach ($contact as $item) {
            if (!$item) {
                continue;
            }

            if (trim($item) == trim((string)$_POST['contact'])) {
                throw new JSONException($config['msg']);
            }
        }
    }

    /**
     * @throws ViewException
     */
    #[Hook(point: \App\Consts\Hook::ADMIN_VIEW_ORDER_TABLE)]
    public function aide()
    {
        echo $this->render(null, "Aide.hook");
    }
}