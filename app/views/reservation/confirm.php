<?php require APP_ROOT . "/app/views/inc/header.php"; ?>

<div class="container main-container cf-container">
    <div class="book-reservation-section cf-book-reservation-section">
        <div class="book-cover cf-book-cover">
            <img src="<?php echo URL_ROOT . '/images/books/' . ($data['book']['image_url'] ?: 'default-book.jpg'); ?>" 
                alt="<?php echo htmlspecialchars($data['book']['title']); ?>">
        </div>
        <div class="book-display-section cf-book-display-section">   
            <h2>Register Loan</h2>             
                <div class="book-info-box cf-book-info-box">
                    <div class="info-row cf-info-row">
                        <span class="label cf-label">Title book:</span>
                        <div class="value-box cf-value-box"><?php echo htmlspecialchars($data['book']['title']); ?></div>
                    </div>
                    <div class="info-row cf-info-row">
                        <span class="label cf-label">Author:</span>
                        <div class="value-box cf-value-box"><?php echo htmlspecialchars($data['book']['author']); ?></div>
                    </div>
                    <div class="info-row cf-info-row">
                        <span class="label cf-label">Publisher:</span>
                        <div class="value-box cf-value-box"><?php echo htmlspecialchars($data['book']['publisher'] ?? 'Paragon'); ?></div>
                    </div>
                    <div class="info-row cf-info-row">
                        <span class="label cf-label">Category:</span>
                        <div class="value-box cf-value-box"><?php echo htmlspecialchars($data['book']['category_name']); ?></div>
                    </div>
                </div>
        </div>
    </div>

    <div class="reservation-card cf-reservation-card">
        <div class="member-info-section cf-member-info-section">
            <?php 
            // Kiểm tra xem user đã đăng nhập chưa
            $isLoggedIn = isset($_SESSION['user_id']) || isset($_SESSION['user_name']);
            
            if (!$isLoggedIn): ?>
                <div class="error-message cf-error-message">
                    <p>You need to be logged in with a member account to make a reservation.</p>
                    <a href="<?php echo URL_ROOT; ?>/users/login" class="btn-login cf-btn-login">Please login first</a>
                </div>
            <?php else: 
                // USER LÀ OBJECT
                $member_code = $data['user']->member_code;
            ?>
            
            <form action="<?php echo URL_ROOT; ?>/reservation/store" method="post">
                <input type="hidden" name="book_id" value="<?php echo $data['book']['book_id']; ?>">
                
                <div class="form-group cf-form-group">
                    <label class="cf-label">Email:</label>
                    <input type="email" name="email"
                           value="<?php echo htmlspecialchars($data['user']->email); ?>" readonly>
                </div>
                
                <div class="form-group cf-form-group">
                    <label class="cf-label">Address:</label>
                    <input type="text" name="address"
                           value="<?php echo htmlspecialchars($data['user']->address); ?>" readonly>
                </div>
                
                <div class="form-group cf-form-group">
                    <label class="cf-label">Member Code:</label>
                    <input type="text" class="cf-input-center"
                           value="<?php echo htmlspecialchars($member_code); ?>" readonly>
                    <input type="hidden" name="member_code"
                           value="<?php echo htmlspecialchars($member_code); ?>">
                </div>
                
                <div class="form-group cf-form-group">
                    <label class="cf-label">Phone number:</label>
                    <input type="tel" name="phone"
                           value="<?php echo htmlspecialchars($data['user']->phone_number); ?>" readonly>
                </div>
                
                <div class="row-flex cf-row-flex">
                    <div class="form-group flex-1 cf-form-group">
                        <label class="cf-label">Borrow Date:</label>
                        <input type="date" name="borrow_date"
                               value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group flex-1 cf-form-group">
                        <label class="cf-label">Loan term:</label>
                        <select name="loan_term" required>
                            <option value="1_week">1 week</option>
                            <option value="2_weeks">2 weeks</option>
                            <option value="3_weeks">3 weeks</option>
                        </select>
                    </div>
                </div>

                <div class="reservation-footer cf-reservation-footer">
                    <button type="submit" class="confirm-btn cf-confirm-btn">Confirm</button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require APP_ROOT . "/app/views/inc/footer.php"; ?>
