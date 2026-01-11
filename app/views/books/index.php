<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thư viện Sách - <?php echo APP_NAME; ?></title>
    <!-- Liên kết file CSS từ thư mục public -->
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/css/style.css">
</head>

<body>
    <div class="container">
        <h1>Danh sách Sách hiện có</h1>

        <?php if (empty($data['books'])): ?>
            <div class="alert">Hiện chưa có cuốn sách nào trong thư viện.</div>
        <?php else: ?>
            <div class="book-grid">
                <?php foreach ($data['books'] as $book): ?>
                    <div class="book-card">
                        <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                        <div class="book-info"><strong>Tác giả:</strong> <?php echo htmlspecialchars($book['author']); ?></div>
                        <div class="book-info"><strong>Thể loại:</strong> <?php echo htmlspecialchars($book['category']); ?></div>
                        <div class="book-info"><strong>NXB:</strong> <?php echo htmlspecialchars($book['publisher']); ?> (<?php echo htmlspecialchars($book['publication_year']); ?>)</div>
                        <div class="book-isbn">ISBN: <?php echo htmlspecialchars($book['isbn']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>