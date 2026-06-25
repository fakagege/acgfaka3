<?php
declare (strict_types=1);

return [
    [
        "title" => "说明",
        "name" => "explain",
        "type" => "explain",
        "placeholder" => "<b style='color: #FF0000;'>coatard_robot，该机器人已被劫持请立即停止使用，请勿相信其发送的任何内容！！！</b>
                          </br>
                          <b style='color: #0f8409;'>使用教程：</b>
                          <p style='color: red;'>1.在Telegram上搜索机器人：BotFather，并创建机器人，不懂可百度！结束后会返回给你一串token！</p>
                          <p style='color: red'>2.在Telegram上搜索机器人：getmyid_bot，并点击start按钮进入对话，此时机器人会发送给你一串数字，就是下面的UserId。</p>",
    ],
    [
        "title" => "Bot Token",
        "name" => "token",
        "type" => "input",
        "placeholder" => "您创建机器人的token"
    ], 
    [
        "title" => "Telegram UserId",
        "name" => "userId",
        "type" => "input",
        "placeholder" => "您的Telegram用户ID"
    ],
    [
        "title" => "下单话术",
        "name" => "trade_content",
        "type" => "textarea",
        "height" => 200,
        "placeholder" => "请输入下单后发给你TG的话术",
        "default" => "<b>【下单通知】您的店铺有人正在下单!</b>
-----------------------------
<b>商品名称</b>：[commodity.name] * [order.card_num]
<b>订单号</b>：<code>[order.trade_no]</code>
<b>下单时间</b>：[order.create_time]
<b>IP地址</b>：<code>[order.create_ip]</code>
<b>联系方式</b>：<code>[order.contact]</code>
<b>支付方式</b>：[pay.name]
<b>支付金额</b>：<code>[order.amount]</code>"
    ],
    [
        "title" => "下单通知",
        "name" => "trade",
        "type" => "switch",
        "text" => "启用"
    ],
    [
        "title" => "付款话术",
        "name" => "payment_content",
        "type" => "textarea",
        "placeholder" => "请输入付款后发给你TG的话术",
        "height" => 200,
        "default" => "<b>【付款成功】有客户已经付款成功啦!</b>
-----------------------------
<b>商品名称</b>：[commodity.name] * [order.card_num]
<b>订单号</b>：<code>[order.trade_no]</code>
<b>支付时间</b>：[order.pay_time]
<b>支付方式</b>：[pay.name]
<b>支付金额</b>：<code>[order.amount]</code>"
    ],
    [
        "title" => "付款通知",
        "name" => "payment",
        "type" => "switch",
        "text" => "启用"
    ]
];