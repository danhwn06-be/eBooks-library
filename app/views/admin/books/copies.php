<?php require APP_ROOT . '/app/views/admin/inc/header.php'; ?>

<main class="container">
    <div class="book-info-section">
        <h1 class="page-title">Details of the book: "<?php echo htmlspecialchars($data['book']['title']); ?>"</h1>
        
        <div class="metadata-list">
            <div class="meta-row" style="display: flex; flex-direction: column; gap: 5px;">
                <span class="meta-label">Categories: <?php echo htmlspecialchars($data['book']['category_name']); ?></span>
                <span class="meta-label">Author: <?php echo htmlspecialchars($data['book']['author']); ?></span>
                <span class="meta-label">ISBN: <?php echo htmlspecialchars($data['book']['isbn']); ?></span>
            </div>

            <a href="<?php echo URL_ROOT; ?>/admin/add_copy/<?php echo $data['book']['book_id']; ?>" style="text-decoration: none;">
                <button class="btn-add">
                    <i class="fa-solid fa-plus"></i>
                    Add new copy
                </button>
            </a>
        </div>

        <div class="table-wrapper">
            <table style="min-width: 0;">
                <thead>
                    <tr>
                        <th>Copy ID</th>
                        <th>Copy code</th>
                        <th>Status</th>
                        <th>Condition note</th>
                        <th>Created at</th>
                        <th style="width: 1%; white-space: nowrap;">Operation</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['copies'])): ?>
                        <?php foreach ($data['copies'] as $copy): ?>
                            <tr>
                                <td class="bold-id">#<?php echo $copy['copy_id']; ?></td>
                                <td class="bold-id"><?php echo htmlspecialchars($copy['copy_code']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo strtolower($copy['status']); ?>">
                                        <?php echo htmlspecialchars($copy['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($copy['condition_note'] ?? ''); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($copy['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="<?php echo URL_ROOT; ?>/admin/edit_copy/<?php echo $copy['copy_id']; ?>" class="btn-edit">
                                            <i class="fa-regular fa-pen-to-square"></i> Edit
                                        </a>
                                        
                                        <a href="<?php echo URL_ROOT; ?>/admin/delete_copy/<?php echo $copy['copy_id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this copy?');">
                                            <i class="fa-regular fa-trash-can"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No copies found for this book.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>