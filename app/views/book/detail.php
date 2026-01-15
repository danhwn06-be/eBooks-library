<?php require APP_ROOT . "/app/views/inc/header.php"; ?>

<div class="container main-container">

    <div class="breadcrumb">
        <a href="<?php echo URL_ROOT; ?>">Home</a>
        <a href="<?php echo URL_ROOT; ?>book/">Books</a>
        <span><?php echo $data['book']['title']; ?></span>
    </div>

    <div class="book-detail-container">

        <div class="detail left">
            <div class="detail-image-wrapper">
                <img src="<?php echo URL_ROOT . '/images/books/' . ($data['book']['image_url'] ? $data['book']['image_url'] : 'default-book.jpg'); ?>"
                    alt="<?php echo $data['book']['title']; ?>">
            </div>
        </div>

        <div class="detail-right">
            <h1 class="detail-title"><?php echo $data['book']['title']; ?></h1>

            <div class="detail-meta">
                <p class="detail-author">by <strong><?php echo $data['book']['author']; ?></strong></p>
                <p class="detail-category"><i class="fa-solid fa-tag"></i> <?php echo $data['book']['category_name']; ?></p>
            </div>

            <div class="detail-status">
                <?php if ($data['book']['available_copies'] > 0): ?>
                    <span class="badge-available"><i class="fa-solid fa-check"></i> Available</span>
                <?php else: ?>
                    <span class="badge-out"><i class="fa-solid fa-xmark"></i> Out of stock</span>
                <?php endif; ?>
                <span class="stock-count">(<?php echo $data['book']['available_copies']; ?> of <?php echo $data['book']['total_copies']; ?> copies available)</span>
            </div>

            <div class="detail-info-grid">
                <div class="info-item">
                    <span class="label">Publisher:</span>
                    <span class="value"><?php echo htmlspecialchars($data['book']['publisher']); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Year:</span>
                    <span class="value"><?php echo htmlspecialchars($data['book']['publication_year']); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">ISBN:</span>
                    <span class="value"><?php echo htmlspecialchars($data['book']['isbn']); ?></span>
                </div>
            </div>

            <div class="detail-actions">
                <?php if ($data['book']['available_copies'] > 0): ?>
                    <!-- <a href="<?php //echo URL_ROOT; ?>/book/borrow/<?php //echo $data['book']['book_id']; ?>" class="btn btn-borrow">Borrow Now</a> -->
                    <a href="<?php echo URL_ROOT; ?>/book/reserve/<?php echo $data['book']['book_id']; ?>" class="btn btn-reserve">Reserve</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="detail-description">
            <h3>Description</h3>
            <p><?php echo nl2br($data['book']['description']); ?></p>
        </div>
    </div>
</div>

<?php require APP_ROOT . "/app/views/inc/footer.php"; ?>