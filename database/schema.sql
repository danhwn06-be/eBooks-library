-- Active: 1768276078050@@localhost@3306@library_db
-- Xóa database cũ nếu tồn tại
DROP DATABASE IF EXISTS library_db;
CREATE DATABASE IF NOT EXISTS library_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE library_db;

-- =============================================
-- I. DATABASE SCHEMA
-- =============================================

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
    member_code VARCHAR(20) UNIQUE PRIMARY KEY, 
    
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
    isbn VARCHAR(20) NOT NULL UNIQUE, -- [cite: 8]
    title VARCHAR(255) NOT NULL, -- [cite: 8]
    author VARCHAR(100) NOT NULL, -- [cite: 8]
    publisher VARCHAR(100), -- [cite: 8]
    publication_year INT, -- [cite: 8]
    category_id INT, -- [cite: 8]
    image_url VARCHAR(255),
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES Categories (category_id) ON DELETE SET NULL
);

-- Tạo Index để tối ưu tìm kiếm theo TC-07
CREATE INDEX idx_books_search ON Books (
    title,
    author,
    category_id,
    publication_year
);

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
-- II. DATA
-- =============================================

-- Categories (gốc)
INSERT INTO Categories (category_name, description) VALUES 
('Science Fiction', 'Speculative fiction dealing with imaginative concepts such as futuristic science, space travel, time travel, faster-than-light travel, parallel universes, and extraterrestrial life.'),
('Mystery & Thriller', 'Genre of fiction in which a detective, either an amateur or a professional, solves a crime or a series of crimes.'),
('Fantasy', 'Stories set in an imaginary universe, often but not always without any locations, events, or people from the real world, featuring magic and mythical creatures.'),
('Romance', 'Novels that focus on a romantic relationship between two or more people, usually with an emotionally satisfying and optimistic ending.'),
('Historical Fiction', 'A literary genre in which the plot takes place in a setting located in the past, capturing the details of the time period accurately.'),
('Horror', 'Fiction intended to scare, unsettle, or horrify the reader, often including supernatural or psychological elements.'),
('Biography & Memoir', 'A detailed description of a person''s life. Biographies are written by someone else, while memoirs are written by the subject themselves.'),
('History', 'Non-fiction books that record, analyze, and interpret past events, societies, and civilizations.'),
('Science & Technology', 'Works covering scientific principles, technological advancements, computer science, engineering, and the natural world.'),
('Business & Economics', 'Books focused on commerce, finance, management, entrepreneurship, and economic theory.'),
('Self-Help', 'Non-fiction books written with the intention to instruct its readers on solving personal problems and improving aspects of their lives.'),
('Children''s Literature', 'Books intended for children and young people, ranging from picture books for toddlers to novels for young adults.');
-- Thêm Users (Admin & Member)
INSERT INTO
    Users (
        member_code,
        full_name,
        email,
        password_hash,
        phone_number,
        role
    )
VALUES (
        'ADMIN01',
        'Quản Trị Viên',
        'admin@library.com',
        'hash_pass',
        '0909000111',
        'Admin'
    ),
    (
        'MEM0001',
        'Nguyễn Văn A',
        'member1@email.com',
        'hash_pass',
        '0909000222',
        'Member'
    ),
    (
        'MEM0002',
        'Trần Thị B',
        'member2@email.com',
        'hash_pass',
        '0909000333',
        'Member'
    );

-- Books (gốc)
INSERT INTO Books (book_id, isbn, title, author, publisher, publication_year, category_id, description, image_url) VALUES 
-- ========================================================
-- KHOA HỌC VIỄN TƯỞNG (Science Fiction - ID: 1)
-- ========================================================
(3, '978-0441013593', 'Dune', 'Frank Herbert', 'Ace', 1965, 1, 'Bối cảnh tại hành tinh sa mạc Arrakis...', 'dune.jpg'), -- Nguồn: https://www.goodreads.com/book/show/44767458-dune
(4, '978-0451524935', '1984', 'George Orwell', 'Signet Classic', 1949, 1, 'Xã hội giả tưởng dưới sự giám sát của Big Brother.', '1984.jpg'), -- Nguồn: https://www.goodreads.com/book/show/61439040-1984
(5, '978-0553380163', 'A Brief History of Time', 'Stephen Hawking', 'Bantam', 1988, 1, 'Khám phá về vũ trụ, lỗ đen và thời gian.', 'brief_history.jpg'), -- Nguồn: https://www.goodreads.com/book/show/3869.A_Brief_History_of_Time
(6, '978-0425211159', 'The Martian', 'Andy Weir', 'Crown', 2011, 1, 'Cuộc sinh tồn đơn độc trên sao Hỏa.', 'the_martian.jpg'), -- Nguồn: https://www.goodreads.com/book/show/18007564-the-martian
(7, '978-0345391803', 'The Hitchhiker''s Guide to the Galaxy', 'Douglas Adams', 'Del Rey', 1979, 1, 'Chuyến du hành vũ trụ hài hước và điên rồ.', 'hitchhiker.jpg'), -- Nguồn: https://www.goodreads.com/book/show/386162.The_Hitchhiker_s_Guide_to_the_Galaxy

-- ========================================================
-- TRINH THÁM & LY KỲ (Mystery & Thriller - ID: 2)
-- ========================================================
(8, '978-0307588371', 'Gone Girl', 'Gillian Flynn', 'Crown', 2012, 2, 'Sự mất tích bí ẩn của người vợ vào kỷ niệm ngày cưới.', 'gone_girl.jpg'), -- Nguồn: https://www.goodreads.com/book/show/19288043-gone-girl
(9, '978-1250301697', 'The Silent Patient', 'Alex Michaelides', 'Celadon Books', 2019, 2, 'Người phụ nữ bắn chồng rồi im lặng mãi mãi.', 'silent_patient.jpg'), -- Nguồn: https://www.goodreads.com/book/show/40097951-the-silent-patient
(10, '978-0307277671', 'The Da Vinci Code', 'Dan Brown', 'Anchor', 2003, 2, 'Mật mã bí ẩn đằng sau các tác phẩm của Da Vinci.', 'davinci_code.jpg'), -- Nguồn: https://www.goodreads.com/book/show/968.The_Da_Vinci_Code
(11, '978-1594480003', 'The Girl with the Dragon Tattoo', 'Stieg Larsson', 'Vintage', 2005, 2, 'Nữ hacker và nhà báo điều tra vụ mất tích 40 năm trước.', 'dragon_tattoo.jpg'), -- Nguồn: https://www.goodreads.com/book/show/2429135.The_Girl_with_the_Dragon_Tattoo
(12, '978-0062068248', 'And Then There Were None', 'Agatha Christie', 'St. Martin''s Press', 1939, 2, '10 người lạ trên đảo hoang và từng người bị sát hại.', 'then_there_were_none.jpg'), -- Nguồn: https://www.goodreads.com/book/show/16299.And_Then_There_Were_None

-- ========================================================
-- GIẢ TƯỞNG (Fantasy - ID: 3)
-- ========================================================
(13, '978-0590353427', 'Harry Potter and the Sorcerer''s Stone', 'J.K. Rowling', 'Scholastic', 1997, 3, 'Cậu bé phù thủy và hòn đá phù thủy.', 'harry_potter_1.jpg'), -- Nguồn: https://www.goodreads.com/book/show/3.Harry_Potter_and_the_Sorcerer_s_Stone
(14, '978-0547928227', 'The Hobbit', 'J.R.R. Tolkien', 'Houghton Mifflin', 1937, 3, 'Hành trình của Bilbo Baggins giành lại kho báu.', 'the_hobbit.jpg'), -- Nguồn: https://www.goodreads.com/book/show/5907.The_Hobbit
(15, '978-0553103540', 'A Game of Thrones', 'George R.R. Martin', 'Bantam', 1996, 3, 'Cuộc chiến vương quyền tại Westeros.', 'game_of_thrones.jpg'), -- Nguồn: https://www.goodreads.com/book/show/13496.A_Game_of_Thrones
(16, '978-0756404079', 'The Name of the Wind', 'Patrick Rothfuss', 'DAW', 2007, 3, 'Hồi ức của kẻ sát thần Kvothe.', 'name_of_wind.jpg'), -- Nguồn: https://www.goodreads.com/book/show/186074.The_Name_of_the_Wind

-- ========================================================
-- LÃNG MẠN (Romance - ID: 4)
-- ========================================================
(17, '978-0141439518', 'Pride and Prejudice', 'Jane Austen', 'Penguin Classics', 1813, 4, 'Câu chuyện tình yêu và định kiến giai cấp.', 'pride_prejudice.jpg'), -- Nguồn: https://www.goodreads.com/book/show/1885.Pride_and_Prejudice
(18, '978-1455582877', 'The Notebook', 'Nicholas Sparks', 'Grand Central', 1996, 4, 'Câu chuyện tình yêu vượt thời gian.', 'the_notebook.jpg'), -- Nguồn: https://www.goodreads.com/book/show/15931.The_Notebook
(19, '978-0142410707', 'The Fault in Our Stars', 'John Green', 'Penguin Books', 2012, 4, 'Tình yêu của hai bệnh nhân ung thư trẻ tuổi.', 'fault_stars.jpg'), -- Nguồn: https://www.goodreads.com/book/show/11870085-the-fault-in-our-stars
(20, '978-0385319959', 'Outlander', 'Diana Gabaldon', 'Delacorte Press', 1991, 4, 'Xuyên không về Scotland thế kỷ 18.', 'outlander.jpg'), -- Nguồn: https://www.goodreads.com/book/show/10964.Outlander

-- ========================================================
-- TIỂU THUYẾT LỊCH SỬ (Historical Fiction - ID: 5)
-- ========================================================
(21, '978-0375842207', 'The Book Thief', 'Markus Zusak', 'Knopf', 2005, 5, 'Cô bé ăn trộm sách ở Đức thời Đức Quốc xã.', 'book_thief.jpg'), -- Nguồn: https://www.goodreads.com/book/show/19063.The_Book_Thief
(22, '978-1476746586', 'All the Light We Cannot See', 'Anthony Doerr', 'Scribner', 2014, 5, 'Cô gái mù Pháp và chàng lính Đức trong thế chiến II.', 'all_the_light.jpg'), -- Nguồn: https://www.goodreads.com/book/show/18143977-all-the-light-we-cannot-see
(23, '978-0312577223', 'The Nightingale', 'Kristin Hannah', 'St. Martin''s Griffin', 2015, 5, 'Hai chị em gái ở Pháp thời bị chiếm đóng.', 'nightingale.jpg'), -- Nguồn: https://www.goodreads.com/book/show/21853621-the-nightingale
(24, '978-1594487361', 'The Kite Runner', 'Khaled Hosseini', 'Riverhead Books', 2003, 5, 'Tình bạn và sự chuộc lỗi ở Afghanistan.', 'kite_runner.jpg'), -- Nguồn: https://www.goodreads.com/book/show/77203.The_Kite_Runner

-- ========================================================
-- KINH DỊ (Horror - ID: 6)
-- ========================================================
(25, '978-1501142970', 'It', 'Stephen King', 'Scribner', 1986, 6, 'Gã hề ma quái ám ảnh thị trấn Derry.', 'it.jpg'), -- Nguồn: https://www.goodreads.com/book/show/830502.It
(26, '978-0486411095', 'Dracula', 'Bram Stoker', 'Dover Publications', 1897, 6, 'Bá tước ma cà rồng huyền thoại.', 'dracula.jpg'), -- Nguồn: https://www.goodreads.com/book/show/17245.Dracula
(27, '978-0307743657', 'The Shining', 'Stephen King', 'Anchor', 1977, 6, 'Khách sạn ma ám và sự điên loạn.', 'shining.jpg'), -- Nguồn: https://www.goodreads.com/book/show/11588.The_Shining
(28, '978-0062356345', 'Bird Box', 'Josh Malerman', 'Ecco', 2014, 6, 'Không được mở mắt nếu muốn sống sót.', 'bird_box.jpg'), -- Nguồn: https://www.goodreads.com/book/show/18498558-bird-box

-- ========================================================
-- HỒI KÝ & TIỂU SỬ (Biography & Memoir - ID: 7)
-- ========================================================
(29, '978-1451648546', 'Steve Jobs', 'Walter Isaacson', 'Simon & Schuster', 2011, 7, 'Tiểu sử chính thức của nhà sáng lập Apple.', 'steve_jobs.jpg'), -- Nguồn: https://www.goodreads.com/book/show/11084145-steve-jobs
(30, '978-1524763138', 'Becoming', 'Michelle Obama', 'Crown', 2018, 7, 'Hồi ký của cựu đệ nhất phu nhân Mỹ.', 'becoming.jpg'), -- Nguồn: https://www.goodreads.com/book/show/38746485-becoming
(31, '978-0399590504', 'Educated', 'Tara Westover', 'Random House', 2018, 7, 'Hành trình tự học của cô gái vùng núi Idaho.', 'educated.jpg'), -- Nguồn: https://www.goodreads.com/book/show/35133922-educated
(32, '978-0553296983', 'The Diary of a Young Girl', 'Anne Frank', 'Bantam', 1947, 7, 'Nhật ký của cô bé Do Thái trong thế chiến.', 'anne_frank.jpg'), -- Nguồn: https://www.goodreads.com/book/show/48855.The_Diary_of_a_Young_Girl

-- ========================================================
-- LỊCH SỬ (History - ID: 8)
-- ========================================================
(33, '978-0062316097', 'Sapiens: A Brief History of Humankind', 'Yuval Noah Harari', 'Harper', 2015, 8, 'Lược sử loài người từ cổ đại đến hiện đại.', 'sapiens.jpg'), -- Nguồn: https://www.goodreads.com/book/show/23692271-sapiens
(34, '978-0393354324', 'Guns, Germs, and Steel', 'Jared Diamond', 'W. W. Norton', 1997, 8, 'Súng, vi trùng và thép định hình thế giới.', 'guns_germs_steel.jpg'), -- Nguồn: https://www.goodreads.com/book/show/1842.Guns_Germs_and_Steel
(35, '978-1101912343', 'The Silk Roads', 'Peter Frankopan', 'Vintage', 2015, 8, 'Lịch sử thế giới mới qua con đường tơ lụa.', 'silk_roads.jpg'), -- Nguồn: https://www.goodreads.com/book/show/25812847-the-silk-roads
(36, '978-0743226721', '1776', 'David McCullough', 'Simon & Schuster', 2005, 8, 'Năm định mệnh của nước Mỹ.', '1776.jpg'), -- Nguồn: https://www.goodreads.com/book/show/1067.1776

-- ========================================================
-- KHOA HỌC & CÔNG NGHỆ (Science & Technology - ID: 9)
-- ========================================================
(37, '978-0201616224', 'The Pragmatic Programmer', 'Andrew Hunt', 'Addison-Wesley', 1999, 9, 'Tư duy thực dụng trong lập trình.', 'pragmatic_programmer.jpg'), -- Nguồn: https://www.amazon.com/Pragmatic-Programmer-Journeyman-Master/dp/020161622X
(38, '978-0131103627', 'The C Programming Language', 'Brian Kernighan', 'Prentice Hall', 1988, 9, 'Sách giáo khoa kinh điển về ngôn ngữ C.', 'c_language.jpg'), -- Nguồn: https://www.amazon.com/Programming-Language-2nd-Brian-Kernighan/dp/0131103628
(39, '978-0735619678', 'Code Complete', 'Steve McConnell', 'Microsoft Press', 2004, 9, 'Cẩm nang hoàn thiện kỹ năng viết code.', 'code_complete.jpg'), -- Nguồn: https://www.amazon.com/Code-Complete-Practical-Handbook-Construction/dp/0735619670
(40, '978-0132350884', 'Clean Code', 'Robert C. Martin', 'Prentice Hall', 2008, 9, 'Nghệ thuật viết code sạch và dễ bảo trì.', 'clean_code_new.jpg'), -- Nguồn: https://www.amazon.com/Clean-Code-Handbook-Software-Craftsmanship/dp/0132350882

-- ========================================================
-- KINH DOANH (Business & Economics - ID: 10)
-- ========================================================
(41, '978-1612680194', 'Rich Dad Poor Dad', 'Robert Kiyosaki', 'Plata Publishing', 1997, 10, 'Tư duy tài chính khác biệt.', 'rich_dad.jpg'), -- Nguồn: https://www.amazon.com/Rich-Dad-Poor-Teach-Middle/dp/1612680194
(42, '978-0374533557', 'Thinking, Fast and Slow', 'Daniel Kahneman', 'Farrar, Straus', 2011, 10, 'Hai hệ thống tư duy chi phối con người.', 'thinking_fast_slow.jpg'), -- Nguồn: https://www.amazon.com/Thinking-Fast-Slow-Daniel-Kahneman/dp/0374533555
(43, '978-0804139298', 'Zero to One', 'Peter Thiel', 'Crown Business', 2014, 10, 'Cách xây dựng tương lai từ con số 0.', 'zero_to_one.jpg'), -- Nguồn: https://www.amazon.com/Zero-One-Notes-Startups-Future/dp/0804139296
(44, '978-1501135910', 'Shoe Dog', 'Phil Knight', 'Scribner', 2016, 10, 'Hồi ký của người sáng lập Nike.', 'shoe_dog.jpg'), -- Nguồn: https://www.amazon.com/Shoe-Dog-Memoir-Creator-Nike/dp/1501135910

-- ========================================================
-- KỸ NĂNG SỐNG (Self-Help - ID: 11)
-- ========================================================
(45, '978-0735211292', 'Atomic Habits', 'James Clear', 'Avery', 2018, 11, 'Thay đổi tí hon, hiệu quả bất ngờ.', 'atomic_habits.jpg'), -- Nguồn: https://jamesclear.com/atomic-habits
(46, '978-1577314806', 'The Power of Now', 'Eckhart Tolle', 'New World Library', 1997, 11, 'Sức mạnh của hiện tại và sự tỉnh thức.', 'power_of_now.jpg'), -- Nguồn: https://www.goodreads.com/book/show/6708.The_Power_of_Now
(47, '978-0062457714', 'The Subtle Art of Not Giving a F*ck', 'Mark Manson', 'Harper', 2016, 11, 'Nghệ thuật tinh tế của việc đếch quan tâm.', 'subtle_art.jpg'), -- Nguồn: https://markmanson.net/books/subtle-art
(48, '978-0060937385', 'The Alchemist', 'Paulo Coelho', 'HarperOne', 1988, 11, 'Hành trình theo đuổi ước mơ của chàng chăn cừu.', 'alchemist.jpg'), -- Nguồn: https://www.goodreads.com/book/show/18144590-the-alchemist

-- ========================================================
-- SÁCH THIẾU NHI (Children''s Literature - ID: 12)
-- ========================================================
(49, '978-0399226908', 'The Very Hungry Caterpillar', 'Eric Carle', 'World of Eric Carle', 1969, 12, 'Chú sâu háu ăn.', 'hungry_caterpillar.jpg'), -- Nguồn: https://www.goodreads.com/book/show/4948.The_Very_Hungry_Caterpillar
(50, '978-0061124952', 'Charlotte''s Web', 'E. B. White', 'HarperCollins', 1952, 12, 'Tình bạn giữa lợn Wilbur và nhện Charlotte.', 'charlottes_web.jpg'), -- Nguồn: https://www.goodreads.com/book/show/24178.Charlotte_s_Web
(51, '978-0142410363', 'Matilda', 'Roald Dahl', 'Puffin Books', 1988, 12, 'Cô bé thiên tài với phép thuật kỳ lạ.', 'matilda.jpg'), -- Nguồn: https://www.roalddahl.com/roald-dahl/stories/a-e/matilda
(52, '978-0156012195', 'The Little Prince', 'Antoine de Saint-Exupéry', 'Harcourt', 1943, 12, 'Hoàng tử bé và bông hồng duy nhất.', 'little_prince.jpg'); -- Nguồn: https://www.goodreads.com/book/show/157993.The_Little_Prince

-- BookCopies
INSERT INTO BookCopies (book_id, copy_code, status, condition_note) VALUES 
-- Sách ID 3 (Dune): 3 bản
(3, 'DN-001', 'Available', 'New'), (3, 'DN-002', 'Borrowed', 'Good'), (3, 'DN-003', 'Available', 'Worn cover'),
-- Sách ID 4 (1984): 2 bản
(4, '1984-001', 'Available', 'Worn'), (4, '1984-002', 'Available', 'Good'),
-- Sách ID 5 (Brief History): 2 bản
(5, 'BHT-001', 'Available', 'New'), (5, 'BHT-002', 'Maintenance', 'Cover torn'),
-- Sách ID 6 (Martian): 3 bản
(6, 'MAR-001', 'Borrowed', 'New'), (6, 'MAR-002', 'Available', 'New'), (6, 'MAR-003', 'Available', 'Good'),
-- Sách ID 7 (Hitchhiker): 2 bản
(7, 'HHG-001', 'Available', 'Good'), (7, 'HHG-002', 'Lost', 'Charge user'),
-- Sách ID 8 (Gone Girl): 2 bản
(8, 'GG-001', 'Available', 'Good'), (8, 'GG-002', 'Borrowed', 'Good'),
-- Sách ID 9 (Silent Patient): 2 bản
(9, 'TSP-001', 'Available', 'New'), (9, 'TSP-002', 'Available', 'New'),
-- Sách ID 10 (Da Vinci): 3 bản
(10, 'DVC-001', 'Available', 'Good'), (10, 'DVC-002', 'Lost', 'Charge user'), (10, 'DVC-003', 'Available', 'New'),
-- Sách ID 11 (Dragon Tattoo): 2 bản
(11, 'GDT-001', 'Available', 'Good'), (11, 'GDT-002', 'Available', 'Good'),
-- Sách ID 12 (And Then There Were None): 2 bản
(12, 'ATT-001', 'Borrowed', 'Old'), (12, 'ATT-002', 'Available', 'Good'),
-- Sách ID 13 (Harry Potter): 5 bản (Sách Hot)
(13, 'HP1-001', 'Borrowed', 'Worn'), (13, 'HP1-002', 'Borrowed', 'Good'), (13, 'HP1-003', 'Available', 'New'), (13, 'HP1-004', 'Available', 'New'), (13, 'HP1-005', 'Maintenance', 'Page missing'),
-- Sách ID 14 (Hobbit): 3 bản
(14, 'HBT-001', 'Available', 'New'), (14, 'HBT-002', 'Borrowed', 'Good'), (14, 'HBT-003', 'Available', 'Good'),
-- Sách ID 15 (Game of Thrones): 3 bản
(15, 'GOT-001', 'Available', 'Good'), (15, 'GOT-002', 'Available', 'New'), (15, 'GOT-003', 'Borrowed', 'Good'),
-- Sách ID 16 (Name of Wind): 2 bản
(16, 'NOW-001', 'Maintenance', 'Binding loose'), (16, 'NOW-002', 'Available', 'Good'),
-- Sách ID 17 (Pride Prejudice): 2 bản
(17, 'PAP-001', 'Available', 'Old'), (17, 'PAP-002', 'Available', 'Good'),
-- Sách ID 18 (Notebook): 2 bản
(18, 'NB-001', 'Borrowed', 'Good'), (18, 'NB-002', 'Available', 'Good'),
-- Sách ID 19 (Fault in Our Stars): 2 bản
(19, 'FOS-001', 'Available', 'New'), (19, 'FOS-002', 'Available', 'New'),
-- Sách ID 20 (Outlander): 2 bản
(20, 'OUT-001', 'Available', 'Good'), (20, 'OUT-002', 'Borrowed', 'Good'),
-- Sách ID 21 (Book Thief): 2 bản
(21, 'BT-001', 'Borrowed', 'New'), (21, 'BT-002', 'Available', 'Good'),
-- Sách ID 22 (All Light): 2 bản
(22, 'ALW-001', 'Available', 'New'), (22, 'ALW-002', 'Available', 'New'),
-- Sách ID 23 (Nightingale): 2 bản
(23, 'NG-001', 'Available', 'Good'), (23, 'NG-002', 'Available', 'Good'),
-- Sách ID 24 (Kite Runner): 2 bản
(24, 'KR-001', 'Available', 'Good'), (24, 'KR-002', 'Available', 'Good'),
-- Sách ID 25 (It): 3 bản
(25, 'IT-001', 'Available', 'New'), (25, 'IT-002', 'Available', 'Good'), (25, 'IT-003', 'Borrowed', 'Worn'),
-- Sách ID 26 (Dracula): 2 bản
(26, 'DRA-001', 'Borrowed', 'Old edition'), (26, 'DRA-002', 'Available', 'New'),
-- Sách ID 27 (Shining): 2 bản
(27, 'SHI-001', 'Available', 'Good'), (27, 'SHI-002', 'Borrowed', 'Good'),
-- Sách ID 28 (Bird Box): 2 bản
(28, 'BB-001', 'Available', 'New'), (28, 'BB-002', 'Available', 'New'),
-- Sách ID 29 (Steve Jobs): 3 bản
(29, 'SJ-001', 'Available', 'Good'), (29, 'SJ-002', 'Borrowed', 'Good'), (29, 'SJ-003', 'Available', 'Good'),
-- Sách ID 30 (Becoming): 2 bản
(30, 'BEC-001', 'Borrowed', 'New'), (30, 'BEC-002', 'Available', 'New'),
-- Sách ID 31 (Educated): 2 bản
(31, 'EDU-001', 'Available', 'New'), (31, 'EDU-002', 'Available', 'Good'),
-- Sách ID 32 (Anne Frank): 2 bản
(32, 'AF-001', 'Available', 'Good'), (32, 'AF-002', 'Borrowed', 'Old'),
-- Sách ID 33 (Sapiens): 3 bản
(33, 'SAP-001', 'Borrowed', 'New'), (33, 'SAP-002', 'Available', 'New'), (33, 'SAP-003', 'Available', 'Good'),
-- Sách ID 34 (Guns Germs): 2 bản
(34, 'GGS-001', 'Available', 'Good'), (34, 'GGS-002', 'Available', 'Good'),
-- Sách ID 35 (Silk Roads): 2 bản
(35, 'SR-001', 'Available', 'New'), (35, 'SR-002', 'Available', 'New'),
-- Sách ID 36 (1776): 2 bản
(36, '1776-001', 'Available', 'Old'), (36, '1776-002', 'Available', 'Good'),
-- Sách ID 37 (Pragmatic Programmer): 3 bản
(37, 'PP-001', 'Borrowed', 'New'), (37, 'PP-002', 'Borrowed', 'Good'), (37, 'PP-003', 'Available', 'New'),
-- Sách ID 38 (C Lang): 2 bản
(38, 'C-001', 'Available', 'Classic'), (38, 'C-002', 'Available', 'New'),
-- Sách ID 39 (Code Complete): 2 bản
(39, 'CC-001', 'Available', 'Good'), (39, 'CC-002', 'Available', 'Good'),
-- Sách ID 40 (Clean Code): 3 bản
(40, 'CLC-001', 'Borrowed', 'Highlighted'), (40, 'CLC-002', 'Available', 'New'), (40, 'CLC-003', 'Available', 'New'),
-- Sách ID 41 (Rich Dad): 2 bản
(41, 'RDPD-001', 'Available', 'Good'), (41, 'RDPD-002', 'Borrowed', 'Good'),
-- Sách ID 42 (Thinking Fast): 2 bản
(42, 'TFS-001', 'Available', 'New'), (42, 'TFS-002', 'Available', 'Good'),
-- Sách ID 43 (Zero to One): 2 bản
(43, 'ZTO-001', 'Borrowed', 'New'), (43, 'ZTO-002', 'Available', 'New'),
-- Sách ID 44 (Shoe Dog): 2 bản
(44, 'SD-001', 'Available', 'Good'), (44, 'SD-002', 'Available', 'Good'),
-- Sách ID 45 (Atomic Habits): 4 bản (Sách Hot)
(45, 'AH-001', 'Borrowed', 'New'), (45, 'AH-002', 'Borrowed', 'New'), (45, 'AH-003', 'Available', 'New'), (45, 'AH-004', 'Available', 'New'),
-- Sách ID 46 (Power of Now): 2 bản
(46, 'PON-001', 'Available', 'Good'), (46, 'PON-002', 'Available', 'Good'),
-- Sách ID 47 (Subtle Art): 2 bản
(47, 'SA-001', 'Available', 'New'), (47, 'SA-002', 'Borrowed', 'Good'),
-- Sách ID 48 (Alchemist): 3 bản
(48, 'ALC-001', 'Borrowed', 'Worn'), (48, 'ALC-002', 'Available', 'Good'), (48, 'ALC-003', 'Available', 'New'),
-- Sách ID 49 (Hungry Caterpillar): 2 bản
(49, 'HC-001', 'Available', 'New'), (49, 'HC-002', 'Available', 'Good'),
-- Sách ID 50 (Charlotte Web): 2 bản
(50, 'CW-001', 'Available', 'Good'), (50, 'CW-002', 'Available', 'Good'),
-- Sách ID 51 (Matilda): 2 bản
(51, 'MAT-001', 'Available', 'New'), (51, 'MAT-002', 'Available', 'Good'),
-- Sách ID 52 (Little Prince): 3 bản
(52, 'LP-001', 'Borrowed', 'New'), (52, 'LP-002', 'Available', 'Good'), (52, 'LP-003', 'Available', 'New');