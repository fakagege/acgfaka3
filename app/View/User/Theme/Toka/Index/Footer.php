<div class="content-icp"><?php echo $data['setting']['icp']; ?></div>
<!--start::HOOK-->
<?php hook(\App\Consts\Hook::USER_VIEW_INDEX_BODY); ?>
<!--end::HOOK-->
<script src="https://cdn.staticfile.org/jquery.lazyload/1.9.1/jquery.lazyload.min.js"></script>
<script>
    $(function() {
        // 定时检测是否有新图片插入，并初始化懒加载
        setInterval(function() {
            // 只对未初始化的图片进行懒加载初始化
            $("img.lazy:not(.lazy-initialized)").addClass("lazy-initialized").lazyload({
                effect : "fadeIn",
                threshold : 200 // 提前200px开始加载
            });
        }, 1000);
    });
</script>
</body>
<!--start::HOOK-->
<?php hook(\App\Consts\Hook::USER_VIEW_INDEX_FOOTER); ?>
<!--end::HOOK-->
</html>