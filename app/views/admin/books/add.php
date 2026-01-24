<?php require APP_ROOT . '/app/views/admin/inc/header.php'; ?>

<main class="container">
    <div class="page-header">
        <div class="header-left">
            <a href="<?php echo URL_ROOT; ?>/admin/books" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i> Back to list
            </a>
            <h1>Add New Book</h1>
        </div>
    </div>

    <div class="form-wrapper">
        <form action="<?php echo URL_ROOT; ?>/admin/store" method="POST" enctype="multipart/form-data">
            
            <div class="form-row">
                <div class="form-group">
                    <label>Book Title <span style="color:red">*</span></label>
                    <input type="text" name="title" required>
                </div>
                <div class="form-group">
                    <label>ISBN <span style="color:red">*</span></label>
                    <input type="text" name="isbn" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Author <span style="color:red">*</span></label>
                    <input type="text" name="author" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id">
                        <?php foreach($data['categories'] as $cat): ?>
                            <option value="<?php echo $cat['category_id']; ?>">
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Publisher</label>
                    <input type="text" name="publisher">
                </div>
                <div class="form-group">
                    <label>Publication Year</label>
                    <input type="number" name="publication_year" min="1000" max="9999">
                </div>
            </div>

            <div class="form-group">
                <label>Book Cover Image</label>
                <input type="file" name="image" accept="image/*">
                <small style="color: #666;">Allowed: jpg, jpeg, png, webp</small>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="5"></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-save">Add Book</button>
            </div>
        </form>
    </div>
</main>