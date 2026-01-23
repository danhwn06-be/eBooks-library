<?php require APP_ROOT . '/app/views/admin/inc/header.php'; ?>

<main class="container">
    <div class="page-header">
        <div class="header-left">
            <a href="<?php echo URL_ROOT; ?>/admin/copies/<?php echo $data['copy']['book_id']; ?>" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i> Back to Copies
            </a>
            <h1>Edit Copy: <?php echo htmlspecialchars($data['copy']['copy_code']); ?></h1>
        </div>
    </div>

    <div class="form-wrapper">
        <form action="<?php echo URL_ROOT; ?>/admin/update_copy/<?php echo $data['copy']['copy_id']; ?>" method="POST">
            <input type="hidden" name="book_id_redirect" value="<?php echo $data['copy']['book_id']; ?>">

            <div class="form-row">
                <div class="form-group">
                    <label>Copy Code</label>
                    <input type="text" value="<?php echo htmlspecialchars($data['copy']['copy_code']); ?>" readonly style="background: #f9f9f9; color: #777;">
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <?php 
                            $options = ['Available', 'Borrowed', 'Lost', 'Damaged'];
                            foreach($options as $opt): 
                        ?>
                            <option value="<?php echo $opt; ?>" <?php echo ($data['copy']['status'] == $opt) ? 'selected' : ''; ?>>
                                <?php echo $opt; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Condition / Quality</label>
                <input type="text" name="quality" value="<?php echo htmlspecialchars($data['copy']['quality'] ?? $data['copy']['condition_note'] ?? ''); ?>">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-save">Update Copy</button>
            </div>
        </form>
    </div>
</main>