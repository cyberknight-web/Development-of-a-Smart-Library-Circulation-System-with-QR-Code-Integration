-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 02, 2026 at 12:51 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smartlibrary`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `created_at`) VALUES
(1, 'Evsu123', '$2y$10$u6kSgYUNzZ50RLT1hCfVce3HHs76.z/G0.R8cA.s9ItshKQwDrW/u', '2026-04-25 09:45:13');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int(10) UNSIGNED NOT NULL,
  `accession_no` varchar(100) DEFAULT NULL,
  `isbn` varchar(50) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) DEFAULT NULL,
  `publisher` varchar(255) DEFAULT NULL,
  `publication_year` varchar(10) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `copies_total` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `copies_available` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `status` enum('available','not_available') NOT NULL DEFAULT 'available',
  `imported_from_excel` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `accession_no`, `isbn`, `title`, `author`, `publisher`, `publication_year`, `category`, `location`, `copies_total`, `copies_available`, `status`, `imported_from_excel`, `created_at`, `updated_at`) VALUES
(1621, '123', '1.23124E+11', 'harryy', 'aa', 'dva', '0', 'ava', 'av', 15, 15, 'available', 1, '2026-05-01 15:41:43', '2026-05-01 15:46:36'),
(1622, '1313', '1313', 'hello', 'sgs', 'bxxcb', '0', 'xbxbx', 'xbxbx', 12, 12, 'available', 1, '2026-05-01 15:41:43', '2026-05-01 15:46:36'),
(1623, '13131', '231', 'sdfs', 'afv', 'vsvsv', '0', 'svs', 'svs', 14, 14, 'available', 1, '2026-05-01 15:41:43', NULL),
(1624, '4242', '14134', 'sfsf', 'fsf', 'sfsf', '0', 'sfsf', 'sfs', 34, 34, 'available', 1, '2026-05-01 15:41:43', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `books_restore_bin`
--

CREATE TABLE `books_restore_bin` (
  `id` int(10) UNSIGNED NOT NULL,
  `accession_no` varchar(100) DEFAULT NULL,
  `isbn` varchar(50) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) DEFAULT NULL,
  `publisher` varchar(255) DEFAULT NULL,
  `publication_year` varchar(10) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `copies_total` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `copies_available` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `status` enum('available','not_available') NOT NULL DEFAULT 'available',
  `imported_from_excel` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `books_restore_bin`
--

INSERT INTO `books_restore_bin` (`id`, `accession_no`, `isbn`, `title`, `author`, `publisher`, `publication_year`, `category`, `location`, `copies_total`, `copies_available`, `status`, `imported_from_excel`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1421, '1', '9.78E+12', 'To Kill a Mockingbird', 'Harper Lee', 'Penguin Books', '1988', 'Fiction', 'Fiction', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1422, '2', '9.78E+12', '1984', 'George Orwell', 'HarperCollins', '1990', 'Classic Literature', 'Classic Literature', 5, 4, 'available', 1, '2026-04-29 05:16:18', '2026-04-29 05:38:06', '2026-05-01 15:41:23'),
(1423, '3', '9.78E+12', 'Pride and Prejudice', 'Jane Austen', 'Random House', '1991', 'Romance', 'Romance', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1424, '4', '9.78E+12', 'The Great Gatsby', 'F. Scott Fitzgerald', 'Simon & Schuster', '1992', 'Adventure', 'Adventure', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1425, '5', '9.78E+12', 'Moby-Dick', 'Herman Melville', 'Macmillan Publishers', '1993', 'Fantasy', 'Fantasy', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1426, '6', '9.78E+12', 'War and Peace', 'Leo Tolstoy', 'Oxford University Press', '1994', 'Science Fiction', 'Science Fiction', 1, 1, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1427, '7', '9.78E+12', 'The Catcher in the Rye', 'J.D. Salinger', 'Cambridge University Press', '1995', 'Mystery', 'Mystery', 7, 7, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1428, '8', '9.78E+12', 'The Hobbit', 'J.R.R. Tolkien', 'Pearson Education', '1996', 'Thriller', 'Thriller', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1429, '9', '9.78E+12', 'Crime and Punishment', 'Fyodor Dostoevsky', 'Scholastic Press', '1997', 'Historical Fiction', 'Historical Fiction', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1430, '10', '9.78E+12', 'Jane Eyre', 'Charlotte Bronte', 'Bloomsbury Publishing', '1998', 'Young Adult', 'Young Adult', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1431, '11', '9.78E+12', 'Brave New World', 'Aldous Huxley', 'Hachette Book Group', '1999', 'Children\'s Literature', 'Children\'s Literature', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1432, '12', '9.78E+12', 'Wuthering Heights', 'Homer', 'Wiley', '2000', 'Horror', 'Horror', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1433, '13', '9.78E+12', 'Great Expectations', 'Emily Bronte', 'Springer', '2001', 'Drama', 'Drama', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1434, '14', '9.78E+12', 'Little Women', 'Charles Dickens', 'McGraw-Hill Education', '2002', 'Dystopian', 'Dystopian', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1435, '15', '9.78E+12', 'The Alchemist', 'Louisa May Alcott', 'MIT Press', '2003', 'Philosophy', 'Philosophy', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1436, '16', '9.78E+12', 'The Kite Runner', 'Paulo Coelho', 'Princeton University Press', '2004', 'Psychology', 'Psychology', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1437, '17', '9.78E+12', 'Life of Pi', 'Khaled Hosseini', 'Vintage Books', '2005', 'Self Help', 'Self Help', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1438, '18', '9.78E+12', 'The Book Thief', 'Yann Martel', 'Doubleday', '2006', 'Business', 'Business', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1439, '19', '9.78E+12', 'The Da Vinci Code', 'Markus Zusak', 'Knopf', '2007', 'Finance', 'Finance', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1440, '20', '9.78E+12', 'Angels & Demons', 'Dan Brown', 'Little Brown and Company', '2008', 'Technology', 'Technology', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1441, '21', '9.78E+12', 'The Girl with the Dragon Tattoo', 'Stieg Larsson', 'Tor Books', '2009', 'Programming', 'Programming', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1442, '22', '9.78E+12', 'The Hunger Games', 'Suzanne Collins', 'Crown Publishing', '2010', 'Computer Science', 'Computer Science', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1443, '23', '9.78E+12', 'Catching Fire', 'J.K. Rowling', 'Ballantine Books', '2011', 'Artificial Intelligence', 'Artificial Intelligence', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1444, '24', '9.78E+12', 'Mockingjay', 'C.S. Lewis', 'Bantam Books', '2012', 'Data Science', 'Data Science', 1, 1, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1445, '25', '9.78E+12', 'Harry Potter and the Sorcerer\'s Stone', 'Ray Bradbury', 'Scribner', '2013', 'Machine Learning', 'Machine Learning', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1446, '26', '9.78E+12', 'Harry Potter and the Chamber of Secrets', 'Bram Stoker', 'Avon Books', '2014', 'Cybersecurity', 'Cybersecurity', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1447, '27', '9.78E+12', 'Harry Potter and the Prisoner of Azkaban', 'Mary Shelley', 'Orbit Books', '2015', 'Networking', 'Networking', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1448, '28', '9.78E+12', 'Harry Potter and the Goblet of Fire', 'Stephen King', 'Penguin Classics', '2016', 'Database Systems', 'Database Systems', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1449, '29', '9.78E+12', 'Harry Potter and the Order of the Phoenix', 'Frank Herbert', 'Harper Perennial', '2017', 'Software Engineering', 'Software Engineering', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1450, '30', '9.78E+12', 'Harry Potter and the Half-Blood Prince', 'Andy Weir', 'Penguin Random House', '2018', 'Web Development', 'Web Development', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1451, '31', '9.78E+12', 'Harry Potter and the Deathly Hallows', 'Ernest Cline', 'Pan Macmillan', '2019', 'Mobile Development', 'Mobile Development', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1452, '32', '9.78E+12', 'The Fellowship of the Ring', 'Orson Scott Card', 'OReilly Media', '2020', 'Game Development', 'Game Development', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1453, '33', '9.78E+12', 'The Two Towers', 'John Green', 'Apress', '2021', 'Cloud Computing', 'Cloud Computing', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1454, '34', '9.78E+12', 'The Return of the King', 'James Dashner', 'Packt Publishing', '2022', 'DevOps', 'DevOps', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1455, '35', '9.78E+12', 'The Lion, the Witch and the Wardrobe', 'Veronica Roth', 'No Starch Press', '2023', 'Information Technology', 'Information Technology', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1456, '36', '9.78E+12', 'Animal Farm', 'Lois Lowry', 'Addison-Wesley', '1995', 'Mathematics', 'Mathematics', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1457, '37', '9.78E+12', 'Fahrenheit 451', 'Margaret Atwood', 'Manning Publications', '1998', 'Statistics', 'Statistics', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1458, '38', '9.78E+12', 'Dracula', 'Cormac McCarthy', 'Routledge', '2001', 'Physics', 'Physics', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1459, '39', '9.78E+12', 'Frankenstein', 'George R.R. Martin', 'Palgrave Macmillan', '2004', 'Chemistry', 'Chemistry', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1460, '40', '9.78E+12', 'The Shining', 'Patrick Rothfuss', 'Elsevier', '2007', 'Biology', 'Biology', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1461, '41', '9.78E+12', 'It', 'Alex Michaelides', 'Cengage Learning', '2010', 'Astronomy', 'Astronomy', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1462, '42', '9.78E+12', 'Dune', 'Gillian Flynn', 'Thames and Hudson', '2013', 'Environmental Science', 'Environmental Science', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1463, '43', '9.78E+12', 'The Martian', 'Paula Hawkins', 'Faber and Faber', '2016', 'Medicine', 'Medicine', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1464, '44', '9.78E+12', 'Ready Player One', 'Erin Morgenstern', 'Verso Books', '2019', 'Health', 'Health', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1465, '45', '9.78E+12', 'Ender\'s Game', 'Madeline Miller', 'Blackwell Publishing', '2022', 'Nutrition', 'Nutrition', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1466, '46', '9.78E+12', 'The Fault in Our Stars', 'Tara Westover', 'Tuttle Publishing', '1992', 'Fitness', 'Fitness', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1467, '47', '9.78E+12', 'Looking for Alaska', 'Michelle Obama', 'Chronicle Books', '1996', 'Sports', 'Sports', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1468, '48', '9.78E+12', 'Paper Towns', 'Walter Isaacson', 'Workman Publishing', '2000', 'Education', 'Education', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1469, '49', '9.78E+12', 'The Maze Runner', 'Yuval Noah Harari', 'Sourcebooks', '2004', 'Teaching', 'Teaching', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1470, '50', '9.78E+12', 'Divergent', 'James Clear', 'Greenleaf Book Group', '2008', 'Language Learning', 'Language Learning', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1471, '51', '9.78E+12', 'Insurgent', 'Cal Newport', 'Basic Books', '2012', 'Linguistics', 'Linguistics', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1472, '52', '9.78E+12', 'Allegiant', 'Charles Duhigg', 'Beacon Press', '2016', 'History', 'History', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1473, '53', '9.78E+12', 'The Giver', 'Daniel Kahneman', 'Island Press', '2020', 'World History', 'World History', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1474, '54', '9.78E+12', 'The Handmaid\'s Tale', 'Robert Kiyosaki', 'North Atlantic Books', '2023', 'Ancient History', 'Ancient History', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1475, '55', '9.78E+12', 'The Road', 'Stephen Covey', 'Skyhorse Publishing', '1997', 'Medieval History', 'Medieval History', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1476, '56', '9.78E+12', 'A Game of Thrones', 'Dale Carnegie', 'Rowman and Littlefield', '2001', 'Modern History', 'Modern History', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1477, '57', '9.78E+12', 'A Clash of Kings', 'Simon Sinek', 'University of Chicago Press', '2005', 'Political Science', 'Political Science', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1478, '58', '9.78E+12', 'A Storm of Swords', 'Peter Thiel', 'University of California Press', '2009', 'Government', 'Government', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1479, '59', '9.78E+12', 'A Feast for Crows', 'Eric Ries', 'Yale University Press', '2013', 'Law', 'Law', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1480, '60', '9.78E+12', 'A Dance with Dragons', 'Jim Collins', 'Harvard University Press', '2017', 'Economics', 'Economics', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1481, '61', '9.78E+12', 'The Name of the Wind', 'Timothy Ferriss', 'Columbia University Press', '2021', 'Sociology', 'Sociology', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1482, '62', '9.78E+12', 'The Wise Man\'s Fear', 'David Goggins', 'Stanford University Press', '1994', 'Anthropology', 'Anthropology', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1483, '63', '9.78E+12', 'The Silent Patient', 'Angela Duckworth', 'Duke University Press', '1999', 'Cultural Studies', 'Cultural Studies', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1484, '64', '9.78E+12', 'Gone Girl', 'Carol Dweck', 'Indiana University Press', '2003', 'Religion', 'Religion', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1485, '65', '9.78E+12', 'The Girl on the Train', 'Malcolm Gladwell', 'Johns Hopkins University Press', '2007', 'Spirituality', 'Spirituality', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1486, '66', '9.78E+12', 'The Night Circus', 'Daniel Pink', 'University of Minnesota Press', '2011', 'Mythology', 'Mythology', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1487, '67', '9.78E+12', 'Circe', 'William McRaven', 'University of Nebraska Press', '2015', 'Philosophy Classics', 'Philosophy Classics', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1488, '68', '9.78E+12', 'The Song of Achilles', 'Morgan Housel', 'University of Texas Press', '2019', 'Ethics', 'Ethics', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1489, '69', '9.78E+12', 'Educated', 'Haruki Murakami', 'University of Washington Press', '2022', 'Logic', 'Logic', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1490, '70', '9.78E+12', 'Becoming', 'Gabriel Garcia Marquez', 'University of Toronto Press', '1991', 'Critical Thinking', 'Critical Thinking', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1491, '71', '9.78E+12', 'Steve Jobs', 'Ernest Hemingway', 'University of Michigan Press', '1996', 'Art', 'Art', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1492, '72', '9.78E+12', 'Sapiens', 'Albert Camus', 'University of Arizona Press', '2001', 'Art History', 'Art History', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1493, '73', '9.78E+12', 'Homo Deus', 'Jean Paul Sartre', 'University of Oklahoma Press', '2006', 'Painting', 'Painting', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1494, '74', '9.78E+12', 'Atomic Habits', 'Miguel de Cervantes', 'University Press of Florida', '2011', 'Photography', 'Photography', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1495, '75', '9.78E+12', 'Deep Work', 'Victor Hugo', 'University of Georgia Press', '2016', 'Design', 'Design', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1496, '76', '9.78E+12', 'The Power of Habit', 'Gustave Flaubert', 'University of New Mexico Press', '2021', 'Graphic Design', 'Graphic Design', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1497, '77', '9.78E+12', 'Thinking, Fast and Slow', 'Alexandre Dumas', 'University of Wisconsin Press', '1993', 'Architecture', 'Architecture', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1498, '78', '9.78E+12', 'Rich Dad Poor Dad', 'Mark Twain', 'University of Illinois Press', '1998', 'Music', 'Music', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1499, '79', '9.78E+12', 'The 7 Habits of Highly Effective People', 'Jack London', 'University of Pennsylvania Press', '2003', 'Music Theory', 'Music Theory', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1500, '80', '9.78E+12', 'How to Win Friends and Influence People', 'Rudyard Kipling', 'University of Kentucky Press', '2008', 'Film', 'Film', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1501, '81', '9.78E+12', 'Start With Why', 'Agatha Christie', 'University of North Carolina Press', '2013', 'Theater', 'Theater', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1502, '82', '9.78E+12', 'Zero to One', 'Arthur Conan Doyle', 'University of South Carolina Press', '2018', 'Poetry', 'Poetry', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1503, '83', '9.78E+12', 'The Lean Startup', 'Oscar Wilde', 'University of Virginia Press', '2022', 'Literary Criticism', 'Literary Criticism', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1504, '84', '9.78E+12', 'Good to Great', 'Toni Morrison', 'University of Alabama Press', '1990', 'Journalism', 'Journalism', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1505, '85', '9.78E+12', 'Built to Last', 'Ralph Ellison', 'University Press of Kansas', '1995', 'Media Studies', 'Media Studies', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1506, '86', '9.78E+12', 'The 4-Hour Workweek', 'Zora Neale Hurston', 'University of Arkansas Press', '2000', 'Communication', 'Communication', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1507, '87', '9.78E+12', 'Can\'t Hurt Me', 'Chinua Achebe', 'University Press of Mississippi', '2005', 'Writing', 'Writing', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1508, '88', '9.78E+12', 'Grit', 'Arundhati Roy', 'University of Notre Dame Press', '2010', 'Creative Writing', 'Creative Writing', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1509, '89', '9.78E+12', 'Mindset', 'Alice Walker', 'University of Pittsburgh Press', '2015', 'Publishing', 'Publishing', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1510, '90', '9.78E+12', 'Outliers', 'Kurt Vonnegut', 'University of Rochester Press', '2020', 'Travel', 'Travel', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1511, '91', '9.78E+12', 'Blink', 'Ursula Le Guin', 'University of Akron Press', '2023', 'Geography', 'Geography', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1512, '92', '9.78E+12', 'Drive', 'William Gibson', 'University of Nevada Press', '1994', 'Tourism', 'Tourism', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1513, '93', '9.78E+12', 'Make Your Bed', 'Neal Stephenson', 'University of Iowa Press', '1998', 'Cooking', 'Cooking', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1514, '94', '9.78E+12', 'Ikigai', 'Isaac Asimov', 'University of Missouri Press', '2002', 'Food and Drink', 'Food and Drink', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1515, '95', '9.78E+12', 'Essentialism', 'Philip K Dick', 'University of Tennessee Press', '2006', 'Baking', 'Baking', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1516, '96', '9.78E+12', 'The Psychology of Money', 'HG Wells', 'University of Hawaii Press', '2010', 'Gardening', 'Gardening', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1517, '97', '9.78E+12', 'Norwegian Wood', 'Jules Verne', 'University Press of Colorado', '2014', 'Agriculture', 'Agriculture', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1518, '98', '9.78E+12', 'Kafka on the Shore', 'Nathaniel Hawthorne', 'University of Massachusetts Press', '2018', 'Nature', 'Nature', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1519, '99', '9.78E+12', 'The Wind-Up Bird Chronicle', 'Henry David Thoreau', 'University Press of New England', '2022', 'Wildlife', 'Wildlife', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1520, '100', '9.78E+12', 'One Hundred Years of Solitude', 'Walt Whitman', 'University Press of New Mexico', '1993', 'Animals', 'Animals', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1521, '101', '9.78E+12', 'Love in the Time of Cholera', 'Plato', 'University Press of Kentucky', '1997', 'Pets', 'Pets', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1522, '102', '9.78E+12', 'The Old Man and the Sea', 'Marcus Aurelius', 'University Press of Mississippi', '2001', 'Crafts', 'Crafts', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1523, '103', '9.78E+12', 'A Farewell to Arms', 'Niccolo Machiavelli', 'University Press of Florida', '2005', 'DIY', 'DIY', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1524, '104', '9.78E+12', 'For Whom the Bell Tolls', 'Sun Tzu', 'University Press of Kansas', '2009', 'Home Improvement', 'Home Improvement', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1525, '105', '9.78E+12', 'The Sun Also Rises', 'Viktor Frankl', 'University Press of Arkansas', '2013', 'Interior Design', 'Interior Design', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1526, '106', '9.78E+12', 'The Stranger', 'Anne Frank', 'University Press of Georgia', '2017', 'Fashion', 'Fashion', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1527, '107', '9.78E+12', 'The Plague', 'Nelson Mandela', 'University Press of Alabama', '2021', 'Beauty', 'Beauty', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1528, '108', '9.78E+12', 'Nausea', 'Malcolm X', 'University Press of South Carolina', '1992', 'Parenting', 'Parenting', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1529, '109', '9.78E+12', 'The Brothers Karamazov', 'Jon Krakauer', 'University Press of Virginia', '1996', 'Family', 'Family', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1530, '110', '9.78E+12', 'Notes from Underground', 'Trevor Noah', 'University Press of North Carolina', '2000', 'Relationships', 'Relationships', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1531, '111', '9.78E+12', 'Anna Karenina', 'William Shakespeare', 'University Press of Tennessee', '2004', 'Personal Development', 'Personal Development', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1532, '112', '9.78E+12', 'The Death of Ivan Ilyich', 'John Steinbeck', 'University Press of Oklahoma', '2008', 'Motivation', 'Motivation', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1533, '113', '9.78E+12', 'Don Quixote', 'LM Montgomery', 'University Press of Texas', '2012', 'Productivity', 'Productivity', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1534, '114', '9.78E+12', 'Les Mis?rables', 'Frances Hodgson Burnett', 'University Press of Washington', '2016', 'Leadership', 'Leadership', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1535, '115', '9.78E+12', 'The Hunchback of Notre-Dame', 'Johanna Spyri', 'University Press of Michigan', '2020', 'Entrepreneurship', 'Entrepreneurship', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1536, '116', '9.78E+12', 'Madame Bovary', 'Robert Louis Stevenson', 'University Press of Minnesota', '2023', 'Marketing', 'Marketing', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1537, '117', '9.78E+12', 'The Count of Monte Cristo', 'Daphne du Maurier', 'University Press of Wisconsin', '1991', 'Sales', 'Sales', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1538, '118', '9.78E+12', 'The Three Musketeers', 'Ken Follett', 'University Press of Nebraska', '1995', 'Customer Service', 'Customer Service', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1539, '119', '9.78E+12', 'Sense and Sensibility', 'Brandon Sanderson', 'University Press of Indiana', '1999', 'Management', 'Management', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1540, '120', '9.78E+12', 'Emma', 'Rick Riordan', 'University Press of Illinois', '2003', 'Project Management', 'Project Management', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1541, '121', '9.78E+12', 'Persuasion', 'Neil Gaiman', 'University Press of Pennsylvania', '2007', 'Startups', 'Startups', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1542, '122', '9.78E+12', 'Mansfield Park', 'Terry Pratchett', 'University Press of Notre Dame', '2011', 'Investing', 'Investing', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1543, '123', '9.78E+12', 'Oliver Twist', 'Douglas Adams', 'University Press of Pittsburgh', '2015', 'Real Estate', 'Real Estate', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1544, '124', '9.78E+12', 'A Tale of Two Cities', 'Arthur C Clarke', 'University Press of Rochester', '2019', 'Accounting', 'Accounting', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1545, '125', '9.78E+12', 'David Copperfield', 'Carl Sagan', 'University Press of Akron', '2022', 'Banking', 'Banking', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1546, '126', '9.78E+12', 'Bleak House', 'Stephen Hawking', 'University Press of Nevada', '1994', 'Insurance', 'Insurance', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1547, '127', '9.78E+12', 'The Secret Garden', 'Richard Dawkins', 'University Press of Iowa', '1998', 'Public Speaking', 'Public Speaking', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1548, '128', '9.78E+12', 'Anne of Green Gables', 'Noam Chomsky', 'University Press of Missouri', '2002', 'Negotiation', 'Negotiation', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1549, '129', '9.78E+12', 'Heidi', 'Malala Yousafzai', 'University Press of Hawaii', '2006', 'Strategy', 'Strategy', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1550, '130', '9.78E+12', 'Treasure Island', 'Chimamanda Ngozi Adichie', 'University Press of Colorado', '2010', 'Innovation', 'Innovation', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1551, '131', '9.78E+12', 'Kidnapped', 'Salman Rushdie', 'University Press of Massachusetts', '2014', 'E Commerce', 'E Commerce', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1552, '132', '9.78E+12', 'The Adventures of Tom Sawyer', 'Kazuo Ishiguro', 'University Press of New England', '2018', 'Digital Marketing', 'Digital Marketing', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1553, '133', '9.78E+12', 'Adventures of Huckleberry Finn', 'Zadie Smith', 'Penguin Modern Classics', '2022', 'Social Media', 'Social Media', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1554, '134', '9.78E+12', 'The Call of the Wild', 'Ian McEwan', 'Vintage International', '1993', 'Content Creation', 'Content Creation', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1555, '135', '9.78E+12', 'White Fang', 'Hilary Mantel', 'Harper Voyager', '1997', 'Blogging', 'Blogging', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1556, '136', '9.78E+12', 'The Jungle Book', 'Anthony Doerr', 'Del Rey Books', '2001', 'Podcasting', 'Podcasting', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1557, '137', '9.78E+12', 'Kim', 'Donna Tartt', 'Ace Books', '2005', 'Photography Guide', 'Photography Guide', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1558, '138', '9.78E+12', 'Rebecca', 'Colson Whitehead', 'DAW Books', '2009', 'Video Production', 'Video Production', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1559, '139', '9.78E+12', 'Jamaica Inn', 'Ann Patchett', 'Baen Books', '2013', 'Animation', 'Animation', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1560, '140', '9.78E+12', 'Murder on the Orient Express', 'Jhumpa Lahiri', 'Subterranean Press', '2017', 'Illustration', 'Illustration', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1561, '141', '9.78E+12', 'And Then There Were None', 'Elena Ferrante', 'Titan Books', '2021', 'Comics', 'Comics', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1562, '142', '9.78E+12', 'The A.B.C. Murders', 'Paulo Freire', 'Angry Robot', '1992', 'Graphic Novels', 'Graphic Novels', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1563, '143', '9.78E+12', 'Death on the Nile', 'Edward Said', 'Head of Zeus', '1996', 'Manga', 'Manga', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1564, '144', '9.78E+12', 'The Murder of Roger Ackroyd', 'Frantz Fanon', 'Canongate Books', '2000', 'Anime', 'Anime', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1565, '145', '9.78E+12', 'The Adventures of Sherlock Holmes', 'Michel Foucault', 'Granta Books', '2004', 'Gaming', 'Gaming', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1566, '146', '9.78E+12', 'The Hound of the Baskervilles', 'Jacques Derrida', 'Atlantic Books', '2008', 'Esports', 'Esports', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1567, '147', '9.78E+12', 'A Study in Scarlet', 'Hannah Arendt', 'Quercus Publishing', '2012', 'Board Games', 'Board Games', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1568, '148', '9.78E+12', 'The Sign of Four', 'Simone de Beauvoir', 'Profile Books', '2016', 'Puzzles', 'Puzzles', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1569, '149', '9.78E+12', 'The Picture of Dorian Gray', 'Slavoj Zizek', 'Constable and Robinson', '2020', 'Trivia', 'Trivia', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1570, '150', '9.78E+12', 'The Importance of Being Earnest', 'Alain de Botton', 'Little Tiger Press', '2023', 'Humor', 'Humor', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1571, '151', '9.78E+12', 'Beloved', 'Ryan Holiday', 'Walker Books', '1990', 'Satire', 'Satire', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1572, '152', '9.78E+12', 'Song of Solomon', 'Robert Greene', 'Usborne Publishing', '1995', 'Memoir', 'Memoir', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1573, '153', '9.78E+12', 'The Bluest Eye', 'Napoleon Hill', 'Egmont Books', '2000', 'Biography', 'Biography', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1574, '154', '9.78E+12', 'Invisible Man', 'Brian Tracy', 'Nosy Crow', '2005', 'Autobiography', 'Autobiography', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1575, '155', '9.78E+12', 'Their Eyes Were Watching God', 'Tony Robbins', 'Chicken House', '2010', 'True Crime', 'True Crime', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1576, '156', '9.78E+12', 'Things Fall Apart', 'Seth Godin', 'Andrews McMeel Publishing', '2015', 'Crime', 'Crime', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1577, '157', '9.78E+12', 'No Longer at Ease', 'Gary Vaynerchuk', 'Chronicle Prism', '2020', 'Detective', 'Detective', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1578, '158', '9.78E+12', 'The God of Small Things', 'Adam Grant', 'New Harbinger Publications', '2023', 'Legal Thriller', 'Legal Thriller', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1579, '159', '9.78E+12', 'A Thousand Splendid Suns', 'Daniel Goleman', 'Hay House', '1991', 'Spy Fiction', 'Spy Fiction', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1580, '160', '9.78E+12', 'The Color Purple', 'Brene Brown', 'Sounds True', '1996', 'War', 'War', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1581, '161', '9.78E+12', 'Slaughterhouse-Five', 'Susan Cain', 'Shambhala Publications', '2001', 'Military History', 'Military History', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1582, '162', '9.78E+12', 'Cat\'s Cradle', 'Virginia Woolf', 'Inner Traditions', '2006', 'Survival', 'Survival', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1583, '163', '9.78E+12', 'Breakfast of Champions', 'Thomas Hardy', 'Parallax Press', '2011', 'Adventure Travel', 'Adventure Travel', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1584, '164', '9.78E+12', 'The Left Hand of Darkness', 'George Eliot', 'North Point Press', '2016', 'Mountaineering', 'Mountaineering', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1585, '165', '9.78E+12', 'A Wizard of Earthsea', 'DH Lawrence', 'Milkweed Editions', '2021', 'Exploration', 'Exploration', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1586, '166', '9.78E+12', 'The Dispossessed', 'Herman Hesse', 'Graywolf Press', '1993', 'Transportation', 'Transportation', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1587, '167', '9.78E+12', 'Neuromancer', 'Thomas Mann', 'Coffee House Press', '1998', 'Automotive', 'Automotive', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1588, '168', '9.78E+12', 'Snow Crash', 'Italo Calvino', 'Akashic Books', '2003', 'Aviation', 'Aviation', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1589, '169', '9.78E+12', 'Foundation', 'Umberto Eco', 'Europa Editions', '2008', 'Engineering', 'Engineering', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1590, '170', '9.78E+12', 'Foundation and Empire', 'Roberto Bolano', 'Archipelago Books', '2013', 'Robotics', 'Robotics', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1591, '171', '9.78E+12', 'Second Foundation', 'Mario Vargas Llosa', 'Seven Stories Press', '2018', 'Electronics', 'Electronics', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1592, '172', '9.78E+12', 'I, Robot', 'Isabel Allende', 'Melville House', '2022', 'Mechanical Engineering', 'Mechanical Engineering', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1593, '173', '9.78E+12', 'Do Androids Dream of Electric Sheep?', 'Octavia Butler', 'Soft Skull Press', '1994', 'Civil Engineering', 'Civil Engineering', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1594, '174', '9.78E+12', 'Ubik', 'NK Jemisin', 'OR Books', '1999', 'Electrical Engineering', 'Electrical Engineering', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1595, '175', '9.78E+12', 'A Scanner Darkly', 'Leigh Bardugo', 'Counterpoint Press', '2004', 'Chemical Engineering', 'Chemical Engineering', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1596, '176', '9.78E+12', 'The Time Machine', 'VE Schwab', 'Grove Press', '2009', 'Industrial Engineering', 'Industrial Engineering', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1597, '177', '9.78E+12', 'The War of the Worlds', 'Cassandra Clare', 'New Directions Publishing', '2014', 'Space Science', 'Space Science', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1598, '178', '9.78E+12', 'The Invisible Man', 'Sarah J Maas', 'New York Review Books', '2019', 'Rocket Science', 'Rocket Science', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1599, '179', '9.78E+12', '20,000 Leagues Under the Sea', 'Colleen Hoover', 'Dalkey Archive Press', '2023', 'Astrophysics', 'Astrophysics', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1600, '180', '9.78E+12', 'Journey to the Center of the Earth', 'Taylor Jenkins Reid', 'Europa Press', '1992', 'Quantum Physics', 'Quantum Physics', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1601, '181', '9.78E+12', 'Around the World in Eighty Days', 'Emily Henry', 'Archway Publishing', '1997', 'Nanotechnology', 'Nanotechnology', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1602, '182', '9.78E+12', 'The Scarlet Letter', 'Kristin Hannah', 'Balboa Press', '2002', 'Biotechnology', 'Biotechnology', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1603, '183', '9.78E+12', 'Walden', 'Jojo Moyes', 'AuthorHouse', '2007', 'Genetics', 'Genetics', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1604, '184', '9.78E+12', 'Leaves of Grass', 'Nicholas Sparks', 'BookBaby', '2012', 'Neuroscience', 'Neuroscience', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1605, '185', '9.78E+12', 'The Republic', 'John Grisham', 'IngramSpark', '2017', 'Psychiatry', 'Psychiatry', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1606, '186', '9.78E+12', 'Meditations', 'Michael Crichton', 'Lulu Press', '2022', 'Public Health', 'Public Health', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1607, '187', '9.78E+12', 'The Prince', 'Lee Child', 'Notion Press', '1991', 'Epidemiology', 'Epidemiology', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1608, '188', '9.78E+12', 'The Art of War', 'Tom Clancy', 'Partridge Publishing', '1996', 'Pharmacology', 'Pharmacology', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1609, '189', '9.78E+12', 'Man\'s Search for Meaning', 'Robert Ludlum', 'Xlibris', '2001', 'Dentistry', 'Dentistry', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1610, '190', '9.78E+12', 'The Diary of a Young Girl', 'James Patterson', 'iUniverse', '2006', 'Nursing', 'Nursing', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1611, '191', '9.78E+12', 'Long Walk to Freedom', 'David Baldacci', 'Outskirts Press', '2011', 'Veterinary Medicine', 'Veterinary Medicine', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1612, '192', '9.78E+12', 'The Autobiography of Malcolm X', 'Harlan Coben', 'Red Wheel Weiser', '2016', 'Mental Health', 'Mental Health', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1613, '193', '9.78E+12', 'Into the Wild', 'Dennis Lehane', 'Skyhorse Kids', '2021', 'Wellness', 'Wellness', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1614, '194', '9.78E+12', 'Into Thin Air', 'Lucy Foley', 'Sterling Publishing', '1993', 'Yoga', 'Yoga', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1615, '195', '9.78E+12', 'Born a Crime', 'Ruth Ware', 'Barrons Educational Series', '1998', 'Meditation', 'Meditation', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1616, '196', '9.78E+12', 'Educating Rita', 'Harper Lee', 'Kaplan Publishing', '2003', 'Mindfulness', 'Mindfulness', 5, 5, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1617, '197', '9.78E+12', 'Hamlet', 'George Orwell', 'McFarland and Company', '2008', 'Spiritual Growth', 'Spiritual Growth', 4, 4, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1618, '198', '9.78E+12', 'Macbeth', 'Jane Austen', 'Zed Books', '2013', 'Self Discovery', 'Self Discovery', 6, 6, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1619, '199', '9.78E+12', 'Romeo and Juliet', 'J.K. Rowling', 'Pluto Press', '2018', 'Life Coaching', 'Life Coaching', 2, 2, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23'),
(1620, '200', '9.78E+12', 'Othello', 'J.K. Rowling', 'Fernwood Publishing', '2022', 'Goal Setting', 'Goal Setting', 3, 3, 'available', 1, '2026-04-29 05:16:18', NULL, '2026-05-01 15:41:23');

-- --------------------------------------------------------

--
-- Table structure for table `borrow_requests`
--

CREATE TABLE `borrow_requests` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `qr_token` varchar(100) NOT NULL,
  `status` enum('pending','approved','rejected','claimed','returned') NOT NULL DEFAULT 'pending',
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `claimed_at` timestamp NULL DEFAULT NULL,
  `returned_at` timestamp NULL DEFAULT NULL,
  `admin_id_approved` int(10) UNSIGNED DEFAULT NULL,
  `admin_id_rejected` int(10) UNSIGNED DEFAULT NULL,
  `admin_id_claimed` int(10) UNSIGNED DEFAULT NULL,
  `admin_id_returned` int(10) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `borrow_requests`
--

INSERT INTO `borrow_requests` (`id`, `student_id`, `qr_token`, `status`, `requested_at`, `approved_at`, `rejected_at`, `claimed_at`, `returned_at`, `admin_id_approved`, `admin_id_rejected`, `admin_id_claimed`, `admin_id_returned`, `notes`) VALUES
(15, 4, '0aa7725689125f83392f00162ca93ceeba3a4941cd6c20c062d20fc8b2862d86', 'returned', '2026-04-26 08:57:37', '2026-04-26 08:58:36', NULL, '2026-04-26 09:01:31', '2026-04-26 09:01:57', 1, NULL, 1, 1, 'kanang dli daan tan awon'),
(16, 5, '836cc4cdaa3719b0f74ba3bbb9924ec88ef2566e7f5742215f64340ff398b4d7', 'returned', '2026-04-29 04:39:39', '2026-04-29 04:45:13', NULL, '2026-04-29 04:46:14', '2026-04-29 04:52:03', 1, NULL, 1, 1, 'colored green books'),
(17, 5, '4542f38a86eec86a73562faec99da92c0373ac881a511b0f7d4d9168973d1031', 'approved', '2026-04-29 04:48:24', '2026-04-29 04:48:44', NULL, NULL, NULL, 1, NULL, NULL, NULL, '........'),
(18, 6, '6f0e36e2adb2c8de5ab218178a292022e253bd275f47d650b924b084e6b73a33', 'returned', '2026-04-29 05:05:57', '2026-04-29 05:08:48', NULL, '2026-04-29 05:10:04', '2026-04-29 05:12:47', 1, NULL, 1, 1, 'limpyo  na cover'),
(19, 7, 'dd4ba77e5144782bb9ecce5f8bea6662a76adeeabc27402ef80d4ab05e3c2008', 'claimed', '2026-04-29 05:24:06', '2026-04-29 05:38:06', NULL, '2026-04-29 05:40:21', NULL, 1, NULL, 1, NULL, NULL),
(20, 8, 'd145b34aa76e89b56c0d529fb04102bb7b90fc3358711e6d59bd9085ff3affa7', 'returned', '2026-05-01 15:42:07', '2026-05-01 15:44:32', NULL, '2026-05-01 15:45:30', '2026-05-01 15:46:36', 1, NULL, 1, 1, 'gdxgxdgx');

-- --------------------------------------------------------

--
-- Table structure for table `borrow_request_items`
--

CREATE TABLE `borrow_request_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `borrow_request_id` int(10) UNSIGNED NOT NULL,
  `book_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `borrow_request_items`
--

INSERT INTO `borrow_request_items` (`id`, `borrow_request_id`, `book_id`, `quantity`) VALUES
(45, 20, 1621, 1),
(46, 20, 1622, 1);

-- --------------------------------------------------------

--
-- Table structure for table `borrow_returns_archive`
--

CREATE TABLE `borrow_returns_archive` (
  `id` int(10) UNSIGNED NOT NULL,
  `borrow_request_id` int(10) UNSIGNED NOT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `borrow_returns_archive`
--

INSERT INTO `borrow_returns_archive` (`id`, `borrow_request_id`, `archived_at`) VALUES
(9, 15, '2026-04-26 09:01:57'),
(10, 16, '2026-04-29 04:52:03'),
(11, 18, '2026-04-29 05:12:47'),
(12, 20, '2026-05-01 15:46:36');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_remember_tokens`
--

CREATE TABLE `student_remember_tokens` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `course` varchar(100) NOT NULL,
  `section` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `name`, `student_id`, `course`, `section`, `email`, `username`, `password_hash`, `profile_picture`, `created_at`, `updated_at`) VALUES
(4, 'je', '2022-123', 'bachelor of science in information technology', '3D', 'jasonkintharcillas.wfg@gmail.com', 'je', '$2y$10$h9xEejp5Q33Qtm6wqG2l4.Mcbc1W7HwHtTexOpIOCznIeQMJHyIeG', 'student_4_1777193648.png', '2026-04-26 08:45:58', '2026-04-26 08:54:08'),
(5, 'iera s. wenceslao', '2022-3088', 'bachelor of science in information technology', '3A', 'wenceslaoieramae@gmail.com', 'ira', '$2y$10$kVX3ojdm.52AvV55vjxlCenuQWiuaeuJ94yGpjvbOx.D4a/OEZm7i', 'student_5_1777437428.png', '2026-04-29 04:29:56', '2026-04-29 04:52:35'),
(6, 'reshel', '2022-31335', 'bachelor of science in information technology', '3A', 'lagutomreshel@gmail.com', 'reshel', '$2y$10$nadK.p4HPzJbBgw8ucOJ9.kMcaB3HWBlh7E76lkXwt0C2GoUC56C.', 'student_6_1777439045.png', '2026-04-29 05:02:38', '2026-04-29 05:04:05'),
(7, 'resh', '2022-3135', 'bachelor of science in information technology', '3D', 'reshellagutom@evsu.edu.ph', 'reshel1', '$2y$10$zvFU3KJQps0nN3cmKM8Nq.LRCYk5V/pnHb8wD.cwrXA3cPj1HBFWW', NULL, '2026-04-29 05:22:16', NULL),
(8, 'arci', '12', 'bachelor of science in information technology', '4A', 'jasonarcillas.a@gmail.com', 'arci', '$2y$10$s9WzIKTRA.uBcCfN1O8SJ.gYmX2moTn7IAtxmShiPlf6fzg8lnfhu', NULL, '2026-05-01 15:36:48', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `books_restore_bin`
--
ALTER TABLE `books_restore_bin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `borrow_requests`
--
ALTER TABLE `borrow_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `qr_token` (`qr_token`),
  ADD KEY `fk_borrow_requests_student` (`student_id`),
  ADD KEY `fk_borrow_requests_admin_approved` (`admin_id_approved`),
  ADD KEY `fk_borrow_requests_admin_rejected` (`admin_id_rejected`),
  ADD KEY `fk_borrow_requests_admin_claimed` (`admin_id_claimed`),
  ADD KEY `fk_borrow_requests_admin_returned` (`admin_id_returned`);

--
-- Indexes for table `borrow_request_items`
--
ALTER TABLE `borrow_request_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_borrow_items_request` (`borrow_request_id`),
  ADD KEY `fk_borrow_items_book` (`book_id`);

--
-- Indexes for table `borrow_returns_archive`
--
ALTER TABLE `borrow_returns_archive`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_returns_archive_request` (`borrow_request_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `fk_reset_tokens_student` (`student_id`);

--
-- Indexes for table `student_remember_tokens`
--
ALTER TABLE `student_remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_hash` (`token_hash`),
  ADD KEY `fk_student_remember_tokens_student` (`student_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1625;

--
-- AUTO_INCREMENT for table `borrow_requests`
--
ALTER TABLE `borrow_requests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `borrow_request_items`
--
ALTER TABLE `borrow_request_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `borrow_returns_archive`
--
ALTER TABLE `borrow_returns_archive`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student_remember_tokens`
--
ALTER TABLE `student_remember_tokens`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `borrow_requests`
--
ALTER TABLE `borrow_requests`
  ADD CONSTRAINT `fk_borrow_requests_admin_approved` FOREIGN KEY (`admin_id_approved`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_borrow_requests_admin_claimed` FOREIGN KEY (`admin_id_claimed`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_borrow_requests_admin_rejected` FOREIGN KEY (`admin_id_rejected`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_borrow_requests_admin_returned` FOREIGN KEY (`admin_id_returned`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_borrow_requests_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `borrow_request_items`
--
ALTER TABLE `borrow_request_items`
  ADD CONSTRAINT `fk_borrow_items_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`),
  ADD CONSTRAINT `fk_borrow_items_request` FOREIGN KEY (`borrow_request_id`) REFERENCES `borrow_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `borrow_returns_archive`
--
ALTER TABLE `borrow_returns_archive`
  ADD CONSTRAINT `fk_returns_archive_request` FOREIGN KEY (`borrow_request_id`) REFERENCES `borrow_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `fk_reset_tokens_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_remember_tokens`
--
ALTER TABLE `student_remember_tokens`
  ADD CONSTRAINT `fk_student_remember_tokens_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
