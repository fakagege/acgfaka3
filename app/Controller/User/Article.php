<?php
declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\Base\View\User;
use App\Interceptor\UserVisitor;
use App\Interceptor\Waf;
use App\Model\Article as ArticleModel;
use Kernel\Annotation\Interceptor;
use Kernel\Exception\NotFoundException;

/**
 * Class Article
 * @package App\Controller\User
 */
#[Interceptor([Waf::class, UserVisitor::class])]
class Article extends User
{
    /**
     * Article List
     */
    public function index(): string
    {
        $articles = ArticleModel::query()
            ->where('status', 1)
            ->orderBy('sort', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);
        
        $data = $articles->toArray();

        // Process summaries for all themes (truncate to 20 chars)
        foreach ($data['data'] as &$item) {
            $source = !empty($item['summary']) ? $item['summary'] : $item['content'];
            $text = strip_tags($source);
            $text = str_replace(["\r", "\n", "&nbsp;"], "", $text);
            $item['summary'] = mb_substr($text, 0, 50, 'utf-8') . '...';
        }
        unset($item); // Break reference

        // Generate pagination links
        try {
            $links = $articles->links('pagination::bootstrap-4');
            $data['links'] = (string)$links;
        } catch (\Throwable $e) {
            // Fallback: manually build simple pagination
            $data['links'] = $this->buildSimplePagination($articles);
        }

        return $this->theme("文章列表", "Article/Index", "Article/Index.html", [
            'articles' => $data,
            'keywords' => '文章资讯, 教程, 动态, 新闻列表',
            'description' => '浏览我们最新的文章资讯、教程和平台动态。'
        ]);
    }

    /**
     * Manual pagination builder
     */
    private function buildSimplePagination($paginator): string
    {
        if ($paginator->lastPage() <= 1) {
            return '';
        }

        $html = '<div class="layui-box layui-laypage layui-laypage-default">';
        
        // Previous Page
        if ($paginator->onFirstPage()) {
            $html .= '<a href="javascript:;" class="layui-laypage-prev layui-disabled">上一页</a>';
        } else {
            $html .= '<a href="' . $paginator->previousPageUrl() . '" class="layui-laypage-prev">上一页</a>';
        }

        // Page Numbers
        $start = max(1, $paginator->currentPage() - 2);
        $end = min($paginator->lastPage(), $paginator->currentPage() + 2);

        if ($start > 1) {
             $html .= '<a href="' . $paginator->url(1) . '">1</a>';
             if ($start > 2) $html .= '<span class="layui-laypage-spr">…</span>';
        }

        for ($i = $start; $i <= $end; $i++) {
            if ($i == $paginator->currentPage()) {
                $html .= '<span class="layui-laypage-curr"><em class="layui-laypage-em"></em><em>' . $i . '</em></span>';
            } else {
                $html .= '<a href="' . $paginator->url($i) . '">' . $i . '</a>';
            }
        }

        if ($end < $paginator->lastPage()) {
            if ($end < $paginator->lastPage() - 1) $html .= '<span class="layui-laypage-spr">…</span>';
            $html .= '<a href="' . $paginator->url($paginator->lastPage()) . '">' . $paginator->lastPage() . '</a>';
        }

        // Next Page
        if ($paginator->hasMorePages()) {
            $html .= '<a href="' . $paginator->nextPageUrl() . '" class="layui-laypage-next">下一页</a>';
        } else {
            $html .= '<a href="javascript:;" class="layui-laypage-next layui-disabled">下一页</a>';
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Article Detail
     */
    public function detail(): string
    {
        $id = (int)$_GET['id'];
        $article = ArticleModel::query()->where('id', $id)->where('status', 1)->first();
        if (!$article) {
            throw new NotFoundException("文章不存在");
        }

        // Increment views
        $article->increment('views');

        // SEO Logic
        $description = $article->summary;
        if (empty($description)) {
             $text = strip_tags($article->content);
             $text = str_replace(["\r", "\n", "&nbsp;"], "", $text);
             $description = mb_substr($text, 0, 150, 'utf-8');
        }

        return $this->theme($article->title, "Article/Detail", "Article/Detail.html", [
            'article' => $article,
            'keywords' => $article->title,
            'description' => $description
        ]);
    }
}
