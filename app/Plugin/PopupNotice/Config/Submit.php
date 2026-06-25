<?php
declare (strict_types=1);

return [
    [
        "title" => "头像",
        "name" => "avatar",
        "type" => "image",
        "placeholder" => "请上传头像(正方形)"
    ],
    [
        "title" => "呢称/标题",
        "name" => "nickname",
        "type" => "input",
        "placeholder" => "请输入呢称或者公告标题"
    ],
    [
        "title" => "生命周期",
        "name" => "life",
        "type" => "select",
        "dict" => [
            ["id" => 0, "name" => "每次进入或刷新网页都弹"],
            ["id" => 1, "name" => "重启浏览器后会弹第二次"],
            ["id" => 2, "name" => "一生只弹一次"],
        ],
        "default" => 0,
        "placeholder" => "请选择"
    ],
    [
        "title" => "夜间模式",
        "name" => "darkmode",
        "type" => "select",
        "dict" => [
            ["id" => 0, "name" => "禁用"],
            ["id" => 1, "name" => "跟随系统"],
            ["id" => 2, "name" => "根据时间自动切换"],
            ["id" => 3, "name" => "常开夜间模式"],
        ],
        "default" => 2,
        "placeholder" => "请选择"
    ],
    [
        "title" => "弹窗内容",
        "name" => "message",
        "type" => "editor",
        "placeholder" => "请输入弹窗内容"
    ],
    [
        "title" => "窗口宽度(自适应)",
        "name" => "width",
        "type" => "input",
        "placeholder" => "请输入弹窗的宽度",
        "default" => "720px"
    ],
    [
        "title" => "帮助链接",
        "name" => "link",
        "type" => "textarea",
        "placeholder" => "请输入链接，一行一个，如：GitHub|https://github.com/lizhipay/acg-faka|primary",
        "default" => "GitHub|https://github.com/lizhipay/acg-faka|primary\nGitee|https://gitee.com/lizhipay/acg-faka|success"
    ],
    [
        "title" => "链接说明",
        "name" => "explain",
        "type" => "explain",
        "placeholder" => "帮助链接是一行一个，格式是：【名字|链接地址|颜色代码】\n这里面颜色代码分为：default、primary、secondary、success、danger、waring、info、light、dark、link",
    ],
];