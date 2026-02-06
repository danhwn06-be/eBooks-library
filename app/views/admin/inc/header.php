<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>

<body>
    <header class="navbar">
        <div class="nav-left">
            <div class="logo-area">
                <div class="logo-text">eBooks Library</div>
                <i class="fa-solid fa-book-open logo-icon"></i>
            </div>

            <?php
                $current_url = $_SERVER['REQUEST_URI'];
                // Kiểm tra xem có phải trang User không
                $is_user_page = (strpos($current_url, 'admin/users') !== false || strpos($current_url, 'editUser') !== false || strpos($current_url, 'addUser') !== false);
                // Kiểm tra xem có phải trang Book không (loại trừ trường hợp có chữ User)
                $is_book_page = (strpos($current_url, 'admin/books') !== false || (strpos($current_url, 'admin/edit') !== false && strpos($current_url, 'editUser') === false) || strpos($current_url, 'admin/add') !== false);
                $is_loan_page = (strpos($current_url, 'admin/loans') !== false);
            ?>

            <nav class="nav-links">
                <a href="<?php echo URL_ROOT; ?>/admin/users" class="nav-item <?php echo $is_user_page ? 'active' : ''; ?>">
                    <i class="fa-solid fa-users icon-red"></i>
                    <span>Users</span>
                </a>

                <a href="<?php echo URL_ROOT; ?>/admin/books" class="nav-item <?php echo $is_book_page ? 'active' : ''; ?>">
                    <i class="fa-solid fa-book icon-blue"></i>
                    <span>Books</span>
                </a>

                <a href="<?php echo URL_ROOT; ?>/admin/loans" class="nav-item <?php echo $is_loan_page ? 'active' : ''; ?>">
                    <i class="fa-solid fa-handshake icon-red"></i>
                    <span>Loans</span>
                </a>

                <a href="#" class="nav-item <?php echo (strpos($current_url, 'notice') !== false) ? 'active' : ''; ?>">
                    <i class="fa-solid fa-bell icon-red"></i>
                    <span>Notice</span>
                </a>
            </nav>
        </div>

        <div class="nav-right">
            <div class="user-info">
                <i class="fa-solid fa-circle-user user-avatar"></i>
                <span class="greeting">👋 Hi, <span class="admin-name">Admin Library!</span></span>
            </div>
            <a href="<?php echo URL_ROOT; ?>/user/logout" class="btn-logout" style="text-decoration: none; color: inherit;">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                Log out
            </a>
        </div>
    </header>