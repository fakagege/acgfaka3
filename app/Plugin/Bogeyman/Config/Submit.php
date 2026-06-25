<?php
declare (strict_types=1);

return [
    [
        "title" => "IP列表",
        "name" => "ip",
        "type" => "textarea",
        "placeholder" => "封锁的IP列表，一行一个"
    ],
    [
        "title" => "联系方式列表",
        "name" => "contact",
        "type" => "textarea",
        "placeholder" => "封锁的联系方式列表，一行一个"
    ],
    [
        "title" => "封锁提示语",
        "name" => "msg",
        "type" => "textarea",
        "placeholder" => "下单时，给对方提示的信息",
        "default" => "您已被封锁，请联系客服。"
    ]
];