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

    <main class="container">
        <div class="page-header">
            <h1>Book management</h1>
            <a href="<?php echo URL_ROOT; ?>/admin/add" style="text-decoration: none;">
                <button class="btn-add">
                    <i class="fa-solid fa-plus"></i>
                    Add new books
                </button>
            </a>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Book ID</th>
                        <th>ISBN</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Image</th>
                        <th>Publisher</th>
                        <th>Publication year</th>
                        <th>Created at</th>
                        <th>Content description</th>
                        <th>Number of copies</th>
                        <th>Operation</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['books'])): ?>
                        <?php foreach ($data['books'] as $book): ?>
                            <tr>
                                <td><?php echo $book['book_id']; ?></td>
                                <td><?php echo $book['isbn']; ?></td>
                                <td class="font-bold"><?php echo htmlspecialchars($book['title']); ?></td>
                                <td><?php echo htmlspecialchars($book['author']); ?></td>
                                <td><?php echo htmlspecialchars($book['category_name']); ?></td>
                                <td class="url-text">
                                    <?php if ($book['image_url']): ?>
                                        <img src="<?php echo URL_ROOT . '/images/books/' . $book['image_url']; ?>" alt="Cover" style="width: 40px; height: auto;">
                                    <?php else: ?>
                                        <span>No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($book['publisher']); ?></td>
                                <td><?php echo $book['publication_year']; ?></td>
                                <td><?php echo date('Y-m-d', strtotime($book['created_at'])); ?></td>
                                <td class="desc-text">
                                    <?php echo (strlen($book['description']) > 50) ? substr(htmlspecialchars($book['description']), 0, 50) . '...' : htmlspecialchars($book['description']); ?>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge-copies">
                                        <i class="fa-solid fa-eye"></i>
                                        <?php echo $book['total_copies']; ?> copies
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons" style="display: flex; gap: 10px;">
                                        <a class="btn-edit" href="<?php echo URL_ROOT; ?>/admin/edit/<?php echo $book['book_id']; ?>" title="Edit">
                                            <i class="fa-regular fa-pen-to-square" style="color: white"></i> Edit
                                        </a>
                                        <a class="btn-delete" href="<?php echo URL_ROOT; ?>/admin/delete/<?php echo $book['book_id']; ?>" title="Delete" onclick="return confirm('Delete this book?');">
                                            <i class="fa-regular fa-trash-can" style="color: white;"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="12" style="text-align: center;">No books found in database</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>

</html>