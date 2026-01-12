# eBooks Library Management System

A library management system built with **native PHP** following the **MVC (Model-View-Controller)** pattern. This project provides an efficient solution for managing books, members, and the borrowing/returning process.

## Introduction

**eBooks-library** is a lightweight web application, independent of large frameworks, designed to help learners understand the internal workings of the MVC pattern, PDO, and relational database structures.

## Project Team

*   **Hồ Văn Đàn**
*   **Nguyễn Hữu Tổng Đạt**
*   **Hồ Thị Như**
*   **Hồ Văn Tiết**

## Key Features

*   **Inventory Management:** Managing book titles, adding/editing/deleting copies, and tracking stock status.
*   **Member Management:** Creating new member accounts, updating profile information, and managing membership status.
*   **Circulation Management:** Handling the borrowing and returning of books, including validation rules.
*   **Public Catalog & Discovery:** Search functionality, category filtering, and displaying book details for all users.
*   **Member Portal:** A dedicated area for members to view their profile, borrowing history, and change passwords.
*   **Book Reservation System:** Handling "out-of-stock" situations, managing waitlists/queues, and notifications.
*   **Overdue Management & Notifications:** Tracking overdue items, calculating days late, and sending reminder alerts.
*   **Reports & Analytics:** Dashboard overview and detailed statistical reports on borrowing trends and new members.
*   **System Administration & Security:** Authentication, role-based authorization [Admin vs. Member], and session management.

## Technologies Used

*   **Language:** PHP (MVC Pattern).
*   **Database:** MySQL (Using PDO & Singleton Pattern).
*   **Frontend:** HTML, CSS, JS.
*   **Server:** Apache (XAMPP).

## Folder Structure

```text
eBooks-library/
├── app/
│   ├── controllers/    # Logic handling (BookController, etc.)
│   ├── core/           # Core classes (App, Controller, Database Singleton)
│   ├── models/         # Database interactions
│   └── views/          # User Interface
├── config/             # System configuration (config.php)
├── database/           # Database creation script (schema.sql)
├── public/             # Public resources (CSS, JS, Images)
└── index.php           # Entry point
```

## Installation Guide

1.  **Clone the Project:**
    Place the project folder into your Web Server's root directory (e.g., `c:\xampp\htdocs\eBooks-library`).

2.  **Database Configuration:**
    *   Open phpMyAdmin or your MySQL management tool.
    *   Run the script `database/schema.sql` to create the `library_db` database, tables, and seed data.

3.  **Application Configuration:**
    *   Open the file `config/config.php`.
    *   Check and modify the connection details if necessary:
        ```php
        define('DB_HOST', 'localhost');
        define('DB_USER', 'root');
        define('DB_PASS', ''); // Your MySQL password
        define('DB_NAME', 'library_db');
        ```
    *   Ensure `URL_ROOT` points to the correct path on your browser (e.g., `/eBooks-library/public`).

4.  **Run the Application:**

    *   Open your browser and navigate to: `http://localhost/eBooks-library`



