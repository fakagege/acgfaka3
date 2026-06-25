<?php
declare(strict_types=1);

namespace App\View\User\Theme\Toka;

use App\Consts\Render;

interface Config
{
    const INFO = [
        "NAME" => "十香",
        "AUTHOR" => "荔枝",
        "VERSION" => "1.0.5",
        "WEB_SITE" => "#",
        "DESCRIPTION" => "真正意义上的大排面，专为数千分类，数万商品打造的大排面模板",
        "RENDER" => Render::ENGINE_PHP
    ];

    /**
     * 配置信息
     */
    const SUBMIT = [
        [
            "title" => "ICP备案号",
            "name" => "icp",
            "type" => "input",
            "placeholder" => "填写后将会在店铺底部显示ICP备案号，不填写则不显示。"
        ],
        [
            "title" => "缓存",
            "name" => "cache",
            "type" => "switch",
            "text" => "开启",
            "tips" => "浏览器本地缓存，缓存时间60秒"
        ],
        [
            "title" => "缓存时间",
            "name" => "cache_expire",
            "type" => "input",
            "placeholder" => "缓存过期时间，推荐60秒",
            "default" => 60
        ]
    ];

    /**
     * 主题配置
     */
    const THEME = [
        "INDEX" => "Index/Index.php",
        "ITEM" => "Index/Index.php",
        "Article/Index" => "Article/Index.php",
        "Article/Detail" => "Article/Detail.php"
    ];
}