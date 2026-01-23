<?php require APP_ROOT . '/app/views/inc/header.php'; ?>

<div class="container profile-container">
    <div class="profile-header">
        <h2>Hồ sơ độc giả</h2>
    </div>

    <div class="profile-content">
        <div class="user-info-card">
            <div class="avatar-placeholder">
                <?php echo strtoupper(substr($data['user']->full_name, 0, 1)); ?>
            </div>
            <h3><?php echo $data['user']->full_name; ?></h3>
            <p><strong>Email:</strong> <?php echo $data['user']->email; ?></p>
            <p><strong>Số điện thoại:</strong> <?php echo $data['user']->phone_number ?? 'Chưa cập nhật'; ?></p>
            <p><strong>Ngày tham gia:</strong> <?php echo date('d/m/Y', strtotime($data['user']->created_at)); ?></p>
            
            <a href="<?php echo URL_ROOT; ?>/user/edit_profile" class="btn btn-primary">Chỉnh sửa thông tin</a>
        </div>

        <div class="borrow-history">
            <h3>Lịch sử mượn sách</h3>
            <?php if (empty($data['borrow_history'])): ?>
                <p>Bạn chưa mượn cuốn sách nào.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tên sách</th>
                            <th>Ngày mượn</th>
                            <th>Hạn trả</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['borrow_history'] as $history): ?>
                            <tr>
                                <td><?php echo $history->title; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($history->borrow_date)); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($history->due_date)); ?></td>
                                <td>
                                    <span class="badge status-<?php echo strtolower($history->status); ?>">
                                        <?php echo $history->status; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require APP_ROOT . "/app/views/inc/footer.php"; ?>