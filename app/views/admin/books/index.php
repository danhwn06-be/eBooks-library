<?php require APP_ROOT . '/app/views/admin/inc/header.php'; ?>

<main class="container">
    <div class="page-header">
        <h1>Book management</h1>
        <div class="page-header right">
            <!-- <a href="<?php //echo URL_ROOT; ?>/admin/export" target="_blank" style="text-decoration: none"> -->
                <button type="button" class="btn-export" id="btnOpenImportModal">
                    <i class="fa-solid fa-file-excel"></i> Import book
                </button>
            <!-- </a>  -->
            <a href="<?php echo URL_ROOT; ?>/admin/add" style="text-decoration: none;">
                <button class="btn-add">
                    <i class="fa-solid fa-plus"></i>
                        Add new book
                </button>
            </a>
        </div>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Book ID</th>
                    <th>ISBN</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>Image</th>
                    <th>Publisher</th>
                    <th>Publication year</th>
                    <th>Created at</th>
                    <th>Content description</th>
                    <th>Number of copies</th>
                    <th>Operation</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data['books'])): ?>
                    <?php foreach ($data['books'] as $book): ?>
                        <tr>
                            <td><?php echo $book['book_id']; ?></td>
                            <td><?php echo $book['isbn']; ?></td>
                            <td class="font-bold"><?php echo htmlspecialchars($book['title']); ?></td>
                            <td><?php echo htmlspecialchars($book['author']); ?></td>
                            <td><?php echo htmlspecialchars($book['category_name']); ?></td>
                            <td class="url-text">
                                <?php if ($book['image_url']): ?>
                                    <img src="<?php echo URL_ROOT . '/images/books/' . $book['image_url']; ?>" alt="Cover" style="width: 40px; height: auto;">
                                <?php else: ?>
                                    <span>No Image</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($book['publisher']); ?></td>
                            <td><?php echo $book['publication_year']; ?></td>
                            <td><?php echo date('Y-m-d', strtotime($book['created_at'])); ?></td>
                            <td class="desc-text">
                                <?php echo (strlen($book['description']) > 50) ? substr(htmlspecialchars($book['description']), 0, 50) . '...' : htmlspecialchars($book['description']); ?>
                            </td>
                            <td style="text-align: center;">
                                <a href="<?php echo URL_ROOT; ?>/admin/copies/<?php echo $book['book_id']; ?>" class="badge-copies">
                                    <i class="fa-solid fa-eye"></i>
                                    <?php echo $book['total_copies']; ?> copies
                                </a>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="<?php echo URL_ROOT; ?>/admin/edit/<?php echo $book['book_id']; ?>" class="btn-edit" title="Edit">
                                        <i class="fa-regular fa-pen-to-square"></i> Edit
                                    </a>

                                    <a href="<?php echo URL_ROOT; ?>/admin/delete/<?php echo $book['book_id']; ?>"
                                        class="btn-delete"
                                        title="Delete"
                                        onclick="return confirm('Are you sure you want to delete this book? This implies deleting all its copies too.');">
                                        <i class="fa-regular fa-trash-can"></i> Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="12" style="text-align: center;">No books found in database</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<div id="importModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h2>Import Books from Excel</h2>
            <button class="btn-close-modal">&times;</button>
        </div>
        
        <div class="modal-body">
            <form action="<?php echo URL_ROOT; ?>/admin/import_books" method="POST" enctype="multipart/form-data">
                <div style="padding: 25px;">
                    <p class="input-hint">Select an Excel file (.xlsx) to update inventory:</p>
                    
                    <div class="file-upload-wrapper">
                        <input type="file" name="import_file" accept=".xlsx, .xls" required class="file-input">
                        <p class="file-hint">Supported formats: .xlsx, .xls</p>
                    </div>

                    <div class="link-wrapper right-align">
                        <a href="<?php echo URL_ROOT; ?>/admin/download_template" class="link-primary">
                            <i class="fa-solid fa-download"></i> Download Template File
                        </a>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn-success full-width">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Upload & Import Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?php echo URL_ROOT; ?>/js/admin.js"></script>
<script>
    // Script xử lý Modal Import (Thêm trực tiếp để đảm bảo hoạt động)
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('importModal');
        const btnOpen = document.getElementById('btnOpenImportModal');
        const btnClose = document.querySelector('.btn-close-modal');

        if (btnOpen && modal) {
            btnOpen.addEventListener('click', function(e) {
                e.preventDefault();
                modal.classList.add('active');
                modal.style.display = 'flex'; // Đảm bảo hiển thị đè lên CSS mặc định
            });
        }

        if (btnClose && modal) {
            btnClose.addEventListener('click', function() {
                modal.classList.remove('active');
                modal.style.display = 'none';
            });
        }

        // Đóng khi click ra ngoài vùng modal
        window.addEventListener('click', function(e) {
            if (e.target == modal) {
                modal.classList.remove('active');
                modal.style.display = 'none';
            }
        });
    });
</script>
</body>

</html>