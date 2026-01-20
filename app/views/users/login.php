<?php require APP_ROOT . '/app/views/inc/header.php'; ?>

<div class="login-page">
    <div class="container login-container-box"> 
        
        <div class="login-overlay"></div>

        <div class="login-card">
            <div class="login-header">
                <div class="logo">
                    <i class="fa-solid fa-book-open">E-BOOK LIBRARY</i>
                </div>
                <h2>E-BOOK LIBRARY</h2>
                <p class="subtitle">Log in</p>
            </div>

            <?php if (!empty($data['error'])) : ?>
                <div class="login-error">
                    <?php echo $data['error']; ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo URL_ROOT; ?>/users/login" method="POST" class="login-form">
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
                <a href="<?php echo URL_ROOT; ?>/users/register">Register here</a>
            </div>
        </div>

    </div> </div>

<?php require APP_ROOT . '/app/views/inc/footer.php'; ?>