<?php require APP_ROOT . '/app/views/admin/inc/header.php'; ?>

<div class="container">
    <a href="<?php echo URL_ROOT; ?>/admin/users" style="color: #666; text-decoration: none;">
        <i class="fas fa-arrow-left"></i> Back to list
    </a>

    <div class="admin-edit-container">
        <div class="form-title" style="margin-bottom: 20px; font-weight: bold; color: #1a237e;">
            <i class="fas fa-user"></i> Account Details
        </div>
        
        <form action="<?php echo URL_ROOT; ?>/admin/editUser/<?php echo $data['user_id']; ?>" method="POST">
            <div class="profile-header">
                <img src="<?php echo URL_ROOT; ?>/public/img/default-avatar.png" class="profile-avatar">
                <br>
                <div class="profile-name">
                    <input type="text" name="full_name" value="<?php echo $data['full_name']; ?>" class="form-control text-center">
                </div>
            </div>

            <div class="info-box">
                <div class="form-row">
                    <div class="form-group">
                        <label>EMAIL ADDRESS</label>
                        <input type="email" name="email" value="<?php echo $data['email']; ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>MEMBER CODE</label>
                        <input type="text" value="<?php echo $data['member_code']; ?>" class="form-control" readonly style="background:#f9f9f9; color:#888;">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>LOCATION</label>
                        <input type="text" name="address" value="<?php echo $data['address']; ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>PHONE NUMBER</label>
                        <input type="text" name="phone_number" value="<?php echo $data['phone_number']; ?>" class="form-control">
                    </div>
                </div>
            </div>

            <div class="info-box">
                <div class="form-group">
                    <label>NEW PASSWORD (LEAVE BLANK TO KEEP CURRENT)</label>
                    <div style="position:relative;">
                        <input type="password" name="password" class="form-control" placeholder="Enter new password...">
                        <i class="toggle-password" style="position:absolute; right:10px; top:12px; color:#333; cursor: pointer;"></i>
                    </div>
                </div>
                </div>

            <div class="actions">
                <a href="<?php echo URL_ROOT; ?>/admin/users" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-save">Save</button>
            </div>
        </form>
    </div>
</div>
