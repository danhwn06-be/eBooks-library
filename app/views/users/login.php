<?php require APP_ROOT . '/app/views/inc/header.php'; ?>

<div class="login-page">
    <div class="container login-container-box"> 
        
        <div class="login-overlay"></div>

        <div class="login-card">
        <div class="login-header">
            <div class="brand-section">
                <span class="brand-name">eBook library</span>
                <div class="logo">
                    <i class="fa-solid fa-book-open"></i>
                </div>
            </div>

            <div class="title-section">
                <h2>E-BOOK LIBRARY</h2>
                <p class="subtitle">Log in</p>
            </div>
        </div>

            <?php if (isset($_GET['status']) && $_GET['status'] == 'success') : ?>
                <div class="alert alert-success" style="color: green; text-align: center; margin-bottom: 10px;">
                    Registration successful! You can now log in.
                </div>
            <?php endif; ?>
            
            <?php if (!empty($data['error'])) : ?>
                <div class="login-error">
                    <?php echo $data['error']; ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo URL_ROOT; ?>/user/login" method="POST" class="login-form">
                <div class="form-group">
                    <input
                        type="text"
                        name="email"
                        placeholder="Email/phoneNumbers..."
                        required
                    >
                </div>

                <div class="form-group password-group">
                    <input
                        type="password"
                        name="password"
                        placeholder="Password..."
                        required
                    >
                    <span class="toggle-password">
                        <i class="fa-regular fa-eye"></i>
                    </span>
                </div>

                <button type="submit" class="btn-login">
                    Log in
                </button>
            </form>

            <div class="login-footer">
                <p>
                    Don't have an account yet?
                </p>
                <a href="<?php echo URL_ROOT; ?>/user/register">Register here</a>
            </div>
        </div>

    </div> </div>

<?php require APP_ROOT . '/app/views/inc/footer.php'; ?>