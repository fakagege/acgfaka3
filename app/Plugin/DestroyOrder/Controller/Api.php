<?php
declare(strict_types=1);

namespace App\Plugin\DestroyOrder\Controller;


use App\Controller\Base\API\ManagePlugin;
use App\Entity\DeleteBatchEntity;
use App\Interceptor\ManageSession;
use App\Interceptor\Waf;
use App\Service\Query;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Exception\JSONException;

#[Interceptor([Waf::class, ManageSession::class], Interceptor::TYPE_API)]
class Api extends ManagePlugin
{

    #[Inject]
    private Query $query;

    /**
     * @return array
     * @throws JSONException
     */
    public function del(): array
    {
        $deleteBatchEntity = new DeleteBatchEntity();
        $deleteBatchEntity->setModel(\App\Model\Order::class);
        $deleteBatchEntity->setList($_POST['list']);
        $count = $this->query->deleteTemplate($deleteBatchEntity);
        if ($count == 0) {
            throw new JSONException("没有销毁任何数据");
        }
        return $this->json(200, '（＾∀＾）订单全部销毁成功');
    }
}