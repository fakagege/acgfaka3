<?php
declare (strict_types=1);

namespace App\Plugin\PayIntercept\Hook;

use App\Controller\Base\View\UserPlugin;
use App\Model\Commodity;
use App\Util\Http;
use App\Util\Plugin;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use Kernel\Annotation\Hook;
use Kernel\Exception\JSONException;
use Kernel\Exception\ViewException;
use voku\helper\ASCII;
use Zxing\Qrcode\Decoder\ECB;

class Main extends UserPlugin
{

    #[\Kernel\Annotation\Plugin(state: \Kernel\Annotation\Plugin::START)]
    public function InstallDB(): void
    {
        $db = Manager::schema()->hasColumn("commodity", "pay_intercept");
        if (!$db) {
            Manager::schema()->table("commodity", function (Blueprint $blueprint) {
                $blueprint->string("pay_intercept", 255)->nullable(true);
            });
        }
    }

    /**
     * @throws JSONException
     */
    #[Hook(point: \App\Consts\Hook::USER_API_ORDER_TRADE_BEGIN)]
    public function trade(): void
    {
        $cfg = Plugin::getConfig("PayIntercept");
        $commodityId = (int)$_POST['commodity_id'];
        $payId = (string)$_POST['pay_id'];

        $commodity = Commodity::query()->find($commodityId);

        if (!$commodity) {
            return;
        }

        $json = (array)json_decode((string)$commodity->pay_intercept);
        if (in_array($payId, $json)) {
            throw new JSONException((string)$cfg['msg']);
        }
    }


    #[Hook(point: \App\Consts\Hook::ADMIN_VIEW_COMMODITY_POST)]
    public function CommodityPost(): void
    {
        echo '{title: "拦截支付", name: "pay_intercept", type: "checkbox", dict: "pay,id,name"},';
    }

    /**
     * @throws \ReflectionException
     * @throws ViewException
     */
    #[Hook(point: \App\Consts\Hook::USER_VIEW_INDEX_FOOTER)]
    public function index(): void
    {
        echo $this->render("callback", "Pay.html");
    }
}