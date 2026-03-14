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

# System Workflow

## Book Data Preparation
The admin prepares book data using **Microsoft Excel** and saves the file.  
This Excel file will later be imported into the system.

---

## Admin Login

The first page of the system contains:

- Borrow Button
- Username Field
- Password Field
- Login Button

The admin enters credentials and logs in.  
If valid, the admin is redirected to the **Admin Dashboard**.

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

1. The admin clicks the **Import Button**  
2. File Explorer opens  
3. The admin selects the Excel file  
4. The system displays **"Import Successfully"**  
5. Books appear in the **Imported Books Page**

---

# Student Borrowing Process

Students click the **Borrow Button** from the homepage.

They are redirected to the **Student Choose Books Page** which contains:

- Search Bar
- Add to Shelves Button
- List of imported books
- Borrow Button beside each book

---

# Book Availability

| Status | Description |
|------|------|
| Available | Borrow button is shown |
| Not Available | Book cannot be borrowed |

---

# Selecting Books

Students may select up to **3 books**.

Selected books are added to the **Add to Shelves Page**.

---

# Student Information Form

Students must fill out:

- Name
- Student ID
- Course
- Section
- Email

### Rules

- Student ID must be **unique**
- Email must be **unique**

---

# QR Code Generation

After submitting the form:

1. Student clicks **Generate QR Code**  
2. System generates a QR Code containing:
   - Student information
   - Selected books
3. QR Code appears in a popup window  

Students can download or capture the QR Code.

---

# Admin Borrow Requests

Requests appear on the **Admin Dashboard** with the following columns:

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

The admin checks availability in the **Available Books Page**.

If books are available:

- Admin clicks **Available**

The system sends an **email notification** to the student.

---

# Claiming Books

When the student arrives at the library:

1. Admin clicks **QR Scan**  
2. Camera scanner opens  
3. Student presents QR Code  
4. QR Code is scanned  

System redirects to the **Review Page**.

Admin verifies and releases the books, then clicks **Claimed Button**.

---

# Borrowing Rules

| Rule | Value |
|----|----|
| Maximum books | 3 |
| Borrowing period | 3 days |

---

# Returning Books

When students return books:

1. They present the same QR Code  
2. Admin scans the QR Code  
3. System redirects to **Return Page**

Returned records are displayed.

---

# Automatic Record Deletion

Returned records are **automatically deleted after 2 days**.

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

This project is developed for **academic and educational purposes**.
