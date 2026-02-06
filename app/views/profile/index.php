<?php require APP_ROOT . '/app/views/inc/header.php'; ?>

<div class="container profile-wrapper">
    
    <div class="profile-top-section">
        <div class="profile-card profile-card-left">
            <div class="avatar-wrapper">
                <img src="<?php echo URL_ROOT; ?>/public/img/default-avatar.png" alt="User Avatar" class="profile-img">
            </div>
            <h2 class="profile-name"><?php echo $data['user']->full_name; ?></h2>
            
            <div class="profile-stats">
                <div class="stat-box">
                    <span class="stat-label">BORROWED</span>
                    <span class="stat-value"><?php echo $data['count_borrowed']; ?></span> 
                </div>
                <div class="stat-box">
                    <span class="stat-label">READING</span>
                    <span class="stat-value"><?php echo $data['count_reading']; ?></span>
                </div>
            </div>
        </div>

        <div class="profile-card profile-card-right">
            <div class="card-header">
                <div class="header-title">
                    <i class="fas fa-user-alt"></i> Account Details
                </div>
                <a href="<?php echo URL_ROOT; ?>/user/edit" class="btn-edit-profile">
                    <i class="fas fa-pencil-alt"></i> Edit profile
                </a>
            </div>

            <div class="account-details-grid">
                <div class="info-group">
                    <label>EMAIL ADDRESS</label>
                    <p><?php echo $data['user']->email; ?></p>
                </div>
                <div class="info-group">
                    <label>MEMBER CODE</label>
                    <p><?php echo isset($data['user']->member_code) ? str_pad($data['user']->member_code, 3, '0', STR_PAD_LEFT) : 'N/A'; ?></p>
                </div>
                <div class="info-group">
                    <label>LOCATION</label>
                    <p><?php echo $data['user']->address ?? 'Not updated'; ?></p>
                </div>
                <div class="info-group">
                    <label>PHONE NUMBER</label>
                    <p><?php echo $data['user']->phone_number ?? 'Not updated'; ?></p>
                </div>
                <div class="info-group full-width">
                    <label>PASSWORD</label>
                    <div class="password-wrapper">
                        <span>********</span> 
                        <i class="fas fa-eye-slash toggle-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="history-section">
        <h3 class="section-title">Borrowing and repaying history</h3>
        <div class="table-responsive">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Book ID</th>
                        <th>Information</th>
                        <th>Borrowed date</th>
                        <th>Return date</th>
                        <th>Status</th> </tr>
                </thead>
<tbody>
    <?php if (empty($data['borrow_history'])): ?>
        <tr>
            <td colspan="5" style="text-align:center; padding: 20px;">No history available.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($data['borrow_history'] as $history): ?>
            <tr>
                <td>
                    <?php echo 'BK' . str_pad($history->book_id, 3, '0', STR_PAD_LEFT); ?>
                </td>
                <td class="book-info">
                    <i class="fas fa-book book-icon"></i> 
                    <?php echo $history->title; ?>
                </td>
                <td>
                    <?php echo date('d/m/Y', strtotime($history->borrow_date)); ?>
                </td>
                <td>
                    <?php 
                        // SỬA: Kiểm tra trạng thái Status thay vì kiểm tra return_date
                        if ($history->status == 'Returned' && $history->return_date) {
                            echo date('d/m/Y', strtotime($history->return_date));
                        } elseif ($history->due_date) {
                            // Nếu chưa trả, hiển thị ngày Hẹn trả (Due date)
                            echo '<span style="color:#666;">Due: ' . date('d/m/Y', strtotime($history->due_date)) . '</span>';
                        } else {
                            echo '<span style="color:red;">Not returned</span>';
                        }
                    ?>
                </td>
                <td>
                    <?php if (strtolower($history->status) == 'returned'): ?>
                        <span class="badge badge-success" style="color: green; font-weight: bold;">Returned</span>
                    <?php else: ?>
                        <span class="badge badge-warning" style="color: orange; font-weight: bold;">Borrowing</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (count($data['borrow_history']) < 3): ?>
            <tr class="empty-row"><td>&nbsp;</td><td></td><td></td><td></td><td></td></tr>
    <?php endif; ?>
</tbody>
            </table>
        </div>
    </div>
</div>

<?php require APP_ROOT . "/app/views/inc/footer.php"; ?>