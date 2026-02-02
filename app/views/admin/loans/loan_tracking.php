<?php require APP_ROOT . '/app/views/admin/inc/header.php'; ?>
<?php 
    $activeTab = 'tracking'; 
    require APP_ROOT . '/app/views/admin/inc/loan_header.php'; 
?>

<div class="main-content">
    <div class="tab-container">
        <a href="<?php echo URL_ROOT; ?>/admin/loans" class="tab-link">Borrow</a>
        <a href="<?php echo URL_ROOT; ?>/admin/returns" class="tab-link">Return</a>
        <a href="<?php echo URL_ROOT; ?>/admin/loan_tracking" class="tab-link active">Loan Tracking</a>
        <a href="<?php echo URL_ROOT; ?>/admin/reservations" class="tab-link">Reservations</a>
    </div>
    <div class="tab-line"></div>

    <div class="loan_tracking-section">
        <table class="loan_tracking-table">
            <thead>
                <tr>
                    <th>Loan ID</th>
                    <th>Member Code</th>
                    <th>Copy ID</th>
                    <th>Timeline (14 Days)</th>
                    <th>Return Date</th>
                    <th>
                        <div class="status-header">
                            <span>Status</span>
                            <select id="statusFilter" onchange="filterTable()">
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
                    <?php foreach ($data['loans'] as $loan): ?>
                        <?php
                            $status = $loan->status;
                            if ($status === 'Active' && strtotime($loan->due_date) < time()) {
                                $status = 'Overdue';
                            }

                            $badgeClass = 'res-waiting';
                            if ($status === 'Returned') $badgeClass = 'res-fulfilled';
                            if ($status === 'Overdue')  $badgeClass = 'res-cancelled';
                        ?>
                        <tr>
                            <td>#<?php echo $loan->loan_id; ?></td>
                            <td class="font-bold"><?php echo $loan->member_code; ?></td>
                            <td><?php echo $loan->copy_id; ?></td>
                            <td class="timeline">
                                Borrowed: <?php echo date('d/m/Y', strtotime($loan->borrow_date)); ?><br>
                                Due: <span class="due-date">
                                    <?php echo date('d/m/Y', strtotime($loan->due_date)); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo $loan->return_date
                                    ? date('d/m/Y', strtotime($loan->return_date))
                                    : '-'; ?>
                            </td>
                            <td>
                                <span class="res-badge <?php echo $badgeClass; ?>">
                                    <?php echo $status; ?>
                                </span>
                            </td>
                            <td class="note">
                                <?php echo !empty($loan->note)
                                    ? $loan->note
                                    : '<span class="note-empty">No note</span>'; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="res-empty">No loan records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function filterTable() {
    const filterValue = document.getElementById('statusFilter').value.toUpperCase();
    const rows = document.querySelectorAll('.loan_tracking-table tbody tr');

    rows.forEach(row => {
        const statusCell = row.children[5];
        if (!statusCell) return;

        const text = statusCell.innerText.toUpperCase();
        row.style.display =
            filterValue === 'ALL' || text.includes(filterValue)
                ? ''
                : 'none';
    });
}
</script>
