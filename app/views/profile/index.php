<?php require APP_ROOT . '/app/views/inc/header.php'; ?>

<div class="profile-container">
    <div class="profile-top">
        <!-- LEFT -->
        <div class="profile-card">
            <!-- <img src="/public/images/avatar.png" class="avatar"> -->

            <h3><?= $data['user']['full_name'] ?></h3>
            <p class="bio">Love Cat and Dog</p>

            <div class="profile-stats">
                <div>
                    <strong>Borrowed</strong>
                    <span><?= count($data['history']) ?></span>
                </div>
                <div>
                    <strong>Reading</strong>
                    <span>2</span>
                </div>
            </div>
        </div>
        <!-- RIGHT -->
        <div class="profile-info">
            <div class="info-header">
                <h3>Account Details</h3>
                <a href="/profile/edit" class="btn-edit">Edit profile</a>
            </div>

            <div class="info-grid">
                <div>
                    <label>Email Address</label>
                    <p><?= $user['email'] ?></p>
                </div>

                <div>
                    <label>Member Code</label>
                    <p><?= $data['user']['member_code'] ?></p>
                </div>

                <div>
                    <label>Location</label>
                    <p><?= $data['user']['address'] ?></p>
                </div>

                <div>
                    <label>Password</label>
                    <p>********</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="profile-history">
    <h3>Borrowing and repaying history</h3>

    <table>
        <thead>
            <tr>
                <th>Book ID</th>
                <th>Information</th>
                <th>Borrowed date</th>
                <th>Return date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data['history'] as $item): ?>
                <tr>
                    <td><?= $item['book_code'] ?></td>
                    <td><?= $item['title'] ?></td>
                    <td><?= $item['borrow_date'] ?></td>
                    <td><?= $item['return_date'] ?></td>
                    <td>
                        <a href="/book/detail/<?= $item['book_id'] ?>" class="btn-detail">
                            See Details
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>





<?php require APP_ROOT . "/app/views/inc/footer.php"; ?>