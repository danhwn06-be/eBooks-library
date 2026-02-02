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
            <div class="stat-icon <?php echo $item[3]; ?>">
                <i class="<?php echo $item[2]; ?>"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $item[1] ?? 0; ?></h3>
                <p><?php echo $item[0]; ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
