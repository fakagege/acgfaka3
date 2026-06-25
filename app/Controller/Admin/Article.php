<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Base\View\Manage;
use App\Interceptor\ManageSession;
use Kernel\Annotation\Interceptor;

/**
 * Class Article
 * @package App\Controller\Admin
 */
#[Interceptor(ManageSession::class)]
class Article extends Manage
{
    /**
     * @throws \Kernel\Exception\ViewException
     */
    public function index(): string
    {
        return $this->render("文章管理", "Article/Index.html");
    }
}
