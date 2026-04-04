# Smart Library Circulation System with QR Code Integration

## System Title
**Development of a Smart Library Circulation System with QR Code Integration**

---

# Overview
The **Smart Library Circulation System** is a web-based system designed to simplify the process of borrowing, claiming, and returning library books using **QR Code technology**.

Students can search and select books, generate a QR code request, and claim books at the library. The admin verifies book availability, approves requests, scans QR codes, and manages borrowing records.

---

# Technologies Used

## Frontend
- HTML5
- CSS3
- JavaScript

## Backend
- PHP

## Database
- MySQL (XAMPP)

## Other Tools
- Microsoft Excel (for book data import)
- QR Code Generator Library
- Email Notification System (SMTP / PHP Mail)

---

# System Requirements

## Software Requirements
Please install the following software before running the system:

- **XAMPP** (Apache and MySQL)
- **Composer** (PHP dependency manager)
- **Git** (optional, for cloning the repository)
- **Web Browser** (Google Chrome, Edge, or Firefox)

## Recommended Versions

| Software | Version |
|--------|--------|
| XAMPP | 8.1 or newer |
| PHP | 8.1+ |
| MySQL / MariaDB | 10+ |
| Composer | 2+ |
| Browser | Latest version |

---

# Required PHP Extensions

Make sure the following extensions are enabled in XAMPP:

- `php_openssl`
- `php_pdo`
- `php_pdo_mysql`
- `php_gd`
- `php_mbstring`
- `php_fileinfo`

### Enable Extensions

1. Open the file:

```
xampp/php/php.ini
```

2. Remove the `;` before the extension name  
3. Save the file  
4. Restart **Apache** in XAMPP

---

# Dependencies

This project uses the following PHP library:

- `php-qrcode` – QR Code generation

Dependencies are managed using **Composer**.

---

# Installation Guide

## 1 Clone or Download the Project

Clone the repository:

```bash
git clone https://github.com/cyberknight-web/Development-of-a-Smart-Library-Circulation-System-with-QR-Code-Integration
```

Or download the ZIP file and extract it.

---

## 2 Move the Project to XAMPP

Move the project folder into the **XAMPP htdocs directory**.

Example:

```
C:\xampp\htdocs\smart-library-system
```

---

## 3 Navigate to the Project Directory

Open **Command Prompt** or **Terminal** and run:

```bash
cd C:\xampp\htdocs\smart-library-system
```

---

# Dependency Environment Activation

PHP does not use a traditional **virtual environment** like Python. Instead, **Composer manages a local dependency environment** inside the `vendor/` folder.

Install dependencies by running:

```bash
composer install
```

This command will:

- Download required libraries  
- Create the `vendor/` folder  
- Generate the `autoload.php` file  

To activate installed dependencies in PHP files, include the Composer autoloader:

```php
require_once __DIR__ . '/vendor/autoload.php';
```

Verify installed packages:

```bash
composer show
```

Expected package:

```
php-qrcode
```

---

# Start the Server

Open **XAMPP Control Panel** and start:

- Apache  
- MySQL  

---

# Database Setup

## Open phpMyAdmin

```
http://localhost/phpmyadmin
```

## Create Database

Create a new database named:

```
smart_library_db
```

If a `.sql` file is included in the project, import it into this database.

---

# Configure Database Connection

Open the database configuration file and update the following settings:

```
Host: localhost
Username: root
Password: (leave empty for XAMPP default)
Database: smart_library_db
```

Save the configuration file.

---

# Run the System

Open your browser and go to:

```
http://localhost/smart-library-system
```

The system should now be running.

---

# Smart Library Circulation System with QR Code Integration

## Overview
The Smart Library Circulation System with QR Code Integration is a web-based library management system designed to simplify the borrowing and returning of books. The system allows administrators to manage book records and student accounts while enabling students to request books and generate QR codes for faster verification and borrowing.

The system improves efficiency in library circulation by automating the borrowing workflow, monitoring book availability, and maintaining accurate borrowing records.

---

## Features

### Admin Features
- Admin authentication (Login and Logout)
- Book management using CSV import
- Student account management
- Borrow request monitoring
- QR code scanning for book claiming
- Processing returned books
- Analytics dashboard

### Student Features
- Student login system
- Book search and selection
- Add books to My Shelves
- Borrow request submission
- QR code generation for borrowing
- View borrowed books

---

## Technologies Used

### Frontend
- HTML5
- CSS3
- JavaScript

### Backend
- PHP

### Database
- MySQL (XAMPP)

### Other Tools
- Composer for PHP dependencies
- Excel or CSV for book import
- QR Code Generator Library
- PHPMailer for email notification

---

## System Requirements

### Software
- XAMPP (Apache + MySQL)
- Composer
- Web Browser (Chrome, Edge, Firefox)

### Recommended Versions
- XAMPP 8.1+
- PHP 8.1+

---

## System Flow

### 1. Admin Login and Dashboard Access
The admin starts on the admin login page and enters a valid username and password. If the credentials are correct, the system redirects the admin to the Admin Dashboard.

The Admin Dashboard shows the following main sections:

- Books
- Returned
- QR Scan
- Students
- Borrow Request
- Approved

---

### 2. Book Management and CSV Import
1. The admin clicks the Books button on the Admin Dashboard.
2. The system opens the Book Management page.
3. On this page, the admin can download a CSV template for book encoding.
4. The admin fills in the book data in Excel and saves the file using the .csv format.
5. To upload the saved file, the admin clicks Choose File.
6. The File Explorer opens and the admin selects the saved CSV file.
7. The admin clicks the import books button.
8. After a successful upload, the system displays the message: “Books imported successfully from Excel file.”
9. The imported books are then stored in the system and become visible to students during book selection.

---

### 3. Student Account Creation
1. Before using the system, the student first coordinates with the librarian or admin to request an account.
2. The admin clicks the Students button on the Admin Dashboard.
3. The system redirects the admin to the Create Student Account page.
4. The admin fills out the student account form using the following details:
   - Name
   - Student ID
   - Course
   - Section
   - Email
   - Username
   - Password
5. The Student ID must be unique, and the Email must also be unique for each student.
6. After completing the form, the admin clicks Save Student Account.
7. The system stores the account, allowing the student to log in.

---

### 4. Student Login and Dashboard
After receiving the account credentials, the student opens the student login page and enters the assigned username and password. If the credentials are valid, the system redirects the student to the Student Dashboard.

The Student Dashboard navigation bar contains the following sections:

- Borrow Books
- My Shelves
- My Borrow Books

---

### 5. Book Selection and Borrow Request
To borrow books, the student clicks Borrow Books and is redirected to the Choose Books page.

This page displays the following:

- Search bar
- Active Borrow Books
- In Your Shelves
- List of all imported books
- Add to Shelves button beside each available book
- View Shelves button

Book availability rules:

- If a book is available, the Add to Shelves button appears and the student may select it.
- If a book is not available, the system shows Not Available instead of a borrow/select button.
- The maximum number of books a student can borrow is 3.
- The borrowing period is limited to 3 days.

For example, if the student wants to borrow three books, the student clicks Add to Shelves beside each available title. The selected books are stored in My Shelves for review before final submission.

---

### 6. My Shelves and QR Code Generation
The student clicks View Shelves to open the My Shelves page. This page shows the list of selected books and the borrower information form.

The student must confirm or provide the following information:

- Name
- Student ID
- Course
- Section
- Email

After checking the selected books and completing the form, the student clicks Generate QR Code. The system creates a QR code in a popup window, which the student may download or save on a phone.

Once the QR code is generated, the student's request details and selected books are submitted to the admin through the Borrow Request section.

---

### 7. Admin Review of Borrow Requests
The admin opens the Borrow Request section to review pending requests. The table contains the following columns:

- Name
- Student ID
- Course/Section
- Email
- QR Token
- Requested At
- Action

In the Action column, the admin chooses either Available or Not Available.

If the requested books are available, the admin clicks Available. The request status is updated from Pending to Approved, and the student may proceed to the library to claim the books.

If the requested books are not available, the admin clicks Not Available, and the request is not approved.

---

### 8. Claiming Approved Books Through QR Scan
1. Once the request is approved, the student goes to the library and presents the generated QR code to the admin.
2. The admin opens the QR Scan section on the dashboard.
3. The QR Scan page shows a Start Camera button and a Search Record feature.
4. The admin scans the student's QR code using the camera scanner.
5. After scanning, the student's information and borrowing details are displayed.
6. The system shows the Mark as Claimed button.
7. The admin clicks Mark as Claimed to confirm that the books were successfully claimed by the student.

---

### 9. Returning Borrowed Books
1. When the student returns the borrowed books, the admin again opens the QR Scan section.
2. The admin uses Start Camera or Search Record to locate the borrowing record.
3. After the student's QR code or record is scanned, the borrowing details are displayed.
4. The system shows the Process Return button.
5. The admin clicks Process Return to complete the return transaction and update book availability.

---

### 10. Additional System Features
- Analytics dashboard showing the most borrowed books
- Summary cards or counters for total books, available books, and total students
- Admin logout and student logout functions
- Password update or credential recovery with email verification for both admin and student accounts

---

## Summary
The system begins with admin-controlled book import and student account creation. Students then log in, choose available books, place them in My Shelves, and generate a QR code for their request. The admin reviews each request, approves or rejects it based on availability, scans the QR code during claiming, and later processes the return of borrowed books.

This flow supports secure borrowing, accurate monitoring, and organized circulation of library materials.
