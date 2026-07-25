-- Buat database
CREATE DATABASE IF NOT EXISTS cinema_reservation;
USE cinema_reservation;

-- Tabel movies
CREATE TABLE movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    genre VARCHAR(50) NOT NULL,
    duration VARCHAR(20) NOT NULL, -- misal "2 jam 15 menit"
    price INT NOT NULL,
    poster VARCHAR(255) DEFAULT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel schedules (jadwal tayang)
CREATE TABLE schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT NOT NULL,
    show_time DATETIME NOT NULL,
    studio VARCHAR(20) NOT NULL,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE
);

-- Tabel reservations
CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT NOT NULL,
    schedule_id INT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    ticket_quantity INT NOT NULL,
    total_price INT NOT NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (movie_id) REFERENCES movies(id),
    FOREIGN KEY (schedule_id) REFERENCES schedules(id)
);

-- Data contoh film
INSERT INTO movies (title, genre, duration, price, poster, description) VALUES
('Avatar: The Way of Water', 'Sci-Fi', '3 jam 12 menit', 50000, 'https://picsum.photos/seed/avatar/400/320', 'Petualangan baru di pandora dengan teknologi canggih.'),
('John Wick: Chapter 4', 'Action', '2 jam 49 menit', 45000, 'https://picsum.photos/seed/johnwick/400/320', 'Kejar-kejaran epik John Wick melawan High Table.'),
('Spider-Man: Across the Spider-Verse', 'Animasi', '2 jam 20 menit', 40000, 'https://picsum.photos/seed/spiderman/400/320', 'Miles Morales berpetualang melintasi multiverse.'),
('The Super Mario Bros. Movie', 'Animasi', '1 jam 32 menit', 35000, 'https://picsum.photos/seed/mario/400/320', 'Mario dan Luigi menyelamatkan princess Peach.'),
('Oppenheimer', 'Drama', '3 jam 0 menit', 55000, 'https://picsum.photos/seed/oppenheimer/400/320', 'Kisah ilmiah dan kontroversi bom atom.'),
('Barbie', 'Komedi', '1 jam 54 menit', 40000, 'https://picsum.photos/seed/barbie/400/320', 'Barbie meninggalkan Barbieland ke dunia nyata.');

-- Data contoh jadwal (untuk setiap film setidaknya 2 jadwal)
INSERT INTO schedules (movie_id, show_time, studio) VALUES
(1, '2026-07-25 10:00:00', 'Studio 1'),
(1, '2026-07-25 13:30:00', 'Studio 2'),
(2, '2026-07-25 11:00:00', 'Studio 3'),
(2, '2026-07-25 14:30:00', 'Studio 1'),
(3, '2026-07-25 09:30:00', 'Studio 2'),
(3, '2026-07-25 12:30:00', 'Studio 4'),
(4, '2026-07-25 10:30:00', 'Studio 3'),
(4, '2026-07-25 14:00:00', 'Studio 2'),
(5, '2026-07-25 11:30:00', 'Studio 1'),
(5, '2026-07-25 15:00:00', 'Studio 4'),
(6, '2026-07-25 12:00:00', 'Studio 3'),
(6, '2026-07-25 16:00:00', 'Studio 2');