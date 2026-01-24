<?php require APP_ROOT . '/app/views/inc/header.php'; ?>

<div class="container profile-wrapper">
    <div class="edit-profile-card">
        <div class="account-header-title">
            <i class="fas fa-user"></i> Account Details
        </div>

        <form action="<?php echo URL_ROOT; ?>/users/edit" method="POST">
            <div class="avatar-edit-section">
                <img src="<?php echo URL_ROOT; ?>/public/img/default-avatar.png" alt="User Avatar" class="profile-img">
                <div class="name-input-wrapper">
                    <input type="text" name="full_name" value="<?php echo $data['user']->full_name; ?>" class="input-name-display">
                </div>
            </div>

            <div class="info-group-box">
                <div class="edit-grid">
                    <div class="form-group">
                        <label>EMAIL ADDRESS</label>
                        <input type="email" name="email" value="<?php echo $data['user']->email; ?>">
                    </div>
                    <div class="form-group">
                        <label>MEMBER CODE</label>
                        <input type="text" value="MB<?php echo str_pad($data['user']->user_id, 3, '0', STR_PAD_LEFT); ?>" readonly class="readonly-input">
                    </div>
                    <div class="form-group">
                        <label>LOCATION</label>
                        <input type="text" name="address" value="<?php echo $data['user']->address; ?>">
                    </div>
                    <div class="form-group">
                        <label>PHONE NUMBER</label>
                        <input type="text" name="phone_number" value="<?php echo $data['user']->phone_number; ?>">
                    </div>
                </div>
            </div>

            <div class="info-group-box password-section">
                <div class="form-group full-width">
                    <label>CURRENT PASSWORD</label>
                    <div class="pwd-input-container">
                        <input type="password" name="current_password" placeholder="••••••••••••••">
                        <i class="fas fa-eye-slash toggle-pwd"></i>
                    </div>
                    <span class="error-msg"><?php echo $data['password_err'] ?? ''; ?></span>
                </div>
                <div class="form-group full-width">
                    <label>NEW PASSWORD</label>
                    <div class="pwd-input-container">
                        <input type="password" name="new_password" placeholder="••••••••••••••">
                        <i class="fas fa-eye-slash toggle-pwd"></i>
                    </div>
                </div>
                <div class="form-group full-width">
                    <label>RE-ENTER PASSWORD</label>
                    <div class="pwd-input-container">
                        <input type="password" name="confirm_password" placeholder="••••••••••••••">
                        <i class="fas fa-eye-slash toggle-pwd"></i>
                    </div>
                </div>
            </div>

            <div class="edit-actions">
                <a href="<?php echo URL_ROOT; ?>/users/profile" class="btn-cancle">Cancle</a>
                <button type="submit" class="btn-save-green">Save</button>
            </div>
        </form>
    </div>
</div>

<?php require APP_ROOT . "/app/views/inc/footer.php"; ?>