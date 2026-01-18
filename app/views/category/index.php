<?php require APP_ROOT . '/app/views/inc/header.php'; ?>

<div class="container" style="min-height: 80vh;">
    <!-- Phần tiêu đề danh mục -->
    <section class="book-list-section">
        <h2 class="section-title">
            <?php echo htmlspecialchars($data['title']); ?>
        </h2>
        
        <!-- Grid hiển thị sách (giống Home) -->
        <div class="book-grid">
            <?php if (!empty($data['books'])): ?>
                <?php foreach ($data['books'] as $book): ?>
                    <div class="book-card">
                        <!-- Badge trạng thái -->
                        <div class="badge-available" 
                             style="background: <?php echo ($book['available_copies'] > 0) ? '#2ecc71' : '#e74c3c'; ?>">
                            <?php echo ($book['available_copies'] > 0) ? 'Available' : 'Out of stock'; ?>
                        </div>
                        
                        <!-- Hình ảnh sách -->
                        <div class="book-cover-placeholder">
                            <img src="<?php echo URL_ROOT; ?>/images/books/<?php echo !empty($book['image_url']) ? htmlspecialchars($book['image_url']) : 'default.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($book['title']); ?>">
                        </div>
                        
                        <!-- Thông tin sách -->
                        <div class="card-body">
                            <h3 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                            <p class="book-author">by <?php echo htmlspecialchars($book['author']); ?></p>
                            <p class="book-isbn">ISBN: <?php echo htmlspecialchars($book['isbn']); ?></p>
                        </div>
                        
                        <!-- Meta thông tin -->
                        <div class="book-meta">
                            <span>Total: <?php echo $book['total_copies']; ?> copies</span>
                            <span>Available: <?php echo $book['available_copies']; ?> copies</span>
                        </div>
                        
                        <!-- Nút hành động -->
                        <div class="card-footer">
                            <a href="<?php echo URL_ROOT; ?>/book/detail/<?php echo $book['book_id']; ?>" 
                               class="btn-details">See details</a>
                            
                            <?php if ($book['available_copies'] > 0): ?>
                                <a href="<?php echo URL_ROOT; ?>/reservation/create/<?php echo $book['book_id']; ?>" 
                                   class="btn-reservation">Reservation</a>
                            <?php else: ?>
                                <button class="btn-reservation" disabled>Unavailable</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Hiển thị khi không có sách -->
                <div class="no-books" style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                    <i class="fa-solid fa-book-open" style="font-size: 70px; color: #bdc3c7; margin-bottom: 20px;"></i>
                    <h3 style="color: #7f8c8d; margin-bottom: 15px;">No books found</h3>
                    <p style="color: #95a5a6;">There are no books in this category yet.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Phân trang (giống Home) -->
        <?php if (isset($data['totalPages']) && $data['totalPages'] > 1): ?>
        <div class="pagination-container">
            <!-- Nút Previous -->
            <a href="?page=<?php echo max(1, $data['currentPage'] - 1); ?>" 
               class="page-btn nav-btn <?php echo ($data['currentPage'] == 1) ? 'disabled' : ''; ?>">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
            
            <!-- Các số trang -->
            <?php 
            $startPage = max(1, $data['currentPage'] - 2);
            $endPage = min($data['totalPages'], $data['currentPage'] + 2);
            
            for ($i = $startPage; $i <= $endPage; $i++): 
            ?>
                <a href="?page=<?php echo $i; ?>" 
                   class="page-btn <?php echo ($i == $data['currentPage']) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            
            <!-- Nút Next -->
            <a href="?page=<?php echo min($data['totalPages'], $data['currentPage'] + 1); ?>" 
               class="page-btn nav-btn <?php echo ($data['currentPage'] == $data['totalPages']) ? 'disabled' : ''; ?>">
                <i class="fa-solid fa-chevron-right"></i>
            </a>
        </div>
        <?php endif; ?>
    </section>
</div>

<?php require APP_ROOT . '/app/views/inc/footer.php'; ?>