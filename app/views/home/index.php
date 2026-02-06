<?php require APP_ROOT . '/app/views/inc/header.php'; ?>
<?php require APP_ROOT . '/app/views/inc/carousel.php'; ?>

<section class="filter-section">
    <div class="container">
        <form action="<?php echo URL_ROOT; ?>/book/index" method="GET" class="filter-form">
            <div class="form-group">
                <select name="category" id="category">
                    <option value="">All genres</option>
                    <?php if (!empty($data['categories'])): ?>
                        <?php foreach ($data['categories'] as $cat): ?>
                            <option value="<?php echo $cat['category_id']; ?>"
                                <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['category_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-group">
                <input type="text" name="year" inputmode="numeric" pattern="[0-9]*" value="<?php echo isset($_GET['year']) ? htmlspecialchars($_GET['year']) : ''; ?>" placeholder="Enter the year of publication...">
            </div>
            <div class="form-group full-width-group">
                <input type="text" name="author" pattern="^[a-zA-Z0-9\s]*$" title="Author name should not contain special characters" value="<?php echo isset($_GET['author']) ? htmlspecialchars($_GET['author']) : ''; ?>" placeholder="Enter author's name...">
            </div>
            <div class="form-group submit-group"></div>
            <button type="submit" class="btn-confirm">Confirm</button>
        </form>
    </div>
</section>

<main class="book-list-section">
    <div class="container">
        <h2 class="section-title">
            <?php echo isset($data['keyword']) && $data['keyword'] !== '' ? 'Kết quả tìm kiếm cho: ' . $data['keyword'] : 'All the books in the library'; ?>
        </h2>

        <div class="book-grid">
            <?php if (empty($data['books'])): ?>
                <div class="no-results">
                    <i class="fa-solid fa-magnifying-glass" style="font-size: 50px; color: #ccc; margin-bottom: 20px;"></i>
                    <h3>Oops! No books found</h3>
                    <p>We couldn't find any books matching your filters. Please try again with different keywords or genres.</p>
                </div>
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
                            <a href="<?php echo URL_ROOT; ?>/book/detail/<?php echo $book['book_id']; ?>">
                                <img src="<?php echo !empty($book['image_url']) ? URL_ROOT . '/images/books/' . htmlspecialchars($book['image_url']) : URL_ROOT . '/images/books/default-book.jpg'; ?>"
                                alt="<?php echo htmlspecialchars($book['title']); ?>"></a>
                        </div>

                        <div class="card-body">
                            <h3 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                            <p class="book-author"><?php echo htmlspecialchars($book['author']); ?></p>
                            <p class="book-isbn">ISBN: <?php echo htmlspecialchars($book['isbn']); ?></p>
                        </div><hr>

                        <div class="book-meta">
                            <span><?php echo $book['available_copies']; ?> versions available</span>
                            <span>/<?php echo $book['total_copies']; ?> copies</span>
                        </div>

                        <div class="card-footer">
                            <a href="<?php echo URL_ROOT; ?>/book/detail/<?php echo $book['book_id']; ?>" class="btn-details">See details</a>

                            <?php if ($book['available_copies'] > 0): ?>
                                <a href="<?php echo URL_ROOT; ?>/reservation/create/<?php echo $book['book_id']; ?> " class="btn-reservation">Reservations</a>
                            <?php else: ?>
                                <button class="btn-reservation" disabled style="opacity: 0.5; cursor: not-allowed;">Unavailable</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($data['pagination'] && $data['pagination']['total_pages'] > 1): ?>
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
        <?php endif; ?>
    </div>
</main>

<?php require APP_ROOT . "/app/views/inc/footer.php"; ?>