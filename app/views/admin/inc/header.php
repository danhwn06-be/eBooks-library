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

            <nav class="nav-links">
                <a href="#" class="nav-item">
                    <i class="fa-solid fa-users icon-red"></i>
                    <span>Users</span>
                </a>
                <a href="<?php echo URL_ROOT; ?>/admin/books" class="nav-item active">
                    <i class="fa-solid fa-book icon-blue"></i>
                    <span>Books</span>
                </a>
                <a href="#" class="nav-item">
                    <i class="fa-solid fa-handshake icon-red"></i>
                    <span>Loans</span>
                </a>
                <a href="#" class="nav-item">
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
            <a href="<?php echo URL_ROOT; ?>/users/logout" class="btn-logout" style="text-decoration: none; color: inherit;">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                Log out
            </a>
        </div>
    </header>