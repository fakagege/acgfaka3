<?php
declare(strict_types=1);

namespace App\Plugin\TelegramNotice\Hook;


use App\Controller\Base\View\ManagePlugin;
use App\Model\Commodity;
use App\Model\Order;
use App\Model\Pay;
use App\Model\User;
use App\Util\Http;
use App\Util\Plugin;
use GuzzleHttp\Exception\GuzzleException;
use Kernel\Annotation\Hook;

class Main extends ManagePlugin
{


    /**
     * @param string $msg
     * @throws GuzzleException
     */
    private function sendMsg(string $msg): void
    {
        $config = Plugin::getConfig("TelegramNotice");
        Http::make(['connect_timeout' => 6.14, 'timeout' => 6.14])->post("https://api.telegram.org/bot". $config['token'] ."/sendMessage", [
            'form_params' => [
                'chat_id' => $config['userId'],
                'text' => $msg,
                'parse_mode' => 'HTML'
            ]
        ]);
    }

    #[Hook(point: \App\Consts\Hook::USER_API_ORDER_TRADE_AFTER)]
    public function trade(Commodity $commodity, Order $order, Pay $pay)
    {
        try {
            $config = Plugin::getConfig("TelegramNotice");
            if ($config['trade'] == 1 && $pay->handle != "#system") {
                $tradeContent = $config['trade_content'];
                preg_match_all("#\[([\S]+?)\]#", $tradeContent, $match);
                $keys = $match[1];
                if (is_array($keys)) {
                    foreach ($keys as $item) {
                        $tr = explode(".", $item);
                        $table = $tr[0];
                        $field = $tr[1];
                        if ($table == "order") {
                            $tradeContent = str_replace("[{$item}]", strip_tags((string)$order->$field), $tradeContent);
                        } elseif ($table == "commodity") {
                            $tradeContent = str_replace("[{$item}]", strip_tags((string)$commodity->$field), $tradeContent);
                        } elseif ($table == "pay") {
                            $tradeContent = str_replace("[{$item}]", strip_tags((string)$pay->$field), $tradeContent);
                        } elseif ($table == "user") {
                            //查询用户信息
                            $user = User::query()->find($order->owner);
                            if ($user instanceof User) {
                                $tradeContent = str_replace("[{$item}]", strip_tags((string)$user->$field), $tradeContent);
                            } else {
                                $tradeContent = str_replace("[{$item}]", '-', $tradeContent);
                            }
                        }
                    }
                }
                $this->sendMsg($tradeContent);
            }
        } catch (\Error|\Exception|GuzzleException $e) {
            Plugin::log("TelegramNotice", "推送错误，原因：" . $e->getMessage());
        }
    }

    #[Hook(point: \App\Consts\Hook::USER_API_ORDER_PAY_AFTER)]
    public function pay(Commodity $commodity, Order $order, Pay $pay)
    {
        try {
            $config = Plugin::getConfig("TelegramNotice");
            if ($config['payment'] == 1) {
                $paymentContent = $config['payment_content'];
                preg_match_all("#\[([\S]+?)\]#", $paymentContent, $match);
                $keys = $match[1];
                if (is_array($keys)) {
                    foreach ($keys as $item) {
                        $tr = explode(".", $item);
                        $table = $tr[0];
                        $field = $tr[1];
                        if ($table == "order") {
                            $paymentContent = str_replace("[{$item}]", strip_tags((string)$order->$field), $paymentContent);
                        } elseif ($table == "commodity") {
                            $paymentContent = str_replace("[{$item}]", strip_tags((string)$commodity->$field), $paymentContent);
                        } elseif ($table == "pay") {
                            $paymentContent = str_replace("[{$item}]", strip_tags((string)$pay->$field), $paymentContent);
                        } elseif ($table == "user") {
                            //查询用户信息
                            $user = User::query()->find($order->owner);
                            if ($user instanceof User) {
                                $paymentContent = str_replace("[{$item}]", strip_tags((string)$user->$field), $paymentContent);
                            } else {
                                $paymentContent = str_replace("[{$item}]", '-', $paymentContent);
                            }
                        }
                    }
                }

                $this->sendMsg($paymentContent);
            }
        } catch (\Error|\Exception|GuzzleException $e) {
            Plugin::log("TelegramNotice", "推送错误，原因：" . $e->getMessage());
        }
    }
}
