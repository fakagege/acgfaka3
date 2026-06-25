<?php
declare(strict_types=1);

namespace App\Controller\Admin\Api;


use App\Controller\Base\API\Manage;
use App\Entity\Query\Delete;
use App\Entity\Query\Get;
use App\Entity\Query\Save;
use App\Interceptor\ManageSession;
use App\Model\ManageLog;
use App\Service\Query;
use App\Util\Date;
use App\Util\Ini;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Request;
use Kernel\Exception\JSONException;
use Kernel\Exception\NotFoundException;
use Kernel\Waf\Filter;

#[Interceptor(ManageSession::class, Interceptor::TYPE_API)]
class Card extends Manage
{
    #[Inject]
    private Query $query;

    /**
     * @return array
     */
    public function data(): array
    {
        $map = $_POST;
        $get = new Get(\App\Model\Card::class);
        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        $get->setWhere($map);
        $data = $this->query->get($get, function (Builder $builder) {
            return $builder->with([
                'owner' => function (Relation $relation) {
                    $relation->select(["id", "username", "avatar"]);
                },
                'commodity' => function (Relation $relation) {
                    $relation->select(["id", "cover", "name"]);
                },
                'order' => function (Relation $relation) {
                    $relation->select(["id", "trade_no"]);
                }
            ]);
        });

        return $this->json(data: $data);
    }

    /**
     * @param int $commodityId
     * @return array
     * @throws JSONException
     */
    public function sku(int $commodityId): array
    {
        $commodity = \App\Model\Commodity::query()->find($commodityId);
        if (!$commodity) {
            throw new JSONException("商品不存在");
        }

        $config = Ini::toArray($commodity->config ?: "");

        return $this->json(data: $config);
    }


    /**
     * @param Request $request
     * @return array
     * @throws JSONException
     */
    public function save(Request $request): array
    {
        $commodityId = $request->post("commodity_id", Filter::INTEGER);
        $raceGetMode = $request->post("race_get_mode", Filter::INTEGER);
        $race = $raceGetMode == 1 ? $request->post("race_input", Filter::NORMAL) : $request->post("race", Filter::NORMAL);
        $sku = $request->post("sku", Filter::NORMAL) ?: [];
        $cardType = $request->post("card_type", Filter::INTEGER);

        if ($commodityId == 0) {
            throw new JSONException('(`･ω･´)请选择商品');
        }

        $cards = trim(trim((string)$request->post("secret", Filter::NORMAL)), PHP_EOL);

        //进行批量插入
        if ($cards == '') {
            throw new JSONException('(`･ω･´)请至少添加1条卡密信息哦');
        }

        $cards = explode(PHP_EOL, $cards);
        $count = count($cards);

        $success = 0;
        $error = 0;
        $date = Date::current();

        $unique = (bool)$_POST['unique'];

        foreach ($cards as $card) {
            $cardt = trim(trim($card), PHP_EOL);
            if ($cardt == "") {
                $error++; //error ++
                continue;
            }

            $cardObj = new \App\Model\Card();

            if ($cardType == 0) {
                $cardObj->secret = $cardt;
            } else {
                // 检测是否使用新版分隔符 ---
                if (str_contains($cardt, "---")) {
                    $list = explode("---", $cardt);
                    if (count($list) < 2) {
                        $error++; //error ++
                        continue;
                    }
                    // 新格式：预选信息---卡密本体---加价(可选)
                    $cardObj->draft = trim($list[0]);
                    $cardObj->secret = trim($list[1]);
                    
                    if (isset($list[2])) {
                        $cardObj->draft_premium = (float)$list[2];
                    }
                } else {
                    // 旧格式：卡密本体║预选信息║加价(可选)
                    //分割
                    $list = explode("║", $cardt);
                    if (count($list) < 2) {
                        $error++; //error ++
                        continue;
                    }
                    $cardObj->secret = trim($list[0]);

                    //预选信息
                    if (isset($list[1])) {
                        $cardObj->draft = trim($list[1]);
                    }

                    //独立加价
                    if (isset($list[2])) {
                        $cardObj->draft_premium = (float)$list[2];
                    }
                }
            }

            // 强制检测重复：无论是否开启 unique 选项，都要检测是否已售出（在订单表中存在）
            // 只要订单表中存在包含该卡密内容的记录，就视为已售出，不允许添加
            if (\App\Model\Order::query()->where('secret', 'like', "%{$cardObj->secret}%")->exists()) {
                 throw new JSONException("该卡密（{$cardObj->secret}）已经卖出去了（存在于历史订单中），无法添加重复的，请清理后才可以再次添加");
            }
            
            // 唯一性检测
            if ($unique) {
                if (\App\Model\Card::query()->where("owner", 0)->where("secret", $cardObj->secret)->exists()) {
                    throw new JSONException("卡密内容已存在（{$cardObj->secret}），请清理后重试");
                }
            }

            $cardObj->commodity_id = $commodityId;
            $cardObj->owner = 0;
            if (isset($_POST['note'])) {
                $cardObj->note = $_POST['note'];
            }
            $cardObj->status = 0;


            $cardObj->sku = $sku;
            $cardObj->create_time = $date;

            if ($race) {
                $cardObj->race = $race;
            }

            try {
                $cardObj->save();
                $success++;
            } catch (\Exception $e) {
                $error++; //error ++
            }
        }


        ManageLog::log($this->getManage(), "[导入卡密]共计导入:{$count}张卡密，成功:{$success}张，失败：{$error}张");
        return $this->json(200, "共计导入:{$count}张卡密，成功:{$success}张，失败：{$error}张");
    }

    /**
     * @return array
     * @throws JSONException
     */
    public function edit(): array
    {
        $map = $_POST;
        $save = new Save(\App\Model\Card::class);
        $save->setMap($map);
        $save = $this->query->save($save);
        if (!$save) {
            throw new JSONException("保存失败");
        }
        ManageLog::log($this->getManage(), "[修改卡密]编辑了卡密信息");
        return $this->json(200, '（＾∀＾）保存成功');
    }

    /**
     * @return array
     */
    public function lock(): array
    {
        $list = (array)$_POST['list'];
        \App\Model\Card::query()->whereIn('id', $list)->whereRaw("status!=1")->update(['status' => 2]);

        ManageLog::log($this->getManage(), "[锁定卡密]批量锁定了卡密信息，共计：" . count($list));
        return $this->json(200, '锁定成功');
    }

    /**
     * @return array
     */
    public function unlock(): array
    {
        $list = (array)$_POST['list'];
        \App\Model\Card::query()->whereIn('id', $list)->whereRaw("status!=1")->update(['status' => 0]);
        ManageLog::log($this->getManage(), "[解锁卡密]批量解锁了卡密信息，共计：" . count($list));
        return $this->json(200, '解锁成功');
    }

    /**
     * @return array
     */
    public function sell(): array
    {
        $list = (array)$_POST['list'];
        \App\Model\Card::query()->whereIn('id', $list)->whereRaw("status!=1")->update(['status' => 1, 'purchase_time' => Date::current()]);
        ManageLog::log($this->getManage(), "[出售卡密]手动出售卡密信息，共计：" . count($list));
        return $this->json(200, '操作成功');
    }

    /**
     * @return array
     * @throws JSONException
     */
    public function unsold(): array
    {
        $list = (array)$_POST['list'];

        // 检查是否有已出售的卡密
        $soldCards = \App\Model\Card::query()->whereIn('id', $list)->where('status', 1)->get();
        if ($soldCards->isNotEmpty()) {
            foreach ($soldCards as $card) {
                // 检查该卡密是否关联了订单
                if ($card->order_id) {
                     // 再次确认订单是否存在于订单表中
                    $orderExists = \App\Model\Order::query()->where('id', $card->order_id)->exists();
                    if ($orderExists) {
                         throw new JSONException("卡密【{$card->secret}】已关联订单，无法直接恢复为未出售状态，请先删除对应订单或取消关联。");
                    }
                }
            }
        }

        // 恢复为未出售状态：status=0, 清空出售时间, 清空关联订单ID
        \App\Model\Card::query()->whereIn('id', $list)->update([
            'status' => 0,
            'purchase_time' => null,
            'order_id' => null
        ]);
        ManageLog::log($this->getManage(), "[恢复卡密]手动恢复卡密为未出售状态，共计：" . count($list));
        return $this->json(200, '操作成功');
    }

    /**
     * @return array
     * @throws JSONException
     */
    public function del(): array
    {
        $del = new Delete(\App\Model\Card::class, $_POST['list']);
        $count = $this->query->delete($del);
        if ($count == 0) {
            throw new JSONException("没有移除任何数据");
        }

        ManageLog::log($this->getManage(), "[批量删除]批量删除了卡密，共计：" . count($_POST['list']));
        return $this->json(200, '（＾∀＾）移除成功');
    }


    /**
     * 导出
     * @return string
     */
    public function export(): string
    {
        $map = $_GET;
        $exportStatus = $map['export_status'];
        $exportNum = (int)$map['export_num'];
        $note = $map['note'] ?: null;

        unset($map['export_status']);
        unset($map['export_num']);


        $get = new Get(\App\Model\Card::class);
        $get->setWhere($map);

        if ($exportNum > 0) {
            $get->setPaginate(1, $exportNum);
            $data = $this->query->get($get);
        } else {
            $data = $this->query->get($get);
        }

        $card = '';
        $ids = [];
        foreach ($data['list'] as $d) {
            $card .= $d['secret'] . PHP_EOL;
            $ids[] = $d['id'];
        }

        if ($note) {
            \App\Model\Card::query()->whereIn('id', $ids)->update(['note' => $note]);
        }

        if ($exportStatus == 1) {
            //锁定卡密
            try {
                \App\Model\Card::query()->whereIn('id', $ids)->whereRaw("status!=1")->update(['status' => 2]);
            } catch (\Exception $e) {
            }
        } elseif ($exportStatus == 2) {
            //删除卡密
            try {
                $deleteBatchEntity = new Delete(\App\Model\Card::class, $ids);
                $this->query->delete($deleteBatchEntity);
            } catch (\Exception $e) {
            }
        } elseif ($exportStatus == 3) {
            \App\Model\Card::query()->whereIn('id', $ids)->whereRaw("status!=1")->update(['status' => 1, 'purchase_time' => Date::current()]);
        }

        ManageLog::log($this->getManage(), "[卡密导出]导出卡密，共计：" . count($data));
        header('Content-Type:application/octet-stream');
        header('Content-Transfer-Encoding:binary');
        header('Content-Disposition:attachment; filename=卡密导出(' . count($data) . ')-' . Date::current() . '.txt');
        return $card;
    }
}