<?php require APP_ROOT . '/app/views/admin/inc/header.php'; ?>

<main class="container">
    <div class="page-header">
        <h1>User management</h1>
        <a href="<?php echo URL_ROOT; ?>/admin/addUser" style="text-decoration: none;">
            <button class="btn-add">
                <i class="fa-solid fa-plus"></i>
                Add new users
            </button>
        </a>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Member code</th>
                    <th>Full name</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Phone number</th>
                    <th>Update at</th>
                    <th>Created at</th>
                    <th>Operation</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data['users'])): ?>
                    <?php foreach ($data['users'] as $user): ?>
                        <tr>
                            <td class="font-bold">
                                <?php echo $user['member_code'] ?? 'MB' . str_pad($user['user_id'], 3, '0', STR_PAD_LEFT); ?>
                            </td>
                            <td class="font-bold"><?php echo htmlspecialchars($user['full_name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($user['email'] ?? ''); ?></td>
                            
                            <td><?php echo htmlspecialchars($user['address'] ?? 'No Address'); ?></td>
                            
                            <td><?php echo htmlspecialchars($user['phone_number'] ?? ''); ?></td>
                            <td><?php echo $user['updated_at'] ?? 'N/A'; ?></td>
                            <td><?php echo $user['created_at']; ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="<?php echo URL_ROOT; ?>/admin/editUser/<?php echo $user['user_id']; ?>" class="btn-edit">
                                        <i class="fa-regular fa-pen-to-square"></i> Edit
                                    </a>

                                    <a href="<?php echo URL_ROOT; ?>/admin/deleteUser/<?php echo $user['user_id']; ?>"
                                       class="btn-delete"
                                       onclick="return confirm('Are you sure you want to delete this user?');">
                                        <i class="fa-regular fa-trash-can"></i> Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" style="text-align: center;">No users found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>