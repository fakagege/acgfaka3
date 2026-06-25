<?php
declare(strict_types=1);

return array (
  'STATUS' => '0',
  'token' => 'He3xNDOGM',
  'userId' => '5238',
  'trade_content' => '<b>【下单通知】您的店铺有人正在下单!</b>
-----------------------------
<b>商品名称</b>：[commodity.name] * [order.card_num]
<b>订单号</b>：<code>[order.trade_no]</code>
<b>下单时间</b>：[order.create_time]
<b>IP地址</b>：<code>[order.create_ip]</code>
<b>联系方式</b>：<code>[order.contact]</code>
<b>支付方式</b>：[pay.name]
<b>支付金额</b>：<code>[order.amount]</code>',
  'trade' => '0',
  'payment_content' => '<b>【崽崽小店】有客户已经付款成功啦!</b>
<b>商品名称</b>：[commodity.name] * [order.card_num]
<b>订单号</b>：<code>[order.trade_no]</code>
<b>下单联系方式</b>：<code>[order.contact]</code>
<b>下单时间</b>：[order.pay_time]
<b>支付方式</b>：[pay.name]
<b>支付金额</b>：[order.amount] CNY

<b>会员用户账号</b>：<code>[user.username]</code>
<b>会员余额账号</b>：[user.balance] CNY',
  'payment' => '1',
);