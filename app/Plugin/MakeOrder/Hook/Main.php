<?php
declare(strict_types=1);

namespace App\Plugin\MakeOrder\Hook;


use App\Controller\Base\View\ManagePlugin;
use Kernel\Annotation\Hook;
use Kernel\Exception\ViewException;

class Main extends ManagePlugin
{

    /**
     * @throws ViewException
     */
    #[Hook(point: \App\Consts\Hook::ADMIN_VIEW_ORDER_TABLE)]
    public function aide()
    {
        echo $this->render(null, "Aide.hook");
    }

}