<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $data['title']; ?> - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="top-bar">
        <div class="container">
            <div class="contact-info">
                <span><i class="fa-solid fa-phone"></i> (+84) 123 456 789</span>
                <span><i class="fa-solid fa-envelope"></i> admin@ebookslibrary.com</span>
                <span><i class="fa-solid fa-clock"></i> 8:00 - 17:00 (Monday - Saturday)</span>
            </div>
            <div class="support-links">
                <a href="#"><i class="fa-solid fa-circle-question"></i>Support</a>
                <a href="#"><i class="fa-solid fa-location-dot"></i>Address</a>
            </div>
        </div>
    </div>

    <header class="main-header">
        <div class="container header-content">
            <div class="logo">
                <a href="<?php echo URL_ROOT; ?>" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;">
                    <h1>eBook library</h1>
                    <i class="fa-solid fa-book-open logo-icon"></i>
                </a>
            </div>

            <div class="search-bar">
                <form action="<?php echo URL_ROOT; ?>/book/search" method="GET">
                    <input type="text" name="q" placeholder="Search for books, author and ISBNs...">
                    <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                </form>
            </div>

            <div class="auth-buttons">
                <a href="<?php echo URL_ROOT; ?>/users/login" class="btn btn-outline"><i class="fa-solid fa-right-to-bracket"></i> Log in</a>
                <a href="<?php echo URL_ROOT; ?>/users/register" class="btn btn-primary">Register</a>
            </div>
        </div>
    </header>

    <nav class="main-nav">
        <div class="container">
            <ul>
                <li><a href="<?php echo URL_ROOT; ?>" class="<?php echo ($data['current_page'] == 'home') ? 'active' : ''; ?>"><i class="fa-solid fa-house"></i> Home</a></li>
                <li class="dropdown">
                    <a href=""><i class="fa-solid fa-book"></i> Category <i class="fa-solid fa-caret-down"></i></a>
                </li>
                <li><a href="#"><i class="fa-solid fa-user"></i> About us</a></li>
            </ul>
        </div>
    </nav>