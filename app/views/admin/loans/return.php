<?php require APP_ROOT . '/app/views/admin/inc/header.php'; ?>

<div class="main-content">
    <?php if(isset($_GET['return_success'])): ?>
        <div class="alert alert-success">Book returned successfully!</div>
    <?php endif; ?>

    <div class="tab-container">
        <a href="<?php echo URL_ROOT; ?>/admin/loans" class="tab-link">Borrow</a>
        <a href="<?php echo URL_ROOT; ?>/admin/returns" class="tab-link active">Return</a>
        <a href="<?php echo URL_ROOT; ?>/admin/loan_tracking" class="tab-link">Loan Tracking</a>
        <a href="#" class="tab-link">Reservations</a>
    </div>
    <div class="tab-line"></div>

    <div class="form-card">
        <form action="<?php echo URL_ROOT; ?>/admin/returns" method="POST" id="returnForm">
            <div class="form-grid">

                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Member Code</label>
                        <input type="text" name="member_code" id="member_code" class="form-input" 
                               placeholder="e.g. MB001..." required>
                        <small id="member_msg" class="form-note">* The system will check active loans.</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Return Date</label>
                        <input type="date" name="return_date" class="form-input"
                               value="<?php echo $data['current_date']; ?>" readonly>
                    </div>
                </div>

                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Book to Return</label>
                        <select name="loan_id" id="loan_select" class="form-input" required disabled>
                            <option value="">-- Enter Member Code First --</option>
                        </select>
                        <input type="hidden" name="copy_id" id="copy_id">
                        <small class="form-note">* Select the book being returned.</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Due Date (From Borrowing)</label>
                        <input type="date" id="due_date_display" class="form-input" readonly>
                    </div>
                </div>
            </div>

            <div class="form-group" style="margin-top: 20px;">
                <label class="form-label">Note</label>
                <textarea name="note" class="form-input" rows="4" placeholder="Additional notes..."><?php echo isset($data['note']) ? $data['note'] : ''; ?></textarea>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-refresh" onclick="location.reload()">Refresh</button>
                <button type="submit" class="btn btn-confirm" id="btn_submit" disabled>Confirm Return</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('member_code').addEventListener('change', function() {
    const code = this.value.trim();
    const loanSelect = document.getElementById('loan_select');
    const msg = document.getElementById('member_msg');

    if (code === '') return;

    fetch(`<?php echo URL_ROOT; ?>/admin/getMemberLoans/${code}`)
        .then(res => res.json())
        .then(data => {
            loanSelect.innerHTML = '<option value="">-- Select Borrowed Book --</option>';
            
            if (data.length > 0) {
                data.forEach(loan => {
                    // Cắt chuỗi ngày tháng để loại bỏ giờ (chỉ lấy 10 ký tự đầu: YYYY-MM-DD)
                    // Ví dụ: "2026-02-11 14:00:00" -> "2026-02-11"
                    let safeDate = loan.due_date ? loan.due_date.substring(0, 10) : '';

                    loanSelect.innerHTML += `<option value="${loan.loan_id}" 
                                                data-copy="${loan.copy_id}" 
                                                data-due="${safeDate}">
                                                ${loan.title} (ID: ${loan.copy_id})
                                             </option>`;
                });
                loanSelect.disabled = false;
                msg.innerText = `Found ${data.length} active loan(s).`;
                msg.style.color = "green";
            } else {
                loanSelect.disabled = true;
                msg.innerText = "No active loans found for this member!";
                msg.style.color = "red";
                // Reset các ô input nếu không tìm thấy
                document.getElementById('due_date_display').value = "";
                document.getElementById('btn_submit').disabled = true;
            }
        })
        .catch(err => console.error("Error fetching loans:", err));
});

document.getElementById('loan_select').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    
    if (selectedOption.value !== "") {
        const dueDate = selectedOption.getAttribute('data-due');
        
        console.log("Selected Due Date:", dueDate);

        document.getElementById('due_date_display').value = dueDate;
        document.getElementById('copy_id').value = selectedOption.getAttribute('data-copy');
        document.getElementById('btn_submit').disabled = false;
    } else {
        document.getElementById('due_date_display').value = "";
        document.getElementById('copy_id').value = "";
        document.getElementById('btn_submit').disabled = true;
    }
});
</script>