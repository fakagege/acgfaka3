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
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Request;
use Kernel\Exception\JSONException;
use Kernel\Exception\NotFoundException;
use Kernel\Exception\RuntimeException;
use Kernel\Waf\Filter;

/**
 * Class Article
 * @package App\Controller\Admin\Api
 */
#[Interceptor(ManageSession::class, Interceptor::TYPE_API)]
class Article extends Manage
{
    #[Inject]
    private Query $query;

    /**
     * @return array
     */
    public function data(): array
    {
        $map = $_POST;
        $get = new Get(\App\Model\Article::class);
        $get->setWhere($map);
        $get->setOrderBy(...$this->query->getOrderBy($map, "sort", "desc"));
        $data = $this->query->get($get);
        return $this->json(data: $data);
    }

    /**
     * @param Request $request
     * @return array
     * @throws JSONException
     * @throws NotFoundException
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function save(Request $request): array
    {
        $map = $request->post(flags: Filter::NORMAL);
        
        // Use unsafePost for content to allow HTML only if provided
        // When updating status via toggle, content might not be sent, so we don't overwrite it
        $content = $request->unsafePost('content');
        if ($content !== null) {
            $map['content'] = $content;
        }

        // Auto-generate summary if empty and content is present
        if (empty($map['summary']) && !empty($map['content'])) {
            $text = strip_tags($map['content']);
            $text = str_replace(["\r", "\n", "&nbsp;"], "", $text);
            $map['summary'] = mb_substr($text, 0, 150, 'utf-8') . '...';
        }

        $save = new Save(\App\Model\Article::class);
        $save->setMap($map);
        $save->enableCreateTime();
        
        // Dynamically set whitelist based on input data
        $whitelist = ['status', 'sort']; // Always allow these
        if (isset($map['title'])) $whitelist[] = 'title';
        if (isset($map['content'])) $whitelist[] = 'content';
        if (isset($map['cover'])) $whitelist[] = 'cover';
        if (isset($map['summary'])) $whitelist[] = 'summary';
        
        $save->addWhitelist = $whitelist;
        $save->modifiableWhitelist = $whitelist;
        
        $save = $this->query->save($save);
        if (!$save) {
            throw new JSONException("保存失败，请检查信息填写是否完整");
        }

        ManageLog::log($this->getManage(), "[新增/修改]文章");
        return $this->json(200, '（＾∀＾）保存成功');
    }

    /**
     * @return array
     * @throws JSONException
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function del(): array
    {
        $list = (array)$_POST['list'];
        $del = new Delete(\App\Model\Article::class, $list);
        $this->query->delete($del);
        ManageLog::log($this->getManage(), "[删除]文章");
        return $this->json(200, '（＾∀＾）删除成功');
    }
}
