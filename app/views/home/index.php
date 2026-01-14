<?php require APP_ROOT . '/app/views/inc/header.php'; ?>
<?php require APP_ROOT . '/app/views/inc/carousel.php'; ?>

<section class="filter-section">
    <div class="container">
        <form action="<?php echo URL_ROOT; ?>/books/filter" method="GET" class="filter-form">
            <div class="form-group">
                <select name="category" id="category">
                    <option value="">All genres</option>
                    <?php if (!empty($data['categories'])): ?>
                        <?php foreach ($data['categories'] as $cat): ?>
                            <option value="<?php echo $cat['category_id']; ?>">
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-group">
                <input type="text" placeholder="Enter the year of publication...">
            </div>
            <div class="form-group full-width-group">
                <input type="text" placeholder="Enter author's name...">
            </div>
            <div class="form-group submit-group"></div>
            <button type="submit" class="btn-confirm">Confirm</button>
        </form>
    </div>
</section>

<main class="book-list-section">
    <div class="container">
        <h2 class="section-title">All the books in the library</h2>

        <div class="book-grid">
            <?php if (empty($data['books'])): ?>
                <p>No books found.</p>
            <?php else: ?>
                <?php foreach ($data['books'] as $book): ?>
                    <div class="book-card">
                        <div class="card-header">
                            <?php if ($book['available_copies'] > 0): ?>
                                <span class="badge-available">Available</span>
                            <?php else: ?>
                                <span class="badge-available" style="background: #e74c3c">Out of stock</span>
                            <?php endif; ?>
                        </div>

                        <div class="book-cover-placeholder">
                            <img src="<?php echo !empty($book['image_url']) ? URL_ROOT . '/images/' . htmlspecialchars($book['image_url']) : URL_ROOT . '/images/default-book.jpg'; ?>"
                                alt="<?php echo htmlspecialchars($book['title']); ?>">
                        </div>

                        <div class="card-body">
                            <h3 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                            <p class="book-author"><?php echo htmlspecialchars($book['author']); ?></p>
                            <p class="book-isbn">ISBN: <?php echo htmlspecialchars($book['isbn']); ?></p>
                        </div>

                        <div class="book-meta">
                            <span><?php echo $book['available_copies']; ?> versions available</span>
                            <span>/<?php echo $book['total_copies']; ?> copies</span>
                        </div>

                        <div class="card-footer">
                            <a href="<?php echo URL_ROOT; ?>/books/detail/<?php echo $book['book_id']; ?>" class="btn-details">See details</a>

                            <?php if ($book['available_copies'] > 0): ?>
                                <a href="<?php echo URL_ROOT; ?>/books/reserve/<?php echo $book['book_id']; ?>" class="btn-reservation">Reservations</a>
                            <?php else: ?>
                                <button class="btn-reservation" disabled style="opacity: 0.5; cursor: not-allowed;">Unavailable</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="pagination-container">
            <?php
            $current = $data['pagination']['current_page'];
            $total = $data['pagination']['total_pages'];
            ?>

            <?php if ($current > 1): ?>
                <a href="?page=<?php echo $current - 1; ?>" class="page-btn prev"><i class="fa-solid fa-chevron-left"></i></a>
            <?php else: ?>
                <span class="page-btn prev disabled"><i class="fa-solid fa-chevron-left"></i></span>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="page-btn <?php echo ($i == $current) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($current < $total): ?>
                <a href="?page=<?php echo $current + 1; ?>" class="page-btn next"><i class="fa-solid fa-chevron-right"></i></a>
            <?php else: ?>
                <span class="page-btn next disabled"><i class="fa-solid fa-chevron-right"></i></span>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require APP_ROOT . "/app/views/inc/footer.php"; ?>