<?php require(dirname(__DIR__) . '/Index/Header.php'); ?>
<div class="content-wrapper">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <p class="card-title"><i class="fa fa-newspaper-o" aria-hidden="true"></i> 文章列表</p>
                    </div>
                    <div class="card-block p-4">
                        <div class="article-list">
                            <?php if (count($data['articles']['data']) > 0): ?>
                                <?php foreach ($data['articles']['data'] as $article): ?>
                                    <a href="/user/article/detail?id=<?php echo $article['id']; ?>" class="d-block text-decoration-none mb-4 pb-4 border-bottom" style="border-color: #f8f9fa !important;">
                                        <div class="media">
                                            <img src="<?php echo $article['cover'] ?: '/favicon.ico'; ?>" class="mr-3 rounded" alt="<?php echo htmlspecialchars($article['title']); ?>" style="width: 48px; height: 48px; object-fit: cover;">
                                            <div class="media-body">
                                                <div class="d-flex w-100 justify-content-between align-items-center">
                                                    <h5 class="mt-0 mb-1 text-dark" style="font-size: 1.1rem; font-weight: bold;"><?php echo htmlspecialchars($article['title']); ?></h5>
                                                    <small class="text-muted"><i class="fa fa-clock-o"></i> <?php echo date('Y-m-d', strtotime($article['create_time'])); ?></small>
                                                </div>
                                                <p class="mb-2 mt-1 text-muted" style="font-size: 0.9rem; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis;">
                                <?php echo htmlspecialchars($article['summary']); ?>
                            </p>
                                                <small class="text-muted"><i class="fa fa-eye"></i> <?php echo $article['views']; ?> 阅读</small>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center p-3">暂无文章</div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($data['articles']['last_page'] > 1): ?>
                            <nav aria-label="Page navigation" class="mt-3">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?php echo $data['articles']['current_page'] == 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="<?php echo $data['articles']['prev_page_url']; ?>">上一页</a>
                                    </li>
                                    <?php for($i=1; $i<=$data['articles']['last_page']; $i++): ?>
                                        <li class="page-item <?php echo $i == $data['articles']['current_page'] ? 'active' : ''; ?>">
                                            <a class="page-link" href="<?php echo $data['articles']['path'] . '?page=' . $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?php echo $data['articles']['current_page'] == $data['articles']['last_page'] ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="<?php echo $data['articles']['next_page_url']; ?>">下一页</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require(dirname(__DIR__) . '/Index/Footer.php'); ?>
