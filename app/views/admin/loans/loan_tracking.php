<?php require APP_ROOT . '/app/views/admin/inc/header.php'; ?>

<div class="container-admin">
    <div class="stats-row">
        <?php 
            $stat_items = [
                ['Total Loans', $data['stats']['total'], 'fas fa-book-open-reader icon-blue'],
                ['Overdue', $data['stats']['overdue'], 'far fa-clock icon-red'],
                ['Reservations', $data['stats']['reservations'], 'fas fa-user-clock icon-black']
            ];
            foreach($stat_items as $item): 
        ?>
        <div class="stat-box">
            <div class="stat-icon <?php echo $item[3]; ?>"><i class="<?php echo $item[2]; ?>"></i></div>
            <div class="stat-info">
                <h3><?php echo $item[1] ?? 0; ?></h3>
                <p><?php echo $item[0]; ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="tab-container">
        <a href="<?php echo URL_ROOT; ?>/admin/loans" class="tab-link">Borrow</a>
        <a href="<?php echo URL_ROOT; ?>/admin/returns" class="tab-link">Return</a>
        <a href="<?php echo URL_ROOT; ?>/admin/loan_tracking" class="tab-link active">Loan Tracking</a>
        <a href="#" class="tab-link">Reservations</a>
    </div>
    <div class="tab-line"></div>

    <div class="lao-section">
            <table class="loan_tracking-table">
                <thead>
                    <tr>
                        <th>Loan ID</th>
                        <th>Member Code</th>
                        <th>Copy ID</th>
                        <th>Timeline (14 Days)</th>
                        <th>Return Date</th>
                        <th style="padding: 5px 8px !important; vertical-align: middle;">
                            <div style="display: flex !important; align-items: center !important; gap: 4px !important; flex-direction: row !important; flex-wrap: nowrap !important;">
                                <span style="font-size: 12px !important; font-weight: bold !important; white-space: nowrap !important;">Status</span>
                                <select id="statusFilter" onchange="filterTable()" 
                                        style="padding: 0 2px !important; 
                                            height: 20px !important; 
                                            font-size: 11px !important; 
                                            width: 75px !important; 
                                            border: 1px solid #ccc !important; 
                                            border-radius: 3px !important;
                                            background-color: #fff !important;
                                            cursor: pointer !important;
                                            display: inline-block !important;
                                            line-height: 1 !important;">
                                    <option value="all">All</option>
                                    <option value="Active">Active</option>
                                    <option value="Returned">Returned</option>
                                    <option value="Overdue">Overdue</option>
                                </select>
                            </div>
                        </th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['loans'])): ?>
                        <?php foreach($data['loans'] as $loan): ?>
                        <tr>
                            <td>#<?php echo $loan->loan_id; ?></td>
                            <td style="font-weight: 600;"><?php echo $loan->member_code; ?></td>
                            <td><?php echo $loan->copy_id; ?></td>
                            <td style="font-size: 0.85em;">
                                Borrowed: <?php echo date('d/m/Y', strtotime($loan->borrow_date)); ?><br>
                                Due: <span style="color: #e67e22;"><?php echo date('d/m/Y', strtotime($loan->due_date)); ?></span>
                            </td>
                            <td><?php echo $loan->return_date ? date('d/m/Y', strtotime($loan->return_date)) : '-'; ?></td>
                            <td>
                                <?php 
                                    $status = $loan->status;
                                    if($status == 'Active' && strtotime($loan->due_date) < time()) $status = 'Overdue';
                                    
                                    $badgeClass = 'res-waiting'; 
                                    if($status == 'Returned') $badgeClass = 'res-fulfilled';
                                    if($status == 'Overdue') $badgeClass = 'res-cancelled';
                                ?>
                                <span class="res-badge <?php echo $badgeClass; ?>"><?php echo $status; ?></span>
                            </td>
                            <td style="max-width: 200px; font-style: italic; color: #666;">
                                <?php echo !empty($loan->note) ? $loan->note : '<span style="color: #ccc;">No note</span>'; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="res-empty">No loan records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function filterTable() {
        // Lấy giá trị từ thanh chọn
        const filterValue = document.getElementById('statusFilter').value.toUpperCase();
        
        // Lấy tất cả hàng trong bảng (trừ hàng tiêu đề)
        const table = document.querySelector(".loan_tracking-table");
        const tr = table.getElementsByTagName("tr");

        for (let i = 1; i < tr.length; i++) {
            // Cột Status nằm ở vị trí thứ 6 (index 5)
            const td = tr[i].getElementsByTagName("td")[5]; 
            
            if (td) {
                const txtValue = td.textContent || td.innerText;
                
                // Nếu chọn "All" hoặc giá trị khớp với nội dung trong cột
                if (filterValue === "ALL" || txtValue.toUpperCase().indexOf(filterValue) > -1) {
                    tr[i].style.display = ""; // Hiện hàng
                } else {
                    tr[i].style.display = "none"; // Ẩn hàng
                }
            }
        }
    }
</script>
