<?php require APP_ROOT . '/app/views/admin/inc/header.php'; ?>

<main class="container">
    <div class="page-header">
        <div class="header-left">
            <a href="<?php echo URL_ROOT; ?>/admin/copies/<?php echo $data['book_id']; ?>" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i> Back to Copies
            </a>
            <h1>Add Copy for: <span style="color: #435ebe;"><?php echo htmlspecialchars($data['book']['title']); ?></span></h1>
        </div>
    </div>

    <div class="form-wrapper">
        <form action="<?php echo URL_ROOT; ?>/admin/store_copy" method="POST">
            <input type="hidden" name="book_id" value="<?php echo $data['book_id']; ?>">

            <div class="form-row">
                <div class="form-group">
                    <label>Copy Code (Unique ID) <span style="color:red">*</span></label>
                    <input type="text" name="copy_code" placeholder="e.g. HP1-005" required>
                    <small style="color: #777;">Must be unique for scanning.</small>
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="Available">Available</option>
                        <option value="Borrowed">Borrowed</option>
                        <option value="Lost">Lost</option>
                        <option value="Damaged">Damaged</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Condition / Quality</label>
                <input type="text" name="quality" placeholder="e.g. New, Good, Worn..." required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-save">Add Copy</button>
            </div>
        </form>
    </div>
</main>