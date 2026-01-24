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
                    <span class="stat-value">5</span> </div>
                <div class="stat-box">
                    <span class="stat-label">READING</span>
                    <span class="stat-value">2</span>
                </div>
            </div>
        </div>

        <div class="profile-card profile-card-right">
            <div class="card-header">
                <div class="header-title">
                    <i class="fas fa-user-alt"></i> Account Details
                </div>
                <a href="<?php echo URL_ROOT; ?>/users/edit" class="btn-edit-profile">
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
                    <p><?php echo str_pad($data['user']->member_code, 3, '0', STR_PAD_LEFT); ?></p>
                </div>
                <div class="info-group">
                    <label>LOCATION</label>
                    <p><?php echo $data['user']->address ?? 'My Khe, An Hai, Son Tra, Da Nang'; ?></p>
                </div>
                <div class="info-group">
                    <label>PHONE NUMBER</label>
                    <p><?php echo $data['user']->phone_number ?? '0123 456 789'; ?></p>
                </div>
                <div class="info-group full-width">
                    <label>PASSWORD</label>
                    <div class="password-wrapper">
                        <span>••••••••••••••</span>
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
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>HTLK917</td>
                        <td class="book-info">
                            <i class="fas fa-book book-icon"></i> Peter Pan
                        </td>
                        <td>16/01/2026</td>
                        <td>20/01/2026</td>
                        <td><a href="#" class="btn-see-details">See Details</a></td>
                    </tr>
                    <tr>
                        <td>BHMF351</td>
                        <td class="book-info">
                            <i class="fas fa-book book-icon"></i> The story of Pinocchio
                        </td>
                        <td>16/01/2026</td>
                        <td>20/01/2026</td>
                        <td><a href="#" class="btn-see-details">See Details</a></td>
                    </tr>
                    <tr class="empty-row"><td>&nbsp;</td><td></td><td></td><td></td><td></td></tr>
                    <tr class="empty-row"><td>&nbsp;</td><td></td><td></td><td></td><td></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require APP_ROOT . "/app/views/inc/footer.php"; ?>