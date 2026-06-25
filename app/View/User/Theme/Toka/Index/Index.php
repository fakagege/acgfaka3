<?php require('Header.php'); ?>
    <div class="content-wrapper">

        <div class="container">

            <div class="row">
                <!--            公告部分-->
                <div class="col-12 notice-html">
                    <div class="card">
                        <div class="card-header">
                            <p class="card-title"><i class="fa fa-bullhorn" aria-hidden="true"></i> 公告</p>
                        </div>
                        <div class="card-block">
                            <?php echo $data['config']['notice']; ?>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <p class="card-title"><i class="fa fa-search" aria-hidden="true"></i> 搜索商品</p>
                        </div>
                        <div class="card-block" style="padding-top: 10px;padding-bottom: 10px">
                            <fieldset>
                                <div class="input-group">
                                    <input type="text" class="form-control commodity-search"
                                           placeholder="请输入商品关键词.."
                                           autocomplete="off">
                                    <div class="input-group-append">
                                        <button class="btn btn-success search-btn">查询</button>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </div>

                <div class="col-3 col-xs-12">
                    <div class="card">
                        <div class="card-header">
                            <p class="card-title"><i class="fa fa-eercast" aria-hidden="true"></i> 商品分类</p>
                        </div>
                        <div class="card-block category-list">
                            <table class="layui-table">
                                <tbody class="category-items"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-9 col-xs-12 shop-html">
                    <div class="card">
                        <div class="card-header">
                            <p class="card-title"><i class="fa fa-bookmark" aria-hidden="true"></i> 选择商品</p>
                        </div>
                        <div class="card-block shop-content">
                            <div class="keguan"
                                 style="color:#c2c9ed;width: 100%;text-align: center;margin-top: 50px;">
                                (✿◡‿◡) 客官，请选择一个分类吧~
                            </div>
                            <table class="layui-table">
                                <tbody class="shop-list">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


            </div>

        </div>
    </div>
    </div>


    <div class="open-commodity" style="display: none;">
        <div class="layout commodity-di">
            <div class="layout-content __html">
                <form class="commodity-form">
                    <p class="commodity_name"></p>
                    <p class="share_url"><i class="fa fa-share"></i> 将宝贝分享给好友</p>
                    <p class="description"></p>
                    <p class="seckill general">限时秒杀：<span class="seckill_timer"></span></p>
                    <p class="general"><span class="price">0</span></p>
                    <p class="general">发货方式：<span class="delivery_way"></span><span class="stock">库存: ...</span>
                    </p>
                    <p class="general race-view">宝贝类型：<span></span></p>
                    <p class="general sku-view"></p>
                    <p class="general">联系方式：<input class="acg-input contact" type="text" name="contact"
                                                       placeholder="请输入联系方式">
                    </p>
                    <p class="password general">查询密码：<input class="acg-input" type="text" name="password"
                                                                placeholder="请设置查询密码">
                    </p>
                    <p class="widget general"></p>
                    <p class="coupon general">优惠代卷：<input class="acg-input" type="text" name="coupon"
                                                              placeholder="没有可不填写"
                                                              onchange="acg.API.tradeAmountPerform('.trade_amount')">
                    </p>
                    <p class="general">购买数量：<input class="acg-input purchase_num" type="number" name="num" value="1"
                                                       onchange="acg.API.tradeAmountPerform('.trade_amount')"> <span
                                class="kucun">库存：<span class="card_count">0</span></span></p>
                    <p class="general captcha_status">人机验证：<input class="acg-input captcha-input" name="captcha"
                                                                      type="text"
                                                                      placeholder="请输入验证码"> <img
                                class="captcha"></p>
                    <p class="purchase_count general"></p>
                    <p class="general">售前客服：<a target="_blank" class="qq-service"><i class="fa fa-telegram"></i>
                            联系客服</a><a
                                target="_blank"
                                class="web-service"><i
                                    class="fa fa-user-plus"></i> 网页客服</a></p>
                    <p class="lot"></p>
                    <p class="draft_status"></p>
                </form>
            </div>
        </div>
        <div class="layout pay-content">
            <label><i class="fa fa-shopping-cart"></i> 付款</label>
            <div class="pay_list">
            </div>
        </div>
    </div>

    <script>
        acg.ready("<?php echo $data['from'];?>", () => {
            let __html = $('.__html').html();
            let __htmlInit = () => {
                $('.commodity-di').show();
                $('.__html').html(__html);
            }
            let __htmlUnload = () => {
                $('.commodity-di').hide();
                $('.__html').html("");
            }

            __htmlUnload();

            let defaultCommodity = "<?php echo $data["commodityId"];?>";
            let defaultCategory = "<?php echo $data["categoryId"];?>";


            function inventoryHidden(state, count) {
                if (state == 0) {
                    return count;
                }
                if (count <= 0) {
                    return '已售罄';
                } else if (count <= 5) {
                    return '马上卖完';
                } else if (count <= 20) {
                    return '一般';
                } else if (count > 20) {
                    return '充足';
                }
            }

            let buyButton = function (item) {
                if (item.delivery_way == 0 && !item.shared && item.card_count <= 0) {
                    return '<td>-</td>';
                }
                return `<td><a data-id="` + item.id + `" href="javascript:void (0);" class="commodity-click"><i class="fa fa-shopping-cart" aria-hidden="true"></i> 购买</a></td>`;
            }

            let shopTitle = `<tr class="head">
                                    <th><i class="fa fa-ioxhost" aria-hidden="true"></i> 商品名称</th>
                                    <th><i class="fa fa-buysellads" aria-hidden="true"></i> 单价</th>
                                    <th><i class="fa fa-bolt" aria-hidden="true"></i> 库存</th>
                                    <th></th>
                                </tr>`;

            let dom = {
                pageViewExec() {
                    let height = 760;
                    let pageHeight = $('.layui-layer-page[type=page]').height();
                    let top = ($(window).height() - height) / 2;
                    if (pageHeight > height) {
                        $('.layui-layer-page[type=page]').css("top", top + "px");
                        $('.layui-layer-content').css("height", height + "px").css("overflow-y", "auto");
                    } else {
                        let top2 = ($(window).height() - pageHeight) / 2;
                        $('.layui-layer-page[type=page]').css("top", top2 + "px");
                    }
                },
                pageView() {
                    if (acg.Util.isPc()) {
                        dom.pageViewExec();
                        $('.layui-layer-page[type=page]').bind('DOMNodeInserted', function (e) {
                            dom.pageViewExec();
                        });
                        $('.layui-layer-page[type=page] img').each(function () {
                            this.onload = function () {
                                dom.pageViewExec();
                            }
                        });
                    }
                },
                commodity(commodityId) {
                    acg.API.commodity({
                        commodityId: commodityId,
                        pay: ".pay-content",
                        auto: {
                            race: '.race-view',
                            name: '.commodity_name',
                            share_url: '.share_url',
                            description: '.description',
                            delivery_way: '.delivery_way',
                            contact_type: '.contact',
                            coupon: '.coupon',
                            purchase_num: '.purchase_num',
                            captcha: '.captcha',
                            password_status: '.password',
                            lot_status: '.lot',
                            seckill_status: '.seckill',
                            card: '.card_count',
                            purchase_count: '.purchase_count',
                            price: '.price',
                            draft_status: '.draft_status',
                            widget: '.widget',
                            sku: '.sku-view'
                        },
                        begin: () => {
                            __htmlInit();
                        },
                        success: res => {
                            // 智能判断链接类型
                            let serviceUrl = res.service_url;
                            if (!serviceUrl && res.service_qq) {
                                if (/^\d+$/.test(res.service_qq)) {
                                    // 纯数字，默认为QQ
                                    serviceUrl = 'https://wpa.qq.com/msgrd?v=1&uin=' + res.service_qq;
                                } else {
                                    // 非纯数字，直接作为链接
                                    serviceUrl = res.service_qq;
                                }
                            }
                            
                            $('.qq-service').attr("href", serviceUrl);
                            $('.web-service').attr("href", res.service_url); // 保持原有逻辑，如果后台填了单独的网页客服链接
                            //layer
                            layer.open({
                                type: 1,
                                shade: [0.3, "#fff"],
                                title: acg.Util.isPc() ? false : res.name,
                                content: $('.open-commodity'),
                                area: acg.Util.isPc() ? ["620px", "680px"] : ["100%", "100%"],
                                success: () => {
                                    dom.pageView();
                                }
                            });
                        }
                    });
                },
                initCategory() {
                    acg.API.category({
                        success: function (res) {
                            if (res.commodity_count > 0) {
                                $('.category-items').append(`
                                <tr class="item">
                                <td class="category category-click-` + res.id + `"><img class="commodity-icon lazy" src="/favicon.ico" data-original="` + res.icon + `"> ` + res.name + `</td>
                                </tr>
                             `);
                                $('.category-click-' + res.id).click(function () {
                                    $('.checked').removeClass('checked');
                                    $(this).addClass('checked');
                                    dom.commoditys(res.id);
                                });
                            }
                        },
                        yes: () => {
                            <?php if (isset($data['item'])) {?>
                            $('.category-click-<?php echo $data['item']['category_id']?>').trigger("click");
                            <?php }?>

                            if (defaultCategory && defaultCategory != 0) {
                                $('.category-click-' + defaultCategory).trigger("click");
                                defaultCategory = null;
                            }
                        }
                    });
                },
                commoditys(categoryId, keywords) {
                    $('.shop-list').html(shopTitle);
                    $('.keguan').remove();
                    acg.API.commoditys({
                        keywords: keywords,
                        categoryId: categoryId,
                        success: item => {
                            $('.shop-list').append(`<tr class="item">
                                <td><img class="commodity-icon lazy" src="/favicon.ico" data-original="` + item.cover + `" onerror="this.src='/favicon.ico';this.onerror=null;"> ` + item.name + `</td>
                                <td>￥` + (item.price) + `</td>
                                <td>` + item.stock + `</td>
                                ` + buyButton(item) + `
                            </tr>`);
                        },
                        empty: () => {
                            $('.shop-html').show(150);
                            $('.shop-list').html('<div class="keguan" style="color:#f19d9d;width: 100%;text-align: center;margin-top: 50px;"> (。﹏。*) 没有找到到任何商品..</div>');
                        },
                        yes: () => {
                            $('.shop-html').show(150);

                            $('.commodity-click').click(function () {
                                let commodityId = $(this).attr("data-id");
                                dom.commodity(commodityId);
                            });

                            if (defaultCommodity && defaultCommodity != 0) {
                                $('.commodity-click' + '[data-id=' + defaultCommodity + ']').trigger("click");
                                defaultCommodity = null;
                            }

                            if (acg.Util.isMobile()) {
                                $('html,body').animate({
                                    scrollTop: 9999
                                }, 'slow');
                            }
                        }
                    });

                }
            }
            dom.initCategory();

            //初始化支付
            acg.API.pay({
                success: item => {
                    if (item.handle === "#system") {
                        <?php if ($data['user']){?>
                        $('.pay_list').append(' <a class="pay-button" onclick="acg.API.tradePerform(' + item.id + ')" style="line-height: 22px;color: #ffffff;background:#e5b9b9b0;"> <img src="' + item.icon + '"> ' + item.name + '(<?php echo sprintf("%.2f", $data['user']['balance'])?>)</a>');
                        <?php }?>
                    } else {
                        $('.pay_list').append(' <a class="pay-button" onclick="acg.API.tradePerform(' + item.id + ')" style="line-height: 22px;"><img src="' + item.icon + '"> ' + item.name + '</a>');
                    }
                }
            });

            $('.search-btn').click(() => {
                let val = $('.commodity-search').val();

                if (!val) {
                    layer.msg("请输入要搜索的商品关键词..")
                    return;
                }

                dom.commoditys(0, val);
            });

            $(".commodity-search").keypress(function (even) {
                if (even.which == 13) {
                    $('.search-btn').click();
                }
            });
        });


        /*        if (!acg.Util.isMobile()) {
                    let noticeHeight = $('.notice-html').height();
                    $('.shop-content').css("height", "calc(100vh - " + (315 + noticeHeight) + "px)");
                    $('.category-list .category-items').css("height", "calc(100vh - " + (315 + noticeHeight) + "px)");
                }*/
    </script>
<?php require('Footer.php'); ?>