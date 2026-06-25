<!doctype html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="keywords" content="<?php echo $data['keywords'] ?: $data["config"]["keywords"]; ?>"/>
    <meta name="description" content="<?php echo $data['description'] ?: $data["config"]["description"]; ?>"/>
    <link href="<?php echo $data['favicon']; ?>" rel="icon">

    <?php echo css([
            "/assets/static/bootstrap/css/bootstrap.min.css",
            "/assets/static/font/font-awesome-4.7.0/css/font-awesome.min.css",
            "/assets/static/css/i.css",
            "/app/View/User/Theme/Toka/Assets/Main.css"
    ]); ?>

    <?php echo js([
            "/assets/static/jquery.min.js",
            "/assets/static/acg.js"
    ]); ?>


    <title><?php echo $data["config"]["title"]; ?></title>
    <!-- SEO Optimization -->
    <link rel="canonical" href="<?php echo \App\Util\Client::getUrl(); ?>" />
    <meta property="og:title" content="<?php echo $data["config"]["title"]; ?>" />
    <meta property="og:description" content="<?php echo $data["config"]["description"]; ?>" />
    <meta property="og:url" content="<?php echo \App\Util\Client::getUrl(); ?>" />
    <meta property="og:site_name" content="<?php echo $data["config"]["shop_name"]; ?>" />
    <meta property="og:type" content="website" />
    <!-- End SEO Optimization -->
    <!--start::HOOK-->
    <?php hook(\App\Consts\Hook::USER_VIEW_INDEX_HEADER); ?>
    <!--end::HOOK-->
    <script>
        const cache_status = parseInt("<?php echo $data['setting']['cache'];?>");
        const cache_expire = parseInt("<?php echo $data['setting']['cache_expire'];?>");
    </script>
</head>
<body style="background: url('<?php echo $data['config']['background_url']; ?>') fixed no-repeat;background-size: cover;">
<div id="app">
    <nav class="navbar navbar-expand-lg navbar-light bg-light"
         style="background-color: rgba(255,255,255,0.85) !important;">
        <div class="container">
            <div class="navbar-brand">
                <a href="<?php
                if ($data['user']) {
                    echo '/user/dashboard/index';
                } else {
                    echo '/';
                }
                ?>">
                    <img src="<?php
                    if ($data['user']['avatar']) {
                        echo $data['user']['avatar'];
                    } else {
                        echo $data['favicon'];
                    }
                    ?>" height="30px"
                         style="border-radius: 50%;box-shadow: #f0d1d4 1px 1px 1px;">
                    <?php if ($data['user']){ ?>
                    <span style="position: relative;top: 4px;left: 3px;font-weight: bold;color: #1396558a;"> <?php echo $data['user']['username']; ?>  <?php }else{ ?> <span
                                style="position: relative;top: 4px;left: 3px;font-weight: bold;color: #1396558a;">  <?php echo $data['config']['shop_name']; ?><?php } ?>
               </span></a>

                <?php if ($data['user']) { ?>
                    <a href="/user/dashboard/index"><span
                                style="position: relative;top: 4px;left: 10px;font-size: 14px;font-weight: bold;color: #7be7bfd9;"><i
                                    class="fa fa-yen"></i><?php echo $data['user']['balance']; ?></span></a>
                <?php } else { ?> <a href="/user/authentication/login?goto=/"><span
                            style="position: relative;top: 4px;left: 10px;font-size: 18px;color: #79b9fbbd;font-weight: bold;"><i
                                class="fa fa-sign-in"></i> 登录</span></a> <?php } ?>
            </div>
            <div class="row">
                <a class="nav-link" href="/" style="font-weight: bolder;"><i class="fa fa-shopping-cart"
                                                                             aria-hidden="true"></i> 购物</a>
                <a class="nav-link" href="/user/index/query" style="font-weight: bolder;"><i class="fa fa-search-plus"
                                                                                             aria-hidden="true"></i> 查单</a>
                <a class="nav-link" href="/user/article/index" style="font-weight: bolder;"><i class="fa fa-newspaper-o"
                                                                                             aria-hidden="true"></i> 文章</a>
                <?php if ($data['config']['service_url']) { ?><a class="nav-link"
                                                                 href="<?php echo $data['config']['service_url']; ?>"
                                                                 target="_blank"
                                                                 style="font-weight: bolder;"><i class="fa fa-twitch"
                                                                                                 aria-hidden="true"></i>
                        客服</a> <?php } ?>

                <?php foreach (hook(\App\Consts\Hook::USER_VIEW_HEADER_NAV) as $item) { ?>
                    <a class="nav-link" href="<?php echo $item['url']; ?>" target="<?php echo $item['target']; ?>"
                       style="font-weight: bolder;"><i class="<?php echo $item['icon']; ?>"
                                                       aria-hidden="true"></i> <?php echo $item['name']; ?></a>
                <?php } ?>
            </div>
            <!--<div class="navbar-collapse">
                <ul class="navbar-nav mr-auto"></ul>
            </div>-->
        </div>
    </nav>