<?php require APP_ROOT . '/app/views/admin/inc/header.php'; ?>

<div class="main-content">
    
    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success">Loan created successfully!</div>
    <?php endif; ?>
    <?php if(!empty($data['error'])): ?>
        <div class="alert alert-danger"><?php echo $data['error']; ?></div>
    <?php endif; ?>

    <div class="tab-container">
        <a href="<?php echo URL_ROOT; ?>/admin/loans" class="tab-link active">Borrow</a>
        <a href="<?php echo URL_ROOT; ?>/admin/returns" class="tab-link">Return</a>
        <a href="#" class="tab-link">Loan Tracking</a>
        <a href="#" class="tab-link">Reservations</a>
    </div>
    <div class="tab-line"></div>

    <div class="form-card">
        <form action="<?php echo URL_ROOT; ?>/admin/loans" method="POST" id="borrowForm">
            <div class="form-grid">
                
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Member Code</label>
                        <input type="text" name="member_code" class="form-input" 
                               placeholder="e.g. MB001..." 
                               value="<?php echo isset($data['member_code']) ? $data['member_code'] : ''; ?>" required>
                        <small class="form-note">* The system will check if member exists.</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Borrow Date</label>
                        <input type="date" name="borrow_date" class="form-input" 
                               value="<?php echo $data['current_date']; ?>" readonly>
                    </div>
                </div>

                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Book Copy ID</label>
                        <select name="copy_id" class="form-input" required>
                            <option value="">-- Select Available Copy --</option>
                            <?php foreach($data['books'] as $book): ?>
                                <option value="<?php echo $book->copy_id; ?>">
                                    <?php echo $book->title . ' (ID: ' . $book->copy_id . ')'; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-note">* Only records with status "Available" are displayed.</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Due Date (Max 30 Days)</label>
                        <input type="date" name="due_date" class="form-input" 
                               value="<?php echo $data['default_due_date']; ?>"
                               min="<?php echo $data['current_date']; ?>" 
                               max="<?php echo $data['max_due_date']; ?>" required>
                        <small class="form-note">* Default is 14 days. Max limit is 30 days.</small>
                    </div>
                </div>
            </div>

            <div class="form-group" style="margin-top: 20px;">
                <label class="form-label">Note</label>
                <textarea name="note" class="form-input" rows="4" placeholder="Additional notes..."><?php echo isset($data['note']) ? $data['note'] : ''; ?></textarea>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-refresh" onclick="document.getElementById('borrowForm').reset()">Refresh</button>
                <button type="submit" class="btn btn-confirm">Confirm Loan</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Script nhỏ để đảm bảo khi reset form thì ngày tháng không bị mất
    document.querySelector('.btn-refresh').addEventListener('click', function(e) {
        e.preventDefault();
        window.location.href = "<?php echo URL_ROOT; ?>/admin/loans";
    });
</script>

</body>
</html>