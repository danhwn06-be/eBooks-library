<?php require APP_ROOT . '/app/views/admin/inc/header.php'; ?>
<?php 
    $activeTab = 'tracking'; 
    require APP_ROOT . '/app/views/admin/inc/loan_header.php'; 
?>
<div class="main-content">

    <div class="tab-container">
        <a href="<?php echo URL_ROOT; ?>/admin/loans" class="tab-link">Borrow</a>
        <a href="<?php echo URL_ROOT; ?>/admin/returns" class="tab-link">Return</a>
        <a href="<?php echo URL_ROOT; ?>/admin/loan_tracking" class="tab-link">Loan Tracking</a>
        <a href="<?php echo URL_ROOT; ?>/admin/reservations" class="tab-link active">Reservations</a>
    </div>
    <div class="tab-line"></div>

    <div class="form-card" style="padding: 0; overflow: hidden; border: 1px solid #dce4ff;">
        <div class="reservation-wrapper">
            <table class="table-reservation">
                <thead>
                    <tr>
                        <th>Reservation ID</th>
                        <th>Member Code</th>
                        <th>Book</th>
                        <th>Reservation Date</th>
                        <th>Status</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['reservations'])): ?>
                        <?php foreach ($data['reservations'] as $res): ?>
                            <tr>
                                <td>#<?php echo $res->reservation_id; ?></td>
                                <td class="font-bold"><?php echo $res->member_code; ?></td>
                                <td><?php echo $res->title; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($res->reservation_date)); ?></td>
                                <td>
                                    <?php
                                        $c = 'res-waiting';
                                        if ($res->status === 'Cancelled') $c = 'res-cancelled';
                                        if ($res->status === 'Fulfilled') $c = 'res-fulfilled';
                                    ?>
                                    <span class="res-badge <?php echo $c; ?>">
                                        <?php echo $res->status; ?>
                                    </span>
                                </td>
                                <td><?php echo $res->note ?? '—'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="res-empty">No reservations found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>
