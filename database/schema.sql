-- Active: 1768276078050@@localhost@3306@library_db
-- Xóa database cũ nếu tồn tại
-- DROP DATABASE IF EXISTS library_db;
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

INSERT INTO Books (isbn, title, author, publisher, publication_year, category_id, description, image_url) VALUES 
-- ========================================================
-- KHOA HỌC VIỄN TƯỞNG (Science Fiction - ID: 1)
-- ========================================================
('978-0441013593', 'Dune', 'Frank Herbert', 'Ace', 1965, 1, 'Set on the desert planet Arrakis, Dune is the story of the boy Paul Atreides, heir to a noble family tasked with ruling an inhospitable world where the only thing of value is the “spice” melange, a drug capable of extending life and enhancing consciousness. Coveted across the known universe, melange is a prize worth killing for... When House Atreides is betrayed, the destruction of Paul’s family will set the boy on a journey toward a destiny greater than he could ever have imagined. And as he evolves into the mysterious man known as Muad’Dib, he will bring to fruition humankind’s most ancient and unattainable dream.', 'dune.jpg'),
('978-0451524935', '1984', 'George Orwell', 'Signet Classic', 1949, 1, 'A masterpiece of rebellion and imprisonment where war is peace freedom is slavery and Big Brother is watching. Thought Police, Big Brother, Orwellian - these words have entered our vocabulary because of George Orwell''s classic dystopian novel 1984. The story of one man''s Nightmare Odyssey as he pursues a forbidden love affair through a world ruled by warring states and a power structure that controls not only information but also individual thought and memory 1984 is a prophetic haunting tale More relevant than ever before 1984 exposes the worst crimes imaginable the destruction of truth freedom and individuality. With a foreword by Thomas Pynchon. This beautiful paperback edition features deckled edges and french flaps a perfect gift for any occasion', '1984.jpg'),
('978-0553380163', 'A Brief History of Time', 'Stephen Hawking', 'Bantam', 1988, 1, 'A landmark volume in science writing by one of the great minds of our time, Stephen Hawking’s book explores such profound questions as: How did the universe begin—and what made its start possible? Does time always flow forward? Is the universe unending—or are there boundaries? Are there other dimensions in space? What will happen when it all ends? Told in language we all can understand, A Brief History of Time plunges into the exotic realms of black holes and quarks, of antimatter and “arrows of time,” of the big bang and a bigger God—where the possibilities are wondrous and unexpected. With exciting images and profound imagination, Stephen Hawking brings us closer to the ultimate secrets at the very heart of creation.', 'brief_history.jpg'),
('978-0425211159', 'The Martian', 'Andy Weir', 'Crown', 2011, 1, 'Six days ago, astronaut Mark Watney became one of the first people to walk on Mars. Now, he’s sure he’ll be the first person to die there. After a dust storm nearly kills him and forces his crew to evacuate while thinking him dead, Mark finds himself stranded and completely alone with no way to even signal Earth that he’s alive—and even if he could get word out, his supplies would be gone long before a rescue could arrive. Chances are, though, he won’t have time to starve to death. The damaged machinery, unforgiving environment, or plain-old “human error” are much more likely to kill him first. But Mark isn’t ready to give up yet. Drawing on his ingenuity, his engineering skills — and a relentless, dogged refusal to quit — he steadfastly confronts one seemingly insurmountable obstacle after the next. Will his resourcefulness be enough to overcome the impossible odds against him?', 'the_martian.jpg'),
('978-0345391803', 'The Hitchhiker''s Guide to the Galaxy', 'Douglas Adams', 'Del Rey', 1979, 1, 'Seconds before the Earth is demolished to make way for a galactic freeway, Arthur Dent is plucked off the planet by his friend Ford Prefect, a researcher for the revised edition of The Hitchhiker''s Guide to the Galaxy who, for the last fifteen years, has been posing as an out-of-work actor. Together this dynamic pair begin a journey through space aided by quotes from The Hitchhiker''s Guide ("A towel is about the most massively useful thing an interstellar hitchhiker can have") and a galaxy-full of fellow travelers: Zaphod Beeblebrox--the two-headed, three-armed ex-hippie and totally out-to-lunch president of the galaxy; Trillian, Zaphod''s girlfriend (formally Tricia McMillan), whom Arthur tried to pick up at a cocktail party once upon a time zone; Marvin, a paranoid, brilliant, and chronically depressed robot; Veet Voojagig, a former graduate student who is obsessed with the disappearance of all the ballpoint pens he bought over the years. Where are these pens? Why are we born? Why do we die? Why do we spend so much time between wearing digital watches? For all the answers stick your thumb to the stars. And don''t forget to bring a towel!', 'hitchhiker.jpg'),

-- ========================================================
-- TRINH THÁM & LY KỲ (Mystery & Thriller - ID: 2)
-- ========================================================
('978-0307588371', 'Gone Girl', 'Gillian Flynn', 'Crown', 2012, 2, 'Who are you? What have we done to each other? These are the questions Nick Dunne finds himself asking on the morning of his fifth wedding anniversary when his wife Amy suddenly disappears. The police suspect Nick. Amy''s friends reveal that she was afraid of him, that she kept secrets from him. He swears it isn''t true. A police examination of his computer shows strange searches. He says they weren''t made by him. And then there are the persistent calls on his mobile phone. So what did happen to Nick''s beautiful wife?', 'gone_girl.jpg'),
('978-1250301697', 'The Silent Patient', 'Alex Michaelides', 'Celadon Books', 2019, 2, 'Alicia Berenson’s life is seemingly perfect. A famous painter married to an in-demand fashion photographer, she lives in a grand house with big windows overlooking a park in one of London’s most desirable areas. One evening her husband Gabriel returns home late from a fashion shoot, and Alicia shoots him five times in the face, and then never speaks another word. Alicia’s refusal to talk, or give any kind of explanation, turns a domestic tragedy into something far grander, a mystery that captures the public imagination and casts Alicia into notoriety. The price of her art skyrockets, and she, the silent patient, is hidden away from the tabloids and spotlight at the Grove, a secure forensic unit in North London. Theo Faber is a criminal psychotherapist who has waited a long time for the opportunity to work with Alicia. His determination to get her to talk and unravel the mystery of why she shot her husband takes him down a twisting path into his own motivations—a search for the truth that threatens to consume him.... The Silent Patient is a shocking psychological thriller of a woman’s act of violence against her husband—and of the therapist obsessed with uncovering her motive.', 'silent_patient.jpg'),
('978-0307277671', 'The Da Vinci Code', 'Dan Brown', 'Anchor', 2003, 2, 'While in Paris on business, Harvard symbologist Robert Langdon receives an urgent late-night phone call: the elderly curator of the Louvre has been murdered inside the museum. Near the body, police have found a baffling cipher. While working to solve the enigmatic riddle, Langdon is stunned to discover it leads to a trail of clues hidden in the works of Da Vinci -- clues visible for all to see -- yet ingeniously disguised by the painter. Langdon joins forces with a gifted French cryptologist, Sophie Neveu, and learns the late curator was involved in the Priory of Sion -- an actual secret society whose members included Sir Isaac Newton, Botticelli, Victor Hugo, and Da Vinci, among others. In a breathless race through Paris, London, and beyond, Langdon and Neveu match wits with a faceless powerbroker who seems to anticipate their every move. Unless Langdon and Neveu can decipher the labyrinthine puzzle in time, the Priory''s ancient secret -- and an explosive historical truth -- will be lost forever. The Da Vinci Code heralds the arrival of a new breed of lightning-paced, intelligent thriller utterly unpredictable right up to its stunning conclusion.', 'davinci_code.jpg'),
('978-1594480003', 'The Girl with the Dragon Tattoo', 'Stieg Larsson', 'Vintage', 2005, 2, 'Harriet Vanger, a scion of one of Sweden’s wealthiest families disappeared over forty years ago. All these years later, her aged uncle continues to seek the truth. He hires Mikael Blomkvist, a crusading journalist recently trapped by a libel conviction, to investigate. He is aided by the pierced and tattooed punk prodigy Lisbeth Salander. Together they tap into a vein of unfathomable iniquity and astonishing corruption. An international publishing sensation, Stieg Larsson’s The Girl with the Dragon Tattoo combines murder mystery, family saga, love story, and financial intrigue into one satisfyingly complex and entertainingly atmospheric novel.', 'dragon_tattoo.jpg'),
('978-0062068248', 'And Then There Were None', 'Agatha Christie', 'St. Martin''s Press', 1939, 2, 'First, there were ten—a curious assortment of strangers summoned as weekend guests to a little private island off the coast of Devon. Their host, an eccentric millionaire unknown to all of them, is nowhere to be found. All that the guests have in common, we find out, is a wicked past they''re unwilling to reveal—and a secret that will seal their fate. For each has been marked for murder. A famous nursery rhyme is framed and hung in every room of the mansion: "Ten little boys went out to dine; One choked his little self and then there were nine. Nine little boys sat up very late; One overslept himself and then there were eight. Eight little boys traveling in Devon; One said he''d stay there then there were seven. Seven little boys chopping up sticks; One chopped himself in half and then there were six. Six little boys playing with a hive; A bumblebee stung one and then there were five. Five little boys going in for law; One got in Chancery and then there were four. Four little boys going out to sea; A red herring swallowed one and then there were three. Three little boys walking in the zoo; A big bear hugged one and then there were two. Two little boys sitting in the sun; One got frizzled up and then there was one. One little boy left all alone; He went out and hanged himself and then there were none." When they realize that murders are occurring as described in the rhyme, terror mounts. One by one they fall prey. Who has choreographed this dastardly scheme? And who will be left to tell the tale? Only the dead are above suspicion.', 'then_there_were_none.jpg'),

-- ========================================================
-- GIẢ TƯỞNG (Fantasy - ID: 3)
-- ========================================================
('978-0590353427', 'Harry Potter and the Sorcerer''s Stone', 'J.K. Rowling', 'Scholastic', 1997, 3, '"Turning the envelope over, his hand trembling, Harry saw a purple wax seal bearing a coat of arms; a lion, an eagle, a badger and a snake surrounding a large letter ''H''." Harry Potter has never even heard of Hogwarts when the letters start dropping on the doormat at number four, Privet Drive. Addressed in green ink on yellowish parchment with a purple seal, they are swiftly confiscated by his grisly aunt and uncle. Then, on Harry''s eleventh birthday, a great beetle-eyed giant of a man called Rubeus Hagrid bursts in with some astonishing news: Harry Potter is a wizard, and he has a place at Hogwarts School of Witchcraft and Wizardry. An incredible adventure is about to begin!', 'harry_potter_1.jpg'),
('978-0547928227', 'The Hobbit', 'J.R.R. Tolkien', 'Houghton Mifflin', 1937, 3, 'In a hole in the ground there lived a hobbit. Not a nasty, dirty, wet hole, filled with the ends of worms and an oozy smell, nor yet a dry, bare, sandy hole with nothing in it to sit down on or to eat: it was a hobbit-hole, and that means comfort. Written for J.R.R. Tolkien’s own children, The Hobbit met with instant critical acclaim when it was first published in 1937. Now recognized as a timeless classic, this introduction to the hobbit Bilbo Baggins, the wizard Gandalf, Gollum, and the spectacular world of Middle-earth recounts of the adventures of a reluctant hero, a powerful and dangerous ring, and the cruel dragon Smaug the Magnificent. The text in this 372-page paperback edition is based on that first published in Great Britain by Collins Modern Classics (1998), and includes a note on the text by Douglas A. Anderson (2001).', 'the_hobbit.jpg'),
('978-0553103540', 'A Game of Thrones', 'George R.R. Martin', 'Bantam', 1996, 3, 'Long ago, in a time forgotten, a preternatural event threw the seasons out of balance. In a land where summers can last decades and winters a lifetime, trouble is brewing. The cold is returning, and in the frozen wastes to the north of Winterfell, sinister forces are massing beyond the kingdom’s protective Wall. To the south, the king’s powers are failing—his most trusted adviser dead under mysterious circumstances and his enemies emerging from the shadows of the throne. At the center of the conflict lie the Starks of Winterfell, a family as harsh and unyielding as the frozen land they were born to. Now Lord Eddard Stark is reluctantly summoned to serve as the king’s new Hand, an appointment that threatens to sunder not only his family but the kingdom itself. Sweeping from a harsh land of cold to a summertime kingdom of epicurean plenty, A Game of Thrones tells a tale of lords and ladies, soldiers and sorcerers, assassins and bastards, who come together in a time of grim omens. Here an enigmatic band of warriors bear swords of no human metal; a tribe of fierce wildlings carry men off into madness; a cruel young dragon prince barters his sister to win back his throne; a child is lost in the twilight between life and death; and a determined woman undertakes a treacherous journey to protect all she holds dear. Amid plots and counter-plots, tragedy and betrayal, victory and terror, allies and enemies, the fate of the Starks hangs perilously in the balance, as each side endeavors to win that deadliest of conflicts: the game of thrones.', 'game_of_thrones.jpg'),
('978-0756404079', 'The Name of the Wind', 'Patrick Rothfuss', 'DAW', 2007, 3, 'Told in Kvothe''s own voice, this is the tale of the magically gifted young man who grows to be the most notorious wizard his world has ever seen. The intimate narrative of his childhood in a troupe of traveling players, his years spent as a near-feral orphan in a crime-ridden city, his daringly brazen yet successful bid to enter a legendary school of magic, and his life as a fugitive after the murder of a king form a gripping coming-of-age story unrivaled in recent literature. A high-action story written with a poet''s hand, The Name of the Wind is a masterpiece that will transport readers into the body and mind of a wizard.', 'name_of_wind.jpg'),

-- ========================================================
-- LÃNG MẠN (Romance - ID: 4)
-- ========================================================
('978-0141439518', 'Pride and Prejudice', 'Jane Austen', 'Penguin Classics', 1813, 4, 'Since its immediate success in 1813, Pride and Prejudice has remained one of the most popular novels in the English language. Jane Austen called this brilliant work "her own darling child" and its vivacious heroine, Elizabeth Bennet, "as delightful a creature as ever appeared in print." The romantic clash between the opinionated Elizabeth and her proud beau, Mr. Darcy, is a splendid performance of civilized sparring. And Jane Austen''s radiant wit sparkles as her characters dance a delicate quadrille of flirtation and intrigue, making this book the most superb comedy of manners of Regency England.', 'pride_prejudice.jpg'),
('978-1455582877', 'The Notebook', 'Nicholas Sparks', 'Grand Central', 1996, 4, 'A man with a faded, well-worn notebook open in his lap. A woman experiencing a morning ritual she doesn''t understand. Until he begins to read to her. The Notebook is an achingly tender story about the enduring power of love, a story of miracles that will stay with you forever. Set amid the austere beauty of coastal North Carolina in 1946, The Notebook begins with the story of Noah Calhoun, a rural Southerner returned home from World War II. Noah, thirty-one, is restoring a plantation home to its former glory, and he is haunted by images of the beautiful girl he met fourteen years earlier, a girl he loved like no other. Unable to find her, yet unwilling to forget the summer they spent together, Noah is content to live with only memories...until she unexpectedly returns to his town to see him once again. Allie Nelson, twenty-nine, is now engaged to another man, but realizes that the original passion she felt for Noah has not dimmed with the passage of time. Still, the obstacles that once ended their previous relationship remain, and the gulf between their worlds is too vast to ignore. With her impending marriage only weeks away, Allie is forced to confront her hopes and dreams for the future, a future that only she can shape. Like a puzzle within a puzzle, the story of Noah and Allie is just the beginning. As it unfolds, their tale miraculously becomes something different, with much higher stakes. The result is a deeply moving portrait of love itself, the tender moments and the fundamental changes that affect us all. Shining with a beauty that is rarely found in current literature, The Notebook establishes Nicholas Sparks as a classic storyteller with a unique insight into the only emotion that really matters. "I am nothing special, of this I am sure. I am a common man with common thoughts and I''ve led a common life. There are no monuments dedicated to me and my name will soon be forgotten, but I''ve loved another with all my heart and soul, and to me, this has always been enough." And so begins one of the most poignant and compelling love stories you will ever read...The Notebook', 'the_notebook.jpg'),
('978-0142410707', 'The Fault in Our Stars', 'John Green', 'Penguin Books', 2012, 4, 'Despite the tumor-shrinking medical miracle that has bought her a few years, Hazel has never been anything but terminal, her final chapter inscribed upon diagnosis. But when a gorgeous plot twist named Augustus Waters suddenly appears at Cancer Kid Support Group, Hazel''s story is about to be completely rewritten. Insightful, bold, irreverent, and raw, The Fault in Our Stars is award-winning author John Green''s most ambitious and heartbreaking work yet, brilliantly exploring the funny, thrilling, and tragic business of being alive and in love.', 'fault_stars.jpg'),
('978-0385319959', 'Outlander', 'Diana Gabaldon', 'Delacorte Press', 1991, 4, 'The year is 1945. Claire Randall, a former combat nurse, is just back from the war and reunited with her husband on a second honeymoon when she walks through a standing stone in one of the ancient circles that dot the British Isles. Suddenly she is a Sassenach—an “outlander”—in a Scotland torn by war and raiding border clans in the year of Our Lord...1743. Hurled back in time by forces she cannot understand, Claire is catapulted into the intrigues of lairds and spies that may threaten her life, and shatter her heart. For here James Fraser, a gallant young Scots warrior, shows her a love so absolute that Claire becomes a woman torn between fidelity and desire—and between two vastly different men in two irreconcilable lives.', 'outlander.jpg'),

-- ========================================================
-- TIỂU THUYẾT LỊCH SỬ (Historical Fiction - ID: 5)
-- ========================================================
('978-0375842207', 'The Book Thief', 'Markus Zusak', 'Knopf', 2005, 5, 'It is 1939. Nazi Germany. The country is holding its breath. Death has never been busier, and will be busier still. By her brother''s graveside, Liesel''s life is changed when she picks up a single object, partially hidden in the snow. It is The Gravedigger''s Handbook, left behind there by accident, and it is her first act of book thievery. So begins a love affair with books and words, as Liesel, with the help of her accordian-playing foster father, learns to read. Soon she is stealing books from Nazi book-burnings, the mayor''s wife''s library, wherever there are books to be found. But these are dangerous times. When Liesel''s foster family hides a Jew in their basement, Liesel''s world is both opened up, and closed down. In superbly crafted writing that burns with intensity, award-winning author Markus Zusak has given us one of the most enduring stories of our time. (Note: this title was not published as YA fiction)', 'book_thief.jpg'),
('978-1476746586', 'All the Light We Cannot See', 'Anthony Doerr', 'Scribner', 2014, 5, 'Marie-Laure lives in Paris near the Museum of Natural History, where her father works. When she is twelve, the Nazis occupy Paris and father and daughter flee to the walled citadel of Saint-Malo, where Marie-Laure''s reclusive great uncle lives in a tall house by the sea. With them they carry what might be the museum''s most valuable and dangerous jewel. In a mining town in Germany, Werner Pfennig, an orphan, grows up with his younger sister, enchanted by a crude radio they find that brings them news and stories from places they have never seen or imagined. Werner becomes an expert at building and fixing these crucial new instruments and is enlisted to use his talent to track down the resistance. Deftly interweaving the lives of Marie-Laure and Werner, Doerr illuminates the ways, against all odds, people try to be good to one another. From the highly acclaimed, multiple award-winning Anthony Doerr, the stunningly beautiful instant New York Times bestseller about a blind French girl and a German boy whose paths collide in occupied France as both try to survive the devastation of World War II.', 'all_the_light.jpg'),
('978-0312577223', 'The Nightingale', 'Kristin Hannah', 'St. Martin''s Griffin', 2015, 5, 'In love we find out who we want to be. In war we find out who we are. France, 1939 In the quiet village of Carriveau, Vianne Mauriac says goodbye to her husband, Antoine, as he heads for the Front. She doesn''t believe that the Nazis will invade France…but invade they do, in droves of marching soldiers, in caravans of trucks and tanks, in planes that fill the skies and drop bombs upon the innocent. When a German captain requisitions Vianne''s home, she and her daughter must live with the enemy or lose everything. Without food or money or hope, as danger escalates all around them, she is forced to make one impossible choice after another to keep her family alive. Vianne''s sister, Isabelle, is a rebellious eighteen-year-old, searching for purpose with all the reckless passion of youth. While thousands of Parisians march into the unknown terrors of war, she meets Gäetan, a partisan who believes the French can fight the Nazis from within France, and she falls in love as only the young can…completely. But when he betrays her, Isabelle joins the Resistance and never looks back, risking her life time and again to save others. With courage, grace, and powerful insight, bestselling author Kristin Hannah captures the epic panorama of World War II and illuminates an intimate part of history seldom seen: the women''s war. The Nightingale tells the stories of two sisters, separated by years and experience, by ideals, passion and circumstance, each embarking on her own dangerous path toward survival, love, and freedom in German-occupied, war-torn France―a heartbreakingly beautiful novel that celebrates the resilience of the human spirit and the durability of women. It is a novel for everyone, a novel for a lifetime.', 'nightingale.jpg'),
('978-1594487361', 'The Kite Runner', 'Khaled Hosseini', 'Riverhead Books', 2003, 5, '1970s Afghanistan: Twelve-year-old Amir is desperate to win the local kite-fighting tournament and his loyal friend Hassan promises to help him. But neither of the boys can foresee what would happen to Hassan that afternoon, an event that is to shatter their lives. After the Russians invade and the family is forced to flee to America, Amir realises that one day he must return to an Afghanistan under Taliban rule to find the one thing that his new world cannot grant him: redemption.', 'kite_runner.jpg'),

-- ========================================================
-- KINH DỊ (Horror - ID: 6)
-- ========================================================
('978-1501142970', 'It', 'Stephen King', 'Scribner', 1986, 6, 'Welcome to Derry, Maine ... It’s a small city, a place as hauntingly familiar as your own hometown. Only in Derry the haunting is real ... They were seven teenagers when they first stumbled upon the horror. Now they are grown-up men and women who have gone out into the big world to gain success and happiness. But none of them can withstand the force that has drawn them back to Derry to face the nightmare without an end, and the evil without a name.', 'it.jpg'),
('978-0486411095', 'Dracula', 'Bram Stoker', 'Dover Publications', 1897, 6, 'When Jonathan Harker visits Transylvania to help Count Dracula with the purchase of a London house, he makes a series of horrific discoveries about his client. Soon afterwards, various bizarre incidents unfold in England: an apparently unmanned ship is wrecked off the coast of Whitby; a young woman discovers strange puncture marks on her neck; and the inmate of a lunatic asylum raves about the ''Master'' and his imminent arrival. In  Dracula , Bram Stoker created one of the great masterpieces of the horror genre, brilliantly evoking a nightmare world of vampires and vampire hunters and also illuminating the dark corners of Victorian sexuality and desire. This Norton Critical Edition includes a rich selection of background and source materials in three areas: Contexts includes probable inspirations for Dracula in the earlier works of James Malcolm Rymer and Emily Gerard. Also included are a discussion of Stoker''s working notes for the novel and "Dracula''s Guest," the original opening chapter to Dracula. Reviews and Reactions reprints five early reviews of the novel. "Dramatic and Film Variations" focuses on theater and film adaptations of Dracula, two indications of the novel''s unwavering appeal. David J. Skal, Gregory A. Waller, and Nina Auerbach offer their varied perspectives. Checklists of both dramatic and film adaptations are included. Criticism collects seven theoretical interpretations of Dracula by Phyllis A. Roth, Carol A. Senf, Franco Moretti, Christopher Craft, Bram Dijkstra, Stephen D. Arata, and Talia Schaffer. A Chronology and a Selected Bibliography are included.', 'dracula.jpg'),
('978-0307743657', 'The Shining', 'Stephen King', 'Anchor', 1977, 6, 'Jack Torrance''s new job at the Overlook Hotel is the perfect chance for a fresh start. As the off-season caretaker at the atmospheric old hotel, he''ll have plenty of time to spend reconnecting with his family and working on his writing. But as the harsh winter weather sets in, the idyllic location feels ever more remote... and more sinister. And the only one to notice the strange and terrible forces gathering around the Overlook is Danny Torrance, a uniquely gifted five-year-old.', 'shining.jpg'),
('978-0062356345', 'Bird Box', 'Josh Malerman', 'Ecco', 2014, 6, 'Something is out there, something terrifying that must not be seen. One glimpse of it, and a person is driven to deadly violence. No one knows what it is or where it came from. Five years after it began, a handful of scattered survivors remains, including Malorie and her two young children. Living in an abandoned house near the river, she has dreamed of fleeing to a place where they might be safe. Now that the boy and girl are four, it''s time to go, but the journey ahead will be terrifying: twenty miles downriver in a rowboat—blindfolded—with nothing to rely on but her wits and the children''s trained ears. One wrong choice and they will die. Something is following them all the while, but is it man, animal, or monster? Interweaving past and present, Bird Box is a snapshot of a world unraveled that will have you racing to the final page.', 'bird_box.jpg'),

-- ========================================================
-- HỒI KÝ & TIỂU SỬ (Biography & Memoir - ID: 7)
-- ========================================================
('978-1451648546', 'Steve Jobs', 'Walter Isaacson', 'Simon & Schuster', 2011, 7, 'Walter Isaacson''s worldwide bestselling biography of Apple cofounder Steve Jobs. Based on more than forty interviews with Steve Jobs conducted over two years--as well as interviews with more than 100 family members, friends, adversaries, competitors, and colleagues--Walter Isaacson has written a riveting story of the roller-coaster life and searingly intense personality of a creative entrepreneur whose passion for perfection and ferocious drive revolutionized six industries: personal computers, animated movies, music, phones, tablet computing, and digital publishing. Isaacson''s portrait touched millions of readers. At a time when America is seeking ways to sustain its innovative edge, Jobs stands as the ultimate icon of inventiveness and applied imagination. He knew that the best way to create value in the twenty-first century was to connect creativity with technology. He built a company where leaps of the imagination were combined with remarkable feats of engineering. Although Jobs cooperated with the author, he asked for no control over what was written. He put nothing off-limits. He encouraged the people he knew to speak honestly. He himself spoke candidly about the people he worked with and competed against. His friends, foes, and colleagues offer an unvarnished view of the passions, perfectionism, obsessions, artistry, devilry, and compulsion for control that shaped his approach to business and the innovative products that resulted. His tale is instructive and cautionary, filled with lessons about innovation, character, leadership, and values. Steve Jobs is the inspiration for the movie of the same name starring Michael Fassbender, Kate Winslet, Seth Rogen, and Jeff Daniels, directed by Danny Boyle with a screenplay by Aaron Sorkin.', 'steve_jobs.jpg'),
('978-1524763138', 'Becoming', 'Michelle Obama', 'Crown', 2018, 7, 'In a life filled with meaning and accomplishment, Michelle Obama has emerged as one of the most iconic and compelling women of our era. As First Lady of the United States of America—the first African American to serve in that role—she helped create the most welcoming and inclusive White House in history, while also establishing herself as a powerful advocate for women and girls in the U.S. and around the world, dramatically changing the ways that families pursue healthier and more active lives, and standing with her husband as he led America through some of its most harrowing moments. Along the way, she showed us a few dance moves, crushed Carpool Karaoke, and raised two down-to-earth daughters under an unforgiving media glare. In her memoir, a work of deep reflection and mesmerizing storytelling, Michelle Obama invites readers into her world, chronicling the experiences that have shaped her—from her childhood on the South Side of Chicago to her years as an executive balancing the demands of motherhood and work, to her time spent at the world’s most famous address. With unerring honesty and lively wit, she describes her triumphs and her disappointments, both public and private, telling her full story as she has lived it—in her own words and on her own terms. Warm, wise, and revelatory, Becoming is the deeply personal reckoning of a woman of soul and substance who has steadily defied expectations—and whose story inspires us to do the same.', 'becoming.jpg'),
('978-0399590504', 'Educated', 'Tara Westover', 'Random House', 2018, 7, 'Tara Westover was 17 the first time she set foot in a classroom. Born to survivalists in the mountains of Idaho, she prepared for the end of the world by stockpiling home-canned peaches and sleeping with her "head-for-the-hills bag". In the summer she stewed herbs for her mother, a midwife and healer, and in the winter she salvaged in her father''s junkyard. Her father forbade hospitals, so Tara never saw a doctor or nurse. Gashes and concussions, even burns from explosions, were all treated at home with herbalism. The family was so isolated from mainstream society that there was no one to ensure the children received an education and no one to intervene when one of Tara''s older brothers became violent. Then, lacking any formal education, Tara began to educate herself. She taught herself enough mathematics and grammar to be admitted to Brigham Young University, where she studied history, learning for the first time about important world events like the Holocaust and the civil rights movement. Her quest for knowledge transformed her, taking her over oceans and across continents, to Harvard and to Cambridge. Only then would she wonder if she''d traveled too far, if there was still a way home. Educated  is an account of the struggle for self-invention. It is a tale of fierce family loyalty and of the grief that comes with severing the closest of ties. With the acute insight that distinguishes all great writers, Westover has crafted a universal coming-of-age story that gets to the heart of what an education is and what it offers: the perspective to see one''s life through new eyes and the will to change it.', 'educated.jpg'),
('978-0553296983', 'The Diary of a Young Girl', 'Anne Frank', 'Bantam', 1947, 7, 'Discovered in the attic where she spent the final years of her life,  Anne Frank’s Diary  has become a timeless classic; a powerful reminder of the horrors of war and a moving testament to the resilience of the human spirit. In 1942, as the Nazis occupied Holland, thirteen-year-old Anne Frank and her Jewish family fled their home in Amsterdam and went into hiding. For the next two years, until they were betrayed to the Gestapo, the Franks and another family lived in the cramped “Secret Annexe” of an old office building. Cut off from the outside world, they endured hunger, boredom, the strain of close quarters, and the constant fear of discovery and death. Through it all, Anne documented her experiences in a diary filled with vivid observations. At times thoughtful, poignant, and even unexpectedly funny, her writing offers a remarkable window into the strength and vulnerability of the human spirit. It’s both a compelling self-portrait of a bright, spirited young woman and a heartbreaking glimpse of a life that should have been far longer.', 'anne_frank.jpg'),

-- ========================================================
-- LỊCH SỬ (History - ID: 8)
-- ========================================================
('978-0062316097', 'Sapiens: A Brief History of Humankind', 'Yuval Noah Harari', 'Harper', 2015, 8, 'From a renowned historian comes a groundbreaking narrative of humanity’s creation and evolution—a #1 international bestseller—that explores the ways in which biology and history have defined us and enhanced our understanding of what it means to be “human.” One hundred thousand years ago, at least six different species of humans inhabited Earth. Yet today there is only one—homo sapiens. What happened to the others? And what may happen to us? Most books about the history of humanity pursue either a historical or a biological approach, but Dr. Yuval Noah Harari breaks the mold with this highly original book that begins about 70,000 years ago with the appearance of modern cognition. From examining the role evolving humans have played in the global ecosystem to charting the rise of empires, Sapiens integrates history and science to reconsider accepted narratives, connect past developments with contemporary concerns, and examine specific events within the context of larger ideas. Dr. Harari also compels us to look ahead, because over the last few decades humans have begun to bend laws of natural selection that have governed life for the past four billion years. We are acquiring the ability to design not only the world around us, but also ourselves. Where is this leading us, and what do we want to become? Featuring 27 photographs, 6 maps, and 25 illustrations/diagrams, this provocative and insightful work is sure to spark debate and is essential reading for aficionados of Jared Diamond, James Gleick, Matt Ridley, Robert Wright, and Sharon Moalem.', 'sapiens.jpg'),
('978-0393354324', 'Guns, Germs, and Steel', 'Jared Diamond', 'W. W. Norton', 1997, 8, '"Diamond has written a book of remarkable scope ... one of the most important and readable works on the human past published in recent years." Winner of the Pulitzer Prize and a national bestseller: the global account of the rise of civilization that is also a stunning refutation of ideas of human development based on race. In this "artful, informative, and delightful" (William H. McNeill, New York Review of Books) book, Jared Diamond convincingly argues that geographical and environmental factors shaped the modern world. Societies that had a head start in food production advanced beyond the hunter-gatherer stage, and then developed writing, technology, government, and organized religion—as well as nasty germs and potent weapons of war—and adventured on sea and land to conquer and decimate preliterate cultures. A major advance in our understanding of human societies,  Guns, Germs, and Steel  chronicles the way that the modern world came to be and stunningly dismantles racially based theories of human history. Winner of the Pulitzer Prize, the Phi Beta Kappa Award in Science, the Rhone-Poulenc Prize, and the Commonwealth Club of California''s Gold Medal.', 'guns_germs_steel.jpg'),
('978-1101912343', 'The Silk Roads', 'Peter Frankopan', 'Vintage', 2015, 8, 'From the Middle East and its political instability to China and its economic rise, the vast region stretching eastward from the Balkans across the steppe and South Asia has been thrust into the global spotlight in recent years. Frankopan teaches us that to understand what is at stake for the cities and nations built on these intricate trade routes, we must first understand their astounding pasts. Frankopan realigns our understanding of the world, pointing us eastward. It was on the Silk Roads that East and West first encountered each other through trade and conquest, leading to the spread of ideas, cultures and religions. From the rise and fall of empires to the spread of Buddhism and the advent of Christianity and Islam, right up to the great wars of the twentieth century—this book shows how the fate of the West has always been inextricably linked to the East.', 'silk_roads.jpg'),
('978-0743226721', '1776', 'David McCullough', 'Simon & Schuster', 2005, 8, 'In this masterful book, David McCullough tells the intensely human story of those who marched with General George Washington in the year of the Declaration of Independence - when the whole American cause was riding on their success, without which all hope for independence would have been dashed and the noble ideals of the Declaration would have amounted to little more than words on paper.Based on extensive research in both American and British archives, 1776 is a powerful drama written with extraordinary narrative vitality. It is the story of Americans in the ranks, men of every shape, size, and color, farmers, schoolteachers, shoemakers, no-accounts, and mere boys turned soldiers. And it is the story of the King''s men, the British commander, William Howe, an his highly disciplined redcoats who looked on their rebel foes with contempt and fought with a valor too little known.At the center of the drama, with Washington, are two young American patriots, who, at first, knew no more of war than what they had read in books - Nathaniel Green, a Quaker who was made a general at thirty-three, and Henry Knox, a twenty-five-year-old bookseller who had the preposterous idea of hauling the guns of Fort Ticonderoga overland to Boston in the dead of Winter.But it is the American commander-in-chief who stands foremost - Washington, who had never before led an army in battle. Written as a companion work to his celebrated biography of John Adams, David McCullough''s 1776 is another landmark in the literature of American history.', '1776.jpg'),

-- ========================================================
-- KHOA HỌC & CÔNG NGHỆ (Science & Technology - ID: 9)
-- ========================================================
('978-0201616224', 'The Pragmatic Programmer', 'Andrew Hunt', 'Addison-Wesley', 1999, 9, 'Ward Cunningham Straight from the programming trenches, The Pragmatic Programmer cuts through the increasing specialization and technicalities of modern software development to examine the core process--taking a requirement and producing working, maintainable code that delights its users. It covers topics ranging from personal responsibility and career development to architectural techniques for keeping your code flexible and easy to adapt and reuse. Read this book, and you’ll learn how to Fight software rot; Avoid the trap of duplicating knowledge; Write flexible, dynamic, and adaptable code; Avoid programming by coincidence; Bullet-proof your code with contracts, assertions, and exceptions; Capture real requirements; Test ruthlessly and effectively; Delight your users; Build teams of pragmatic programmers; and Make your developments more precise with automation. Written as a series of self-contained sections and filled with entertaining anecdotes, thoughtful examples, and interesting analogies, The Pragmatic Programmer illustrates the best practices and major pitfalls of many different aspects of software development. Whether you’re a new coder, an experienced program.', 'pragmatic_programmer.jpg'),
('978-0131103627', 'The C Programming Language', 'Brian Kernighan', 'Prentice Hall', 1988, 9, 'The authors present the complete guide to ANSI standard C language programming. Written by the developers of C, this new version helps readers keep up with the finalized ANSI standard for C while showing how to take advantage of C''s rich set of operators, economy of expression, improved control flow, and data structures. The 2/E has been completely rewritten with additional examples and problem sets to clarify the implementation of difficult language constructs. For years, C programmers have let K&R guide them to building well-structured and efficient programs. Now this same help is available to those working with ANSI compilers. Includes detailed coverage of the C language plus the official C language reference manual for at-a-glance help with syntax notation, declarations, ANSI changes, scope rules, and the list goes on and on.', 'c_language.jpg'),
('978-0735619678', 'Code Complete', 'Steve McConnell', 'Microsoft Press', 2004, 9, 'Widely considered one of the best practical guides to programming, Steve McConnell’s original CODE COMPLETE has been helping developers write better software for more than a decade. Now this classic book has been fully updated and revised with leading-edge practices―and hundreds of new code samples―illustrating the art and science of software construction. Capturing the body of knowledge available from research, academia, and everyday commercial practice, McConnell synthesizes the most effective techniques and must-know principles into clear, pragmatic guidance. No matter what your experience level, development environment, or project size, this book will inform and stimulate your thinking―and help you build the highest quality code.Discover the timeless techniques and strategies that help you: Design for minimum complexity and maximum creativity Reap the benefits of collaborative development Apply defensive programming techniques to reduce and flush out errors Exploit opportunities to refactor―or evolve―code, and do it safely Use construction practices that are right-weight for your project Debug problems quickly and effectively Resolve critical construction issues early and correctly Build quality into the beginning, middle, and end of your project', 'code_complete.jpg'),
('978-0132350884', 'Clean Code', 'Robert C. Martin', 'Prentice Hall', 2008, 9, 'Even bad code can function. But if code isn’t clean, it can bring a development organization to its knees. Every year, countless hours and significant resources are lost because of poorly written code. But it doesn’t have to be that way. Noted software expert Robert C. Martin, presents a revolutionary paradigm with  Clean Code: A Handbook of Agile Software Craftsmanship . Martin, who has helped bring agile principles from a practitioner’s point of view to tens of thousands of programmers, has teamed up with his colleagues from Object Mentor to distill their best agile practice of cleaning code “on the fly” into a book that will instill within you the values of software craftsman, and make you a better programmer―but only if you work at it. What kind of work will you be doing? You’ll be reading code―lots of code. And you will be challenged to think about what’s right about that code, and what’s wrong with it. More importantly you will be challenged to reassess your professional values and your commitment to your craft. Clean Code is divided into three parts. The first describes the principles, patterns, and practices of writing clean code. The second part consists of several case studies of increasing complexity. Each case study is an exercise in cleaning up code―of transforming a code base that has some problems into one that is sound and efficient. The third part is the payoff: a single chapter containing a list of heuristics and “smells” gathered while creating the case studies. The result is a knowledge base that describes the way we think when we write, read, and clean code. Readers will come away from this book understanding How to tell the difference between good and bad code How to write good code and how to transform bad code into good code How to create good names, good functions, good objects, and good classes How to format code for maximum readability How to implement complete error handling without obscuring code logic How to unit test and practice test-driven development What “smells” and heuristics can help you identify bad code This book is a must for any developer, software engineer, project manager, team lead, or systems analyst with an interest in producing better code.', 'clean_code_new.jpg'),

-- ========================================================
-- KINH DOANH (Business & Economics - ID: 10)
-- ========================================================
('978-1612680194', 'Rich Dad Poor Dad', 'Robert Kiyosaki', 'Plata Publishing', 1997, 10, 'It''s been nearly 25 years since Robert Kiyosaki’s  Rich Dad Poor Dad  first made waves in the Personal Finance arena. It has since become the #1 Personal Finance book of all time... translated into dozens of languages and sold around the world. Rich Dad Poor Dad  is Robert''s story of growing up with two dads  his real father and the father of his best friend, his rich dad  and the ways in which both men shaped his thoughts about money and investing. The book explodes the myth that you need to earn a high income to be rich and explains the difference between working for money and having your money work for you. 20 Years... 20/20 Hindsight In the 20th Anniversary Edition of this classic, Robert offers an update on what we’ve seen over the past 20 years related to money, investing, and the global economy. Sidebars throughout the book will take readers fast forward”  from 1997 to today  as Robert assesses how the principles taught by his rich dad have stood the test of time. In many ways, the messages of  Rich Dad Poor Dad , messages that were criticized and challenged two decades ago, are more meaningful, relevant and important today than they were 20 years ago. As always, readers can expect that Robert will be candid, insightful... and continue to rock more than a few boats in his retrospective.', 'rich_dad.jpg'),
('978-0374533557', 'Thinking, Fast and Slow', 'Daniel Kahneman', 'Farrar, Straus', 2011, 10, 'In his mega bestseller,  Thinking, Fast and Slow , Daniel Kahneman, world-famous psychologist and winner of the Nobel Prize in Economics, takes us on a groundbreaking tour of the mind and explains the two systems that drive the way we think. System 1 is fast, intuitive, and emotional; System 2 is slower, more deliberative, and more logical. The impact of overconfidence on corporate strategies, the difficulties of predicting what will make us happy in the future, the profound effect of cognitive biases on everything from playing the stock market to planning our next vacation―each of these can be understood only by knowing how the two systems shape our judgments and decisions. Engaging the reader in a lively conversation about how we think, Kahneman reveals where we can and cannot trust our intuitions and how we can tap into the benefits of slow thinking. He offers practical and enlightening insights into how choices are made in both our business and our personal lives―and how we can use different techniques to guard against the mental glitches that often get us into trouble. Topping bestseller lists for almost ten years,  Thinking, Fast and Slow  is a contemporary classic, an essential book that has changed the lives of millions of readers.', 'thinking_fast_slow.jpg'),
('978-0804139298', 'Zero to One', 'Peter Thiel', 'Crown Business', 2014, 10, '“Peter Thiel has built multiple breakthrough companies, and  Zero to One  shows how.”—Elon Musk, CEO of SpaceX and Tesla The great secret of our time is that there are still uncharted frontiers to explore and new inventions to create. In  Zero to One , legendary entrepreneur and investor Peter Thiel shows how we can find singular ways to create those new things. Thiel begins with the contrarian premise that we live in an age of technological stagnation, even if we’re too distracted by shiny mobile devices to notice. Information technology has improved rapidly, but there is no reason why progress should be limited to computers or Silicon Valley. Progress can be achieved in any industry or area of business. It comes from the most important skill that every leader must master: learning to think for yourself. Doing what someone else already knows how to do takes the world from 1 to n, adding more of something familiar. But when you do something new, you go from 0 to 1. The next Bill Gates will not build an operating system. The next Larry Page or Sergey Brin won’t make a search engine. Tomorrow’s champions will not win by competing ruthlessly in today’s marketplace. They will escape competition altogether, because their businesses will be unique. Zero to One  presents at once an optimistic view of the future of progress in America and a new way of thinking about innovation: it starts by learning to ask the questions that lead you to find value in unexpected places.', 'zero_to_one.jpg'),
('978-1501135910', 'Shoe Dog', 'Phil Knight', 'Scribner', 2016, 10, 'In this candid and riveting memoir, for the first time ever, Nike founder and board chairman Phil Knight shares the inside story of the company’s early days as an intrepid start-up and its evolution into one of the world’s most iconic, game-changing, and profitable brands. Young, searching, fresh out of business school, Phil Knight borrowed fifty dollars from his father and launched a company with one simple mission: import high-quality, low-cost running shoes from Japan. Selling the shoes from the trunk of his Plymouth Valiant, Knight grossed eight thousand dollars that first year, 1963. Today, Nike’s annual sales top $30 billion. In this age of start-ups, Knight’s Nike is the gold standard, and its swoosh is more than a logo. A symbol of grace and greatness, it’s one of the few icons instantly recognized in every corner of the world. But Knight, the man behind the swoosh, has always been a mystery. Now, in a memoir that’s surprising, humble, unfiltered, funny, and beautifully crafted, he tells his story at last. It all begins with a classic crossroads moment. Twenty-four years old, backpacking through Asia and Europe and Africa, wrestling with life’s Great Questions, Knight decides the unconventional path is the only one for him. Rather than work for a big corporation, he will create something all his own, something new, dynamic, different. Knight details the many terrifying risks he encountered along the way, the crushing setbacks, the ruthless competitors, the countless doubters and haters and hostile bankers—as well as his many thrilling triumphs and narrow escapes. Above all, he recalls the foundational relationships that formed the heart and soul of Nike, with his former track coach, the irascible and charismatic Bill Bowerman, and with his first employees, a ragtag group of misfits and savants who quickly became a band of swoosh-crazed brothers. Together, harnessing the electrifying power of a bold vision and a shared belief in the redemptive, transformative power of sports, they created a brand, and a culture, that changed everything.', 'shoe_dog.jpg'),

-- ========================================================
-- KỸ NĂNG SỐNG (Self-Help - ID: 11)
-- ========================================================
('978-0735211292', 'Atomic Habits', 'James Clear', 'Avery', 2018, 11, '', 'atomic_habits.jpg'),
('978-1577314806', 'The Power of Now', 'Eckhart Tolle', 'New World Library', 1997, 11, 'To make the journey into the Now we will need to leave our analytical mind and its false created self, the ego, behind. From the very first page of Eckhart Tolle''s extraordinary book, we move rapidly into a significantly higher altitude where we breathe a lighter air. We become connected to the indestructible essence of our Being, “The eternal, ever present One Life beyond the myriad forms of life that are subject to birth and death.” Although the journey is challenging, Eckhart Tolle uses simple language and an easy question-and-answer format to guide us. A word-of-mouth phenomenon since its first publication,  The Power of Now  is one of those rare books with the power to create an experience in readers, one that can radically change their lives for the better.', 'power_of_now.jpg'),
('978-0062457714', 'The Subtle Art of Not Giving a F*ck', 'Mark Manson', 'Harper', 2016, 11, '', 'subtle_art.jpg'),
('978-0060937385', 'The Alchemist', 'Paulo Coelho', 'HarperOne', 1988, 11, 'Combining magic, mysticism, wisdom, and wonder into an inspiring tale of self-discovery,  The Alchemist  has become a modern classic, selling millions of copies around the world and transforming the lives of countless readers across generations. Paulo Coelho''s masterpiece tells the mystical story of Santiago, an Andalusian shepherd boy who yearns to travel in search of a worldly treasure. His quest will lead him to riches far different—and far more satisfying—than he ever imagined. Santiago''s journey teaches us about the essential wisdom of listening to our hearts, recognizing opportunity and learning to read the omens strewn along life''s path, and, most importantly, following our dreams.', 'alchemist.jpg'),

-- ========================================================
-- SÁCH THIẾU NHI (Children''s Literature - ID: 12)
-- ========================================================
('978-0399226908', 'The Very Hungry Caterpillar', 'Eric Carle', 'World of Eric Carle', 1969, 12, 'THE all-time classic story, from generation to generation, sold somewhere in the world every 30 seconds! Have you shared it with a child or grandchild in your life? One sunny Sunday, the caterpillar was hatched out of a tiny egg. He was very hungry. On Monday, he ate through one apple; on Tuesday, he ate through three plums--and still he was hungry. When full at last, he made a cocoon around himself and went to sleep, to wake up a few weeks later wonderfully transformed into a butterfly! The brilliantly innovative Eric Carle has dramatized the story of one of Nature''s commonest yet loveliest marvels, the metamorphosis of the butterfly. This audiobook will delight as well as instruct the very youngest listener.', 'hungry_caterpillar.jpg'),
('978-0061124952', 'Charlotte''s Web', 'E. B. White', 'HarperCollins', 1952, 12, 'This beloved book by E. B. White, author of  Stuart Little  and  The Trumpet of the Swan , is a classic of children''s literature that is "just about perfect." This high-quality paperback features vibrant illustrations colorized by Rosemary Wells! Some Pig. Humble. Radiant. These are the words in Charlotte''s Web, high up in Zuckerman''s barn. Charlotte''s spiderweb tells of her feelings for a little pig named Wilbur, who simply wants a friend. They also express the love of a girl named Fern, who saved Wilbur''s life when he was born the runt of his litter. E. B. White''s Newbery Honor Book is a tender novel of friendship, love, life, and death that will continue to be enjoyed by generations to come. This edition contains newly color illustrations by Garth Williams, the acclaimed illustrator of E. B. White''s  Stuart Little  and Laura Ingalls Wilder''s Little House series, among many other books.', 'charlottes_web.jpg'),
('978-1423103349', 'Matilda', 'Roald Dahl', 'Puffin', 1988, 12, 'Matilda is a little girl who is far too good to be true. At age five-and-a-half she''s knocking off double-digit multiplication problems and blitz-reading Dickens. Even more remarkably, her classmates love her even though she''s a super-nerd and the teacher''s pet. But everything is not perfect in Matilda''s world...', 'matilda.jpg'),
('978-0156012195', 'The Little Prince', 'Antoine de Saint-Exupéry', 'Harcourt', 1943, 12, 'A pilot stranded in the desert awakes one morning to see, standing before him, the most extraordinary little fellow. "Please," asks the stranger, "draw me a sheep." And the pilot realizes that when life''s events are too difficult to understand, there is no choice but to succumb to their mysteries. He pulls out pencil and paper... And thus begins this wise and enchanting fable that, in teaching the secret of what is really important in life, has changed forever the world for its readers. Few stories are as widely read and as universally cherished by children and adults alike as The Little Prince, presented here in a stunning new translation with carefully restored artwork. The definitive edition of a worldwide classic, it will capture the hearts of readers of all ages.', 'little_prince.jpg');

-- Book Copies
INSERT INTO BookCopies (book_id, copy_code, status, condition_note) VALUES
-- Sách ID 1 (Dune): 3 bản
(1, 'DN-001', 'Available', 'New'), (1, 'DN-002', 'Borrowed', 'Good'), (1, 'DN-003', 'Available', 'Worn cover'),
-- Sách ID 2 (1984): 2 bản
(2, '1984-001', 'Available', 'Worn'), (2, '1984-002', 'Available', 'Good'),
-- Sách ID 3 (Brief History): 2 bản
(3, 'BHT-001', 'Available', 'New'), (3, 'BHT-002', 'Maintenance', 'Cover torn'),
-- Sách ID 4 (Martian): 3 bản
(4, 'MAR-001', 'Borrowed', 'New'), (4, 'MAR-002', 'Available', 'New'), (4, 'MAR-003', 'Available', 'Good'),
-- Sách ID 5 (Hitchhiker): 2 bản
(5, 'HHG-001', 'Available', 'Good'), (5, 'HHG-002', 'Lost', 'Charge user'),
-- Sách ID 6 (Gone Girl): 2 bản
(6, 'GG-001', 'Available', 'Good'), (6, 'GG-002', 'Borrowed', 'Good'),
-- Sách ID 7 (Silent Patient): 2 bản
(7, 'TSP-001', 'Available', 'New'), (7, 'TSP-002', 'Available', 'New'),
-- Sách ID 8 (Da Vinci): 3 bản
(8, 'DVC-001', 'Available', 'Good'), (8, 'DVC-002', 'Lost', 'Charge user'), (8, 'DVC-003', 'Available', 'New'),
-- Sách ID 9 (Dragon Tattoo): 2 bản
(9, 'GDT-001', 'Available', 'Good'), (9, 'GDT-002', 'Available', 'Good'),
-- Sách ID 10 (And Then There Were None): 2 bản
(10, 'ATT-001', 'Borrowed', 'Old'), (10, 'ATT-002', 'Available', 'Good'),
-- Sách ID 11 (Harry Potter): 5 bản (Sách Hot)
(11, 'HP1-001', 'Borrowed', 'Worn'), (11, 'HP1-002', 'Borrowed', 'Good'), (11, 'HP1-003', 'Available', 'New'), (11, 'HP1-004', 'Available', 'New'), (11, 'HP1-005', 'Maintenance', 'Page missing'),
-- Sách ID 12 (Hobbit): 3 bản
(12, 'HBT-001', 'Available', 'New'), (12, 'HBT-002', 'Borrowed', 'Good'), (12, 'HBT-003', 'Available', 'Good'),
-- Sách ID 13 (Game of Thrones): 3 bản
(13, 'GOT-001', 'Available', 'Good'), (13, 'GOT-002', 'Available', 'New'), (13, 'GOT-003', 'Borrowed', 'Good'),
-- Sách ID 14 (Name of Wind): 2 bản
(14, 'NOW-001', 'Maintenance', 'Binding loose'), (14, 'NOW-002', 'Available', 'Good'),
-- Sách ID 15 (Pride Prejudice): 2 bản
(15, 'PAP-001', 'Available', 'Old'), (15, 'PAP-002', 'Available', 'Good'),
-- Sách ID 16 (Notebook): 2 bản
(16, 'NB-001', 'Borrowed', 'Good'), (16, 'NB-002', 'Available', 'Good'),
-- Sách ID 17 (Fault in Our Stars): 2 bản
(17, 'FOS-001', 'Available', 'New'), (17, 'FOS-002', 'Available', 'New'),
-- Sách ID 18 (Outlander): 2 bản
(18, 'OUT-001', 'Available', 'Good'), (18, 'OUT-002', 'Borrowed', 'Good'),
-- Sách ID 19 (Book Thief): 2 bản
(19, 'BT-001', 'Borrowed', 'New'), (19, 'BT-002', 'Available', 'Good'),
-- Sách ID 20 (All Light): 2 bản
(20, 'ALW-001', 'Available', 'New'), (20, 'ALW-002', 'Available', 'New'),
-- Sách ID 21 (Nightingale): 2 bản
(21, 'NG-001', 'Available', 'Good'), (21, 'NG-002', 'Available', 'Good'),
-- Sách ID 22 (Kite Runner): 2 bản
(22, 'KR-001', 'Available', 'Good'), (22, 'KR-002', 'Available', 'Good'),
-- Sách ID 23 (It): 3 bản
(23, 'IT-001', 'Available', 'New'), (23, 'IT-002', 'Available', 'Good'), (23, 'IT-003', 'Borrowed', 'Worn'),
-- Sách ID 24 (Dracula): 2 bản
(24, 'DRA-001', 'Borrowed', 'Old edition'), (24, 'DRA-002', 'Available', 'New'),
-- Sách ID 25 (Shining): 2 bản
(25, 'SHI-001', 'Available', 'Good'), (25, 'SHI-002', 'Borrowed', 'Good'),
-- Sách ID 26 (Bird Box): 2 bản
(26, 'BB-001', 'Available', 'New'), (26, 'BB-002', 'Available', 'New'),
-- Sách ID 27 (Steve Jobs): 3 bản
(27, 'SJ-001', 'Available', 'Good'), (27, 'SJ-002', 'Borrowed', 'Good'), (27, 'SJ-003', 'Available', 'Good'),
-- Sách ID 28 (Becoming): 2 bản
(28, 'BEC-001', 'Borrowed', 'New'), (28, 'BEC-002', 'Available', 'New'),
-- Sách ID 29 (Educated): 2 bản
(29, 'EDU-001', 'Available', 'New'), (29, 'EDU-002', 'Available', 'Good'),
-- Sách ID 30 (Anne Frank): 2 bản
(30, 'AF-001', 'Available', 'Good'), (30, 'AF-002', 'Borrowed', 'Old'),
-- Sách ID 31 (Sapiens): 3 bản
(31, 'SAP-001', 'Borrowed', 'New'), (31, 'SAP-002', 'Available', 'New'), (31, 'SAP-003', 'Available', 'Good'),
-- Sách ID 32 (Guns Germs): 2 bản
(32, 'GGS-001', 'Available', 'Good'), (32, 'GGS-002', 'Available', 'Good'),
-- Sách ID 33 (Silk Roads): 2 bản
(33, 'SR-001', 'Available', 'New'), (33, 'SR-002', 'Available', 'New'),
-- Sách ID 34 (1776): 2 bản
(34, '1776-001', 'Available', 'Old'), (34, '1776-002', 'Available', 'Good'),
-- Sách ID 35 (Pragmatic Programmer): 3 bản
(35, 'PP-001', 'Borrowed', 'New'), (35, 'PP-002', 'Borrowed', 'Good'), (35, 'PP-003', 'Available', 'New'),
-- Sách ID 36 (C Lang): 2 bản
(36, 'C-001', 'Available', 'Classic'), (36, 'C-002', 'Available', 'New'),
-- Sách ID 37 (Code Complete): 2 bản
(37, 'CC-001', 'Available', 'Good'), (37, 'CC-002', 'Available', 'Good'),
-- Sách ID 38 (Clean Code): 3 bản
(38, 'CLC-001', 'Borrowed', 'Highlighted'), (38, 'CLC-002', 'Available', 'New'), (38, 'CLC-003', 'Available', 'New'),
-- Sách ID 39 (Rich Dad): 2 bản
(39, 'RDPD-001', 'Available', 'Good'), (39, 'RDPD-002', 'Borrowed', 'Good'),
-- Sách ID 40 (Thinking Fast): 2 bản
(40, 'TFS-001', 'Available', 'New'), (40, 'TFS-002', 'Available', 'Good'),
-- Sách ID 41 (Zero to One): 2 bản
(41, 'ZTO-001', 'Borrowed', 'New'), (41, 'ZTO-002', 'Available', 'New'),
-- Sách ID 42 (Shoe Dog): 2 bản
(42, 'SD-001', 'Available', 'Good'), (42, 'SD-002', 'Available', 'Good'),
-- Sách ID 43 (Atomic Habits): 4 bản (Sách Hot)
(43, 'AH-001', 'Borrowed', 'New'), (43, 'AH-002', 'Borrowed', 'New'), (43, 'AH-003', 'Available', 'New'), (43, 'AH-004', 'Available', 'New'),
-- Sách ID 44 (Power of Now): 2 bản
(44, 'PON-001', 'Available', 'Good'), (44, 'PON-002', 'Available', 'Good'),
-- Sách ID 45 (Subtle Art): 2 bản
(45, 'SA-001', 'Available', 'New'), (45, 'SA-002', 'Borrowed', 'Good'),
-- Sách ID 46 (Alchemist): 3 bản
(46, 'ALC-001', 'Borrowed', 'Worn'), (46, 'ALC-002', 'Available', 'Good'), (46, 'ALC-003', 'Available', 'New'),
-- Sách ID 47 (Hungry Caterpillar): 2 bản
(47, 'HC-001', 'Available', 'New'), (47, 'HC-002', 'Available', 'Good'),
-- Sách ID 48 (Charlotte Web): 2 bản
(48, 'CW-001', 'Available', 'Good'), (48, 'CW-002', 'Available', 'Good'),
-- Sách ID 49 (Little Prince): 3 bản
(49, 'LP-001', 'Borrowed', 'New'), (49, 'LP-002', 'Available', 'Good'), (49, 'LP-003', 'Available', 'New');