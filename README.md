# Smart Library Circulation System with QR Code Integration

## System Title
Development of a Smart Library Circulation System with QR Code Integration

---

# Overview
The Smart Library Circulation System is a web-based system designed to simplify the process of borrowing, claiming, and returning library books using QR Code technology.

Students can search and select books, generate a QR code request, and claim books at the library. The admin verifies book availability, approves requests, scans QR codes, and manages borrowing records.

---

# Technologies Used

Frontend
- HTML5
- CSS3
- JavaScript

Backend
- PHP

Database
- MySQL (XAMPP)

Other Tools
- Excel (for book data import)
- QR Code Generator Library
- Email Notification System (SMTP / PHP Mail)

---

# System Requirements

## Software Requirements

Please install the following software before running the system:

- XAMPP (Apache and MySQL)
- Composer (for PHP dependencies)
- Web Browser (Google Chrome, Edge, or Firefox)

## Recommended Versions

- XAMPP: 8.1 or newer
- PHP: 8.1+
- MySQL / MariaDB: 10+
- Composer: 2+
- Browser: Latest version

---

# Required PHP Extensions

Make sure the following extensions are enabled in XAMPP:

- php_openssl
- php_pdo
- php_pdo_mysql
- php_gd
- php_mbstring
- php_fileinfo

You can enable them in:

xampp/php/php.ini

---

# Dependencies

This project uses the following PHP library:

- chillerlan/php-qrcode (QR Code generation)

Install dependencies by running:

composer install

---

# Installation Guide

## 1 Clone or Download the Project

Clone the repository:

git clone https://github.com/yourusername/smart-library-system.git

Or download the ZIP file and extract it.

---

## 2 Move the Project to XAMPP

Move the project folder into the XAMPP htdocs directory.

Example:

C:\xampp\htdocs\smart-library-system

---

## 3 Start XAMPP

Open XAMPP Control Panel and start the following services:

- Apache
- MySQL

---

## 4 Create the Database

Open phpMyAdmin:

http://localhost/phpmyadmin

Create a database named:

smart_library_db

If a SQL file is included, import it into the database.

---

## 5 Configure Database Connection

Open the database configuration file and update the following settings:

Host: localhost  
Username: root  
Password: (leave empty for XAMPP default)  
Database: smart_library_db  

---

## 6 Install Dependencies

Open a terminal in the project folder and run:

composer install

This will install the required PHP libraries.

---

## 7 Run the System

Open your browser and go to:

http://localhost/smart-library-system

---

# System Workflow

## Book Data Preparation

The admin prepares book data in an Excel file and saves it.  
This Excel file will later be imported into the system.

---

## Admin Login

The first page of the system contains:

- Borrow Button
- Username Field
- Password Field
- Login Button

The admin enters credentials and logs in. If valid, the admin is redirected to the Admin Dashboard.

---

# Admin Dashboard Features

The Admin Dashboard includes the following sections:

- Imported Books
- Return
- QR Scan
- Import
- Claimed
- Review
- Available Books
- Approved

---

# Importing Books

1. The admin clicks the Import Button  
2. File Explorer opens  
3. The admin selects the Excel file  
4. The system displays "Import Successfully"  
5. The books appear in the Imported Books page  

---

# Student Borrowing Process

Students click the Borrow Button from the homepage and are redirected to the Student Choose Books page.

This page displays:

- Search Bar
- Add to Shelves Button
- List of imported books
- Borrow Button beside each book

---

# Book Availability

Available books show a Borrow Button.

Unavailable books display the label "Not Available".

---

# Selecting Books

Students may select up to **3 books**.

Selected books are added to the **Add to Shelves Page**.

---

# Student Information Form

Students must fill in the following information:

- Name
- Student ID
- Course
- Section
- Email

Rules:

- Student ID must be unique
- Email must be unique

---

# QR Code Generation

After filling out the form:

1. The student clicks Generate QR Code  
2. The system generates a QR Code containing the student's information and selected books  
3. The QR Code appears in a popup window  

The student can download or capture the QR Code.

---

# Admin Borrow Requests

Requests appear on the Admin Dashboard with the following columns:

- Name
- Student ID
- Course
- Section
- Email
- Books Borrowed
- Book Quantity
- Action

---

# Book Approval

The admin checks book availability in the Available Books page.

If books are available, the admin clicks the Available button.

The system then sends an email notification to the student.

---

# Claiming Books

When the student arrives at the library:

1. The admin clicks QR Scan  
2. The camera scanner opens  
3. The student presents the QR Code  
4. The QR Code is scanned  

The system redirects to the Review Page.

The admin verifies the request and gives the books to the student.

The admin then clicks the Claimed Button.

---

# Borrowing Rules

Maximum number of books allowed: **3**

Borrowing period: **3 days**

---

# Returning Books

When students return books:

1. They present the same QR Code  
2. The admin scans the QR Code again  
3. The system redirects to the Return Page  

Returned records are displayed on this page.

---

# Automatic Record Deletion

Returned records are automatically deleted after **2 days**.

---

# System Pages

- Login Page
- Student Choose Books Page
- Add to Shelves Page
- Admin Dashboard
- Imported Books Page
- Available Books Page
- Review Page
- Claimed Page
- Return Page
- Approved Page

---

# Key Features

- Excel Book Import
- QR Code Borrowing System
- Email Notification
- QR Code Scanning for Claiming
- Borrow Limit Control
- Automatic Record Deletion

---

# License

This project is developed for academic and educational purposes.
