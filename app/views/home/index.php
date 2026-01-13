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
                <li><a href="#" class="active"><i class="fa-solid fa-house"></i> Home</a></li>
                <li class="dropdown">
                    <a href="#"><i class="fa-solid fa-book"></i> Category <i class="fa-solid fa-caret-down"></i></a>
                </li>
                <li><a href="#"><i class="fa-solid fa-user"></i> About us</a></li>
            </ul>
        </div>
    </nav>

    <section class="hero-slider">
        <div class="slider-wrapper">
            <button class="slider-btn prev"><i class="fa-solid fa-chevron-left"></i></button>
        </div>
        <div class="slide-content">
            <img src="<?php echo URL_ROOT; ?>/images/banner-library.jpg" alt="Library Carousel" style="width: 100%; height: auto;">
        </div>
        <div>
            <button class="slider-btn next"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
    </section>

    <section class="filter-section">
        <div class="container">
            <form action="" class="filter-form">
                <div class="form-group">
                    <select name="genre" id="genre">
                        <option value="">All genres</option>
                        <option value="IT">IT</option>
                        <option value="Fiction">Fiction</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="text" placeholder="Enter the year of publication...">
                </div>
                <div class="form-group full-width-group">
                    <input type="text" placeholder="Enter author's name...">
                </div>
                <div class="form-group submit-group"></div>
                <button type="submit" class="btn-confirm">Confirm</button>
            </form>
        </div>
        </div>
    </section>

    <main class="book-list-section">
        <div class="container">
            <h2 class="section-title">All the books in the library</h2>

            <div class="book-grid">
                <?php if (empty($data['books'])): ?>
                    <p>No books found in the library.</p>
                <?php else: ?>
                    <?php foreach ($data['books'] as $book): ?>
                        <div class="book-card">
                            <div class="card-header">
                                <?php if ($book['available_copies'] > 0): ?>
                                    <span class="badge-available">Available</span>
                                <?php else: ?>
                                    <span class="badge-available" style="background: red">Out of stock</span>
                                <?php endif; ?>
                            </div>
                            <div class="book-cover-placeholder">
                                <img src="<?php echo !empty($book['image_url']) ? URL_ROOT . '/images/' . $book['image_url'] : URL_ROOT . '/images/default-book.jpg'; ?>" alt="<?php echo $book['title']; ?>">
                            </div>
                            <div class="card-body">
                                <h3 class="book-title"><?php echo $book['title']; ?></h3>
                                <p class="book-author"><?php echo $book['author']; ?></p>
                                <p class="book-isbn">ISBN: <?php echo $book['isbn']; ?></p>
                            </div>
                            <div class="book-meta">
                                <span><?php echo $book['available_copies']; ?> versions available</span>
                                <span>/<?php echo $book['total_copies']; ?> copies</span>
                            </div>
                            <div class="card-footer">
                                <a href="<?php echo URL_ROOT; ?>/books/detail/<?php echo $book['book_id']; ?>" class="btn-details">See details</a>
                                <button class="btn-reservation">Reservation</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="pagination-container">
                <?php
                $current = $data['pagination']['current_page'];
                $total = $data['pagination']['total_pages'];
                ?>

                <?php if ($current > 1): ?>
                    <a href="?page=<?php echo $current - 1; ?>" class="page-btn prev"><i class="fa-solid fa-chevron-left"></i></a>
                <?php else: ?>
                    <span class="page-btn prev disabled"><i class="fa-solid fa-chevron-left"></i></span>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" class="page-btn <?php echo ($i == $current) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($current < $total): ?>
                    <a href="?page=<?php echo $current + 1; ?>" class="page-btn next"><i class="fa-solid fa-chevron-right"></i></a>
                <?php else: ?>
                    <span class="page-btn next disabled"><i class="fa-solid fa-chevron-right"></i></span>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container footer-content">
            <div class="footer-col about">
                <h3>About the Library</h3>
                <p>The E-Book library is a modern library management system that provides a comprehensive solution for managing books, members, and borrowing and returning activities</p>
            </div>
            <div class="social-links">
                <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#"><i class="fa-brands fa-twitter"></i></a>
                <a href="#"><i class="fa-brands fa-instagram"></i></a>
                <a href="#"><i class="fa-brands fa-youtube"></i></a>
            </div>
        </div>

        <div class="footer-col links">
            <h3>Quick link</h3>
            <ul>
                <li><a href="#">Homepage</a></li>
                <li><a href="#">Booklist</a></li>
                <li><a href="#">Register as a member</a></li>
                <li><a href="#">Instructions for use</a></li>
                <li><a href="#">New & events</a></li>
            </ul>
        </div>

        <div class="footer-col service">
            <h3>Service</h3>
            <ul>
                <li><a href="#">Borrow books online</a></li>
                <li><a href="#">Book reservations</a></li>
                <li><a href="#">Renew membership card</a></li>
                <li><a href="#">Look up documents</a></li>
                <li><a href="#">Notificaitons of overdue books</a></li>
            </ul>
        </div>

        <div class="footer-col contact">
            <h3>Contact</h3>
            <ul>
                <li><i class="fa-solid fa-location-dot"></i> No.XXX, To Hien Thanh Street, Son Tra District, Da Nang</li>
                <li><i class="fa-solid fa-phone"></i> (+84) 123 456 789</li>
                <li><i class="fa-solid fa-envelope"></i> admin@ebookslibrary.com</li>
                <li><i class="fa-solid fa-clock"></i> Monday - Saturday: <strong>8:00 - 17:00</strong></li>
            </ul>
        </div>
    </footer>
</body>

</html>