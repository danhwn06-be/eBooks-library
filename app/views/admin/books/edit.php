<?php require APP_ROOT . '/app/views/admin/inc/header.php'; ?>

<main class="container">
    <div class="page-header">
        <div class="header-left">
            <a href="<?php echo URL_ROOT; ?>/admin/books" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i> Back to list
            </a>
            <h1>Edit Book: <?php echo htmlspecialchars($data['book']['title']); ?></h1>
        </div>
    </div>

    <div class="form-wrapper">
        <form action="<?php echo URL_ROOT; ?>/admin/update/<?php echo $data['book']['book_id']; ?>" method="POST" enctype="multipart/form-data">
            
            <div class="form-row">
                <div class="form-group">
                    <label>Book Title</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($data['book']['title']); ?>" required>
                </div>
                <div class="form-group">
                    <label>ISBN</label>
                    <input type="text" name="isbn" value="<?php echo htmlspecialchars($data['book']['isbn']); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Author</label>
                    <input type="text" name="author" value="<?php echo htmlspecialchars($data['book']['author']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id">
                        <?php foreach($data['categories'] as $cat): ?>
                            <option value="<?php echo $cat['category_id']; ?>" 
                                <?php echo ($data['book']['category_id'] == $cat['category_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Publisher</label>
                    <input type="text" name="publisher" value="<?php echo htmlspecialchars($data['book']['publisher']); ?>">
                </div>
                <div class="form-group">
                    <label>Publication Year</label>
                    <input type="number" name="publication_year" value="<?php echo $data['book']['publication_year']; ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Current Image</label>
                    <?php if(!empty($data['book']['image_url'])): ?>
                        <div style="margin-bottom: 10px;">
                            <img src="<?php echo URL_ROOT . '/images/books/' . $data['book']['image_url']; ?>" alt="Cover" style="height: 100px; border-radius: 4px; border: 1px solid #ddd;">
                        </div>
                    <?php else: ?>
                        <p style="color: #888;">No image uploaded.</p>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Change Image (Optional)</label>
                    <input type="file" name="image" accept="image/*">
                    <small style="color: #666;">Leave empty to keep current image.</small>
                </div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="5"><?php echo htmlspecialchars($data['book']['description']); ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-save">Update Book</button>
            </div>
        </form>
    </div>
</main>