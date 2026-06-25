<?php require(dirname(__DIR__) . '/Index/Header.php'); ?>
<style>
    .article-content img {
        max-width: 100%;
        height: auto;
    }
</style>
<div class="content-wrapper">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                         <p class="card-title">
                             <a href="/user/article/index" class="text-muted"><i class="fa fa-chevron-left"></i> 返回列表</a>
                         </p>
                    </div>
                    <div class="card-block p-4">
                        <h1 class="text-center mb-4"><?php echo htmlspecialchars($data['article']->title); ?></h1>
                        <div class="text-center text-muted mb-4">
                            <span><i class="fa fa-clock-o"></i> <?php echo $data['article']->create_time; ?></span>
                            <span class="ml-3"><i class="fa fa-eye"></i> <?php echo $data['article']->views; ?></span>
                        </div>
                        <hr>
                        <div class="article-content">
                            <?php echo $data['article']->content; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require(dirname(__DIR__) . '/Index/Footer.php'); ?>
