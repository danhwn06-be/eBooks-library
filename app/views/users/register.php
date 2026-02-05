<?php require APP_ROOT . '/app/views/inc/header.php'; ?>

<div class="login-page">
    <div class="container login-container-box"> 
        <div class="login-overlay"></div>

        <div class="login-card custom-padding"> <div class="login-header">
                <div class="brand-section">
                    <span class="brand-name">eBook library</span>
                    <div class="logo">
                        <i class="fa-solid fa-book-open"></i>
                    </div>
                </div>

                <div class="title-section">
                    <h2 class="main-title">E-BOOK LIBRARY</h2>
                    <p class="subtitle">Register</p> </div>
            </div>

            <?php if (!empty($data['error'])) : ?>
                <div class="alert alert-danger" style="color: red; text-align: center; margin-bottom: 10px;">
                    <?php echo $data['error']; ?>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo URL_ROOT; ?>/user/register" method="POST" class="login-form">
                <div class="form-group">
                    <input type="text" name="email" placeholder="Email..." class="input-blue">
                </div>
                <div class="form-group">
                    <input type="text" name="phone_number" placeholder="Phone number..." class="input-blue">
                </div>
                <div class="form-group">
                    <input type="text" name="full_name" placeholder="Full-name..." class="input-blue">
                </div>
                <div class="form-group">
                    <input type="text" name="user_name" placeholder="User name..." class="input-blue">
                </div>
                <div class="form-group">
                    <input type="text" name="address" placeholder="Address..." class="input-blue">
                </div>
                <div class="form-group password-group">
                    <input type="password" name="password" placeholder="Password..." class="input-blue">
                    <span class="toggle-password"><i class="fa-regular fa-eye"></i></span>
                </div>
                <div class="form-group password-group">
                    <input type="password" name="confirm_password" placeholder="Confirm password...." class="input-blue">
                    <span class="toggle-password"><i class="fa-regular fa-eye-slash"></i></span>
                </div>

                <button type="submit" class="btn-register">Register</button>
            </form>

            <div class="login-footer">
                <p>Do you already have an account?</p>
                <a href="<?php echo URL_ROOT; ?>/user/login" class="login-link">Login here</a>
            </div>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/app/views/inc/footer.php'; ?>