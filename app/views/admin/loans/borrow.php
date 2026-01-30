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
        <a href="#" class="tab-link">Return</a>
        <a href="#" class="tab-link">Loan Tracking</a>
        <a href="#" class="tab-link">Reservations</a>
    </div>
    <div class="tab-line"></div>

    <div class="form-card" id="borrow-content">
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
    <div id="reservation-content" style="display: none;">
            <div class="form-card" style="padding: 0; overflow: hidden; border: 1px solid #dce4ff;">
                <div class="reservation-wrapper">
                    <table class="table-reservation">
                        <thead>
                            <tr>
                                <th>Reservation ID</th>
                                <th>User ID</th>
                                <th>Book ID</th>
                                <th>Reservation Date</th>
                                <th>Status</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($data['reservations'])): ?>
                                <?php foreach($data['reservations'] as $res): ?>
                                <tr>
                                    <td>#<?php echo $res->reservation_id; ?></td>
                                    <td class="font-bold"><?php echo $res->member_code; ?></td>
                                    <td><?php echo $res->title; ?></td> 
                                    <td><?php echo date('d/m/Y', strtotime($res->reservation_date)); ?></td>
                                    <td>
                                        <?php 
                                            $c = 'res-waiting';
                                            if($res->status == 'Cancelled') $c = 'res-cancelled';
                                            if($res->status == 'Fulfilled') $c = 'res-fulfilled';
                                        ?>
                                        <span class="res-badge <?php echo $c; ?>">
                                            <?php echo $res->status; ?>
                                        </span>
                                    </td>
                                    <td class="url-text">Null</td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="res-empty">No reservations found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    const tabs = document.querySelectorAll('.tab-link');
    const borrowContent = document.getElementById('borrow-content');
    const reservationContent = document.getElementById('reservation-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            // Nếu nhấn vào tab Reservations
            if (this.textContent.trim() === 'Reservations') {
                e.preventDefault();
                
                // Đổi class active
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                // Ẩn Form mượn, hiện Bảng đặt chỗ
                borrowContent.style.display = 'none';
                reservationContent.style.display = 'block';
            } 
            // Nếu nhấn vào tab Borrow
            else if (this.textContent.trim() === 'Borrow') {
                // Giữ nguyên link gốc để reset lại trạng thái chuẩn
                return; 
            }
        });
    });

    // Nút Refresh giữ nguyên logic cũ
    document.querySelector('.btn-refresh').addEventListener('click', function(e) {
        e.preventDefault();
        window.location.href = "<?php echo URL_ROOT; ?>/admin/loans";
    });
</script>

</body>
</html>