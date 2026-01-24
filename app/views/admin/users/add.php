<?php require APP_ROOT . '/app/views/admin/inc/header.php'; ?>

<div class="add-user-bg">
    <div class="add-card">
        <div style="margin-bottom: 20px;">
            <i class="fas fa-book-open" style="font-size: 40px; color: #4a90e2;"></i>
            <h2 style="margin-top: 10px;">E-BOOK LIBRARY<br>+ Add new User</h2>
        </div>

        <form action="<?php echo URL_ROOT; ?>/admin/addUser" method="POST">
            <input type="text" name="full_name" placeholder="Full-name..." class="custom-input" required>
            <input type="email" name="email" placeholder="Email..." class="custom-input" value="<?php echo $data['email']; ?>" required>
            <span style="color:red; display:block; text-align:left; margin-bottom:10px;"><?php echo $data['email_err']; ?></span>
            
            <input type="text" name="address" placeholder="Address..." class="custom-input">
            <input type="text" name="phone_number" placeholder="Phone number..." class="custom-input">
            
            <div style="position:relative;">
                <input type="password" name="password" placeholder="Password..." class="custom-input">
                <i class="fas fa-eye-slash" style="position:absolute; right:15px; top:15px; color:white;"></i>
            </div>
            
            <div style="position:relative;">
                <input type="password" name="confirm_password" placeholder="Confirm password..." class="custom-input">
                <i class="fas fa-eye-slash" style="position:absolute; right:15px; top:15px; color:white;"></i>
            </div>

            <button type="submit" class="btn-confirm">Confirm</button>
            <a href="<?php echo URL_ROOT; ?>/admin/users">
                <button type="button" class="btn-back">Back to List</button>
            </a>
            
            <p style="margin-top:15px; font-size:12px; color:#1a237e;">
                Once the User is successfully added,<br>it will appear on the list page.
            </p>
        </form>
    </div>
</div>