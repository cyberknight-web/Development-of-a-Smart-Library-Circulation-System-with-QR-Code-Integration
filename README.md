# 📚 Smart Library Circulation System with QR Code Integration

## 📌 1. System Title
**Development of a Smart Library Circulation System with QR Code Integration**

---

# 📖 2. Overview

The **Smart Library Circulation System** is a web-based system designed to simplify the process of borrowing, claiming, and returning library books using **QR Code technology**.

The system provides separate accounts for both **Admins** and **Students**.

Students can create and access their own accounts to browse, search, and borrow available books by adding them to **My Shelves** and generating a QR code request. The admin verifies book availability, approves requests, scans QR codes, and manages borrowing and return records.

---

# ⚙️ 3. Technologies Used

## 🖥️ Frontend
- HTML5  
- CSS3  
- JavaScript  

## 🧠 Backend
- PHP  

## 🗄️ Database
- MySQL / MariaDB (XAMPP)  

---

# 💻 4. System Requirements

## 🔧 Software Requirements
- XAMPP
- PHP
- Composer
- Web Browser (Google Chrome, Microsoft Edge, Firefox)

## 📊 Recommended Versions

| Software | Version |
|----------|----------|
| XAMPP | 8.1+ |
| Composer | v2.9.7 |
| PHP | 8.1+ |
| Browser | Latest |

---

# 🚀 6. Installation Guide

# 🪟 Windows Installation Guide

## 6.0 Download Required Software

### PHP
copy and paste  and then Click “Zip” under “VS17 x64 Non Thread Safe”
```plaintext
https://www.php.net/downloads.php
```

### Composer
copy and paste 
```plaintext
https://getcomposer.org/download/
```

### XAMPP
copy and paste 
```plaintext
https://www.apachefriends.org/index.html
```

---

## 6.1 Clone or Download the Project

### Clone Using Git
Open CMD, PowerShell, Git Bash, or VS Code Terminal and run:

```bash
git clone https://github.com/cyberknight-web/Development-of-a-Smart-Library-Circulation-System-with-QR-Code-Integration.git
```

### Or Download ZIP File
Download the ZIP file from GitHub and extract it.

---

## 6.2 Move the Project to XAMPP

Move the project folder to:

```plaintext
C:\xampp\htdocs
```

Example:

```plaintext
C:\xampp\htdocs\smartlibrary
```

---

## 6.3 Start XAMPP

Open **XAMPP Control Panel** and start:

- Apache
- MySQL

---

# 🗃️ 7. Database Setup (Windows)

## 7.1 Open phpMyAdmin

Open your browser and go to:

```plaintext
http://localhost/phpmyadmin
```

---

## 7.2 Create Database

Create a new database named:

```plaintext
smartlibrary
```

---

## 7.3 Import Database

1. Select `smartlibrary`
2. Click **Import**
3. Choose the `smartlibrary.sql` file from the folder you downloaded.
4. Click **Go**

---

# ⚡ 8. Database Configuration (Windows)

## 8.1 Generate Admin Password Hash

Type this in your browser:

```plaintext
http://localhost/smartlibrary/generate_admin_hash.php
```

Copy the generated Hash Code.

---

## 8.2 Insert Admin Account

1. Go to:

```plaintext
http://localhost/phpmyadmin
```

2. Click database `smartlibrary`
3. Open the `admins` table
4. Click **SQL**
5. Paste this query:

```sql
INSERT INTO admins (username, password_hash)
VALUES ('Evsu123', 'PASTE_THE_HASH_CODE_HERE');
```

> ⚠️ NOTE: Replace `PASTE_THE_HASH_CODE_HERE` with the generated hash code.

Use the following default admin credentials to log in to the system:

```plaintext
Username: Evsu123
Password: Evsu123
```

---

## 8.3 Install Composer Dependencies

Open CMD, PowerShell, or VS Code Terminal inside the project folder and run:

```bash
composer install
```
---

# 📧 8.4 Configure Gmail App Password (Required for Email Notifications)

The system uses Gmail SMTP to send email notifications.

## Step 1 — Enable 2-Step Verification

Go to your Google Account Security settings:

```plaintext
https://myaccount.google.com/security
```

Enable:

- 2-Step Verification

---

## Step 2 — Generate Gmail App Password

After enabling 2-Step Verification:

1. Go to:

```plaintext
https://myaccount.google.com/apppasswords
```

2. Select:

- App → Mail
- Device → Windows Computer (or Custom Name)

3. Click **Generate**

Google will provide a 16-character App Password.

Example:

```plaintext
abcd efgh ijkl mnop
```

---

## Step 3 — Open `config.php`

Open:

```plaintext
config.php
```

Find this code:

```php
const MAIL_SMTP_USER = 'your_email@gmail.com';
const MAIL_SMTP_PASS = 'your_app_password';
```

Replace it with your own Gmail and App Password:

```php
const MAIL_SMTP_USER = 'youremail@gmail.com';
const MAIL_SMTP_PASS = 'your_generated_app_password';
```

Example:

```php
const MAIL_SMTP_USER = 'sample@gmail.com';
const MAIL_SMTP_PASS = 'abcdefghijklmnop';
```

> ⚠️ IMPORTANT:
> Never share your Gmail App Password publicly or upload it to GitHub.

---

## Step 4 — Save the File

Save `config.php`.

The system can now send email notifications using Gmail SMTP.

---

---

# 🌐 9. Run the System (Windows)

Open your browser and go to:

```plaintext
http://localhost/smartlibrary
```

---

# 🍎 macOS Installation Guide

## 10.0 Download Required Software

### XAMPP for macOS
```plaintext
https://www.apachefriends.org/index.html
```

### PHP
```plaintext
https://www.php.net/downloads.php
```

### Composer
```plaintext
https://getcomposer.org/download/
```

---

## 10.1 Clone or Download the Project

### Clone Using Git
Open **Terminal** and run:

```bash
git clone https://github.com/cyberknight-web/Development-of-a-Smart-Library-Circulation-System-with-QR-Code-Integration.git
```

### Or Download ZIP File
Download the ZIP file from GitHub and extract it.

---

## 10.2 Move the Project to XAMPP

Move the project folder to:

```plaintext
/Applications/XAMPP/xamppfiles/htdocs
```

Example:

```plaintext
/Applications/XAMPP/xamppfiles/htdocs/smartlibrary
```

---

## 10.3 Start XAMPP

Open **XAMPP Manager.app** and start:

- Apache
- MySQL

---

# 🗃️ 11. Database Setup (macOS)

## 11.1 Open phpMyAdmin

Open your browser and go to:

```plaintext
http://localhost/phpmyadmin
```

---

## 11.2 Create Database

Create a new database named:

```plaintext
smartlibrary
```

---

## 11.3 Import Database

1. Select `smartlibrary`
2. Click **Import**
3. Choose the `smartlibrary.sql` file from the folder you downloaded.
4. Click **Go**

---

# ⚡ 12. Database Configuration (macOS)

## 12.1 Generate Admin Password Hash

Type this in your browser:

```plaintext
http://localhost/smartlibrary/generate_admin_hash.php
```

Copy the generated Hash Code.

---

## 12.2 Insert Admin Account

1. Go to:

```plaintext
http://localhost/phpmyadmin
```

2. Click database `smartlibrary`
3. Open the `admins` table
4. Click **SQL**
5. Paste this query:

```sql
INSERT INTO admins (username, password_hash)
VALUES ('Evsu123', 'PASTE_THE_HASH_CODE_HERE');
```

> ⚠️ NOTE: Replace `PASTE_THE_HASH_CODE_HERE` with the generated hash code.

Use the following default admin credentials to log in to the system:

```plaintext
Username: Evsu123
Password: Evsu123
```

---

## 12.3 Install Composer Dependencies

Open Terminal and run:

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/smartlibrary
composer install
```

---

# 🌐 13. Run the System (macOS)

Open your browser and go to:

```plaintext
http://localhost/smartlibrary
```

