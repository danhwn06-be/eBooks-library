-- Xóa database cũ nếu tồn tại
DROP DATABASE IF EXISTS library_db;
CREATE DATABASE library_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE library_db;

-- =============================================
-- 1. Bảng Categories: Quản lý Danh mục/Thể loại (MỚI)
-- Giúp chuẩn hóa dữ liệu cho TC-01 và TC-07
-- =============================================
CREATE TABLE Categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE, -- Tên thể loại không trùng lặp (VD: Technology, History)
    description TEXT
);

-- =============================================
-- 1. Bảng Users: Quản lý Admin và Member
-- Đáp ứng: TC-02, TC-09, TC-10
-- =============================================
CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    -- Mã thành viên hiển thị (VD: MEM001), tách biệt với user_id hệ thống 
    member_code VARCHAR(20) UNIQUE, 
    
    full_name VARCHAR(100) NOT NULL,            -- [cite: 19]
    email VARCHAR(100) NOT NULL UNIQUE,         -- [cite: 19]
    password_hash VARCHAR(255) NOT NULL,        -- [cite: 78]
    phone_number VARCHAR(20) NOT NULL,          -- [cite: 19]
    address TEXT,                               -- [cite: 19]
    
    -- Phân quyền: Admin (Nhân viên) và Member (Thành viên) [cite: 69, 70]
    role ENUM('Admin', 'Member') DEFAULT 'Member', 
    
    -- Bảo mật: Theo dõi đăng nhập sai và khóa tài khoản [cite: 79]
    failed_login_attempts INT DEFAULT 0,        
    account_locked_until DATETIME NULL,         
    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP, -- Ngày đăng ký [cite: 19]
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =============================================
-- 2. Bảng Books: Metadata đầu sách
-- Đáp ứng: TC-01, TC-07
-- =============================================
CREATE TABLE Books (
    book_id INT AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(20) NOT NULL UNIQUE,           -- [cite: 8]
    title VARCHAR(255) NOT NULL,                -- [cite: 8]
    author VARCHAR(100) NOT NULL,               -- [cite: 8]
    publisher VARCHAR(100),                     -- [cite: 8]
    publication_year INT,                       -- [cite: 8]
    category VARCHAR(50),                       -- [cite: 8]
    image_url VARCHAR(255),
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tạo Index để tối ưu tìm kiếm theo TC-07 
CREATE INDEX idx_books_search ON Books(title, author, category, publication_year);

-- =============================================
-- 3. Bảng BookCopies: Bản sao vật lý
-- Đáp ứng: TC-01
-- =============================================
CREATE TABLE BookCopies (
    copy_id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    -- Mã định danh riêng cho từng bản sao (VD: CP001-BNK) 
    copy_code VARCHAR(50) UNIQUE NOT NULL,      
    
    -- Trạng thái bản sao 
    -- Available: Có sẵn để mượn
    -- Borrowed: Đang được mượn
    -- Lost/Maintenance: Không thể mượn
    status ENUM('Available', 'Borrowed', 'Lost', 'Maintenance') DEFAULT 'Available',
    
    condition_note TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (book_id) REFERENCES Books(book_id) ON DELETE CASCADE
);

-- =============================================
-- 4. Bảng Loans: Quản lý Mượn - Trả
-- Đáp ứng: TC-04, TC-05, TC-06, TC-11
-- =============================================
CREATE TABLE Loans (
    loan_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    copy_id INT NOT NULL,
    
    borrow_date DATETIME DEFAULT CURRENT_TIMESTAMP, -- Ngày mượn 
    due_date DATETIME NOT NULL,                     -- Hạn trả (Default +14 ngày) [cite: 35]
    return_date DATETIME NULL,                      -- Ngày trả thực tế [cite: 44]
    
    -- Trạng thái phiếu mượn
    -- Active: Đang mượn, Returned: Đã trả, Overdue: Quá hạn (Tính toán logic khi query)
    status ENUM('Active', 'Returned') DEFAULT 'Active',
    note TEXT,
    
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (copy_id) REFERENCES BookCopies(copy_id)
);

-- =============================================
-- 5. Bảng Reservations: Đặt chỗ
-- Đáp ứng: TC-13
-- =============================================
CREATE TABLE Reservations (
    reservation_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    reservation_date DATETIME DEFAULT CURRENT_TIMESTAMP, -- Ghi nhận thứ tự đặt [cite: 103]
    
    -- Waiting: Đang chờ, Fulfilled: Đã nhận sách, Cancelled: Hủy
    status ENUM('Waiting', 'Fulfilled', 'Cancelled') DEFAULT 'Waiting',
    
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (book_id) REFERENCES Books(book_id)
);

-- =============================================
-- 7. Bảng Notifications: Lưu thông báo người dùng
-- Đáp ứng: TC-11 (Nhắc hạn), TC-13 (Báo sách đã có)
-- =============================================
CREATE TABLE Notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,               -- Thông báo cho ai?
    message TEXT NOT NULL,              -- Nội dung thông báo
    
    -- Loại thông báo để hiển thị màu sắc (Info=Xanh, Warning=Vàng, Alert=Đỏ)
    type ENUM('Info', 'Warning', 'Alert') DEFAULT 'Info',
    
    is_read BOOLEAN DEFAULT FALSE,      -- Đã xem chưa?
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

-- =============================================
-- 6. SEED DATA (Dữ liệu mẫu)
-- =============================================

-- Thêm Users (Admin & Member)
INSERT INTO Users (member_code, full_name, email, password_hash, phone_number, role) VALUES 
('ADMIN01', 'Quản Trị Viên', 'admin@library.com', 'hash_pass', '0909000111', 'Admin'),
('MEM0001', 'Nguyễn Văn A', 'member1@email.com', 'hash_pass', '0909000222', 'Member'),
('MEM0002', 'Trần Thị B', 'member2@email.com', 'hash_pass', '0909000333', 'Member');

-- Thêm Books
INSERT INTO Books (isbn, title, author, publisher, publication_year, category) VALUES 
('978-0132350884', 'Clean Code', 'Robert C. Martin', 'Prentice Hall', 2008, 'IT'),
('978-0201633610', 'Design Patterns', 'Erich Gamma', 'Addison-Wesley', 1994, 'IT');

-- Thêm BookCopies
INSERT INTO BookCopies (book_id, copy_code, status) VALUES 
(1, 'CC-001', 'Available'),
(1, 'CC-002', 'Available'),
(2, 'DP-001', 'Available');