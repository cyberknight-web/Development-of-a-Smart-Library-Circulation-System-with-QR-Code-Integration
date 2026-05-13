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


# PHP Installation for Windows

## 1. Download PHP for Windows

Go to the official PHP for Windows download page:

```text
https://windows.php.net/download/
```

---

## 2. Choose the Correct PHP File

For most Windows laptops, choose:

```text
VS17 x64 Non Thread Safe → Zip
```

Do not choose:

```text
Download source code
Download tests package
Debug Pack
Development package
```

---

## 3. Extract the ZIP File

After downloading the ZIP file:

1. Right-click the ZIP file.
2. Click **Extract All**.
3. Rename the extracted folder to:

```text
php
```

---

## 4. Move PHP Folder

Move the `php` folder to:

```text
C:\php
```

---

## 5. Add PHP to Environment Variables

1. Search **Environment Variables** in Windows Search.
2. Click **Edit the system environment variables**.
3. Click **Environment Variables**.
4. Under **System variables**, find and click **Path**.
5. Click **Edit**.
6. Click **New**.
7. Add this path:

```text
C:\php
```

8. Click **OK** to save.

---

## 6. Check PHP Version

Open **Command Prompt** and run:

```bash
php -v
```

If the PHP version appears, PHP is installed correctly.

---

## Important Note

If `php -v` does not work:

1. Close Command Prompt.
2. Open Command Prompt again.
3. Run:

```bash
php -v
```

If it still does not work, check if this path was added correctly:

```text
C:\php
```

# Composer Installation for Windows

## 1. Download Composer

Go to the official Composer download page:

```text
https://getcomposer.org/download/
```

---

## 2. Download the Windows Installer

Look for and download:

```text
Composer-Setup.exe
```

---

## 3. Run the Installer

Open the downloaded file:

```text
Composer-Setup.exe
```

Then follow the installation steps.

---

## 4. Select PHP Path

During installation, Composer may ask for your PHP path.

Choose:

```text
C:\php\php.exe
```

---

## 5. Finish Installation

Click **Next** until the installation is complete.

---

## 6. Restart Command Prompt

Close your current Command Prompt or terminal.

Then open a new **Command Prompt**.

---

## 7. Check Composer Version

Run this command:

```bash
composer -V
```

If the Composer version appears, Composer is installed correctly.

---

## Important Note

Composer needs PHP to work.

If Composer cannot find PHP, make sure this file exists:

```text
C:\php\php.exe
```

Also make sure PHP is added to your Windows Path:

```text
C:\php
```

Then open a new Command Prompt and check again:

```bash
composer -V
```

# XAMPP Installation for Windows

## 1. Download XAMPP

Go to the official XAMPP download page:

```text
https://www.apachefriends.org/download.html
```

---

## 2. Choose XAMPP for Windows

Look for:

```text
XAMPP for Windows
```

Then download the installer.

The file usually looks like:

```text
xampp-windows-x64-installer.exe
```

---

## 3. Run the Installer

Open the downloaded installer.

If Windows asks for permission, click:

```text
Yes
```

---

## 4. Choose Components

During installation, you can keep the default selected components.

Make sure these are included:

```text
Apache
MySQL
PHP
phpMyAdmin
```

Then click:

```text
Next
```

---

## 5. Choose Installation Folder

The default installation folder is usually:

```text
C:\xampp
```

You can keep this default folder.

Then click:

```text
Next
```

---

## 6. Finish Installation

Continue clicking:

```text
Next
```

Wait for the installation to finish.

Then click:

```text
Finish
```

---

## 7. Open XAMPP Control Panel

Open:

```text
XAMPP Control Panel
```

You can search it in the Windows Start Menu.

---

## 8. Start Apache and MySQL

In the XAMPP Control Panel, click **Start** for:

```text
Apache
MySQL
```

Make sure their status turns green or shows:

```text
Running
```

---

## 9. Test XAMPP in Browser

Open your browser and go to:

```text
http://localhost
```

or:

```text
http://localhost/dashboard
```

If the XAMPP dashboard appears, XAMPP is installed correctly.

---

## 10. Put Your PHP Files in htdocs

Your PHP project files should be placed inside:

```text
C:\xampp\htdocs
```

Example:

```text
C:\xampp\htdocs\myproject
```

Then open it in your browser:

```text
http://localhost/myproject
```

---

## Important Note

If Apache does not start, another app may be using port 80.

Common apps that may cause this:

```text
Skype
IIS
Other web servers
```

You can close the other app or change the Apache port in XAMPP.

---

## Check PHP in XAMPP

Open Command Prompt and run:

```bash
C:\xampp\php\php.exe -v
```

If the PHP version appears, XAMPP PHP is working.

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
# XAMPP Installation for Mac

## 1. Download XAMPP for Mac

Go to the official XAMPP download page:

```text
https://www.apachefriends.org/download.html
```

Look for:

```text
XAMPP for OS X / macOS
```

Then download the Mac installer.

---

## 2. Open the Installer

After downloading, open the downloaded file.

It usually looks like:

```text
xampp-osx-installer.dmg
```

Then follow the installation steps.

---

## 3. Install XAMPP

Click **Next** until the installation starts.

Wait for the installation to finish.

XAMPP will usually be installed in:

```text
/Applications/XAMPP
```

---

## 4. Open XAMPP Manager

Open Finder, then go to:

```text
Applications > XAMPP
```

Open:

```text
manager-osx.app
```

---

## 5. Start Apache and MySQL

Inside XAMPP Manager, go to the **Manage Servers** tab.

Start these services:

```text
Apache Web Server
MySQL Database
```

Make sure their status becomes:

```text
Running
```

---

## 6. Test XAMPP in Browser

Open your browser and go to:

```text
http://localhost
```

or:

```text
http://localhost/dashboard
```

If the XAMPP dashboard appears, XAMPP is working correctly.

---

## 7. Put Your PHP Files in htdocs

Your PHP project files should be placed inside:

```text
/Applications/XAMPP/xamppfiles/htdocs
```

Example:

```text
/Applications/XAMPP/xamppfiles/htdocs/myproject
```

Then open it in your browser:

```text
http://localhost/myproject
```

---

## Important Note

If macOS blocks XAMPP from opening:

1. Go to **System Settings**
2. Go to **Privacy & Security**
3. Click **Open Anyway**
4. Open XAMPP again

---

## Check PHP in XAMPP

To check XAMPP PHP version, open Terminal and run:

```bash
/Applications/XAMPP/xamppfiles/bin/php -v
```

If the PHP version appears, XAMPP PHP is working.


### PHP
```plaintext
https://www.php.net/downloads.php
```
# PHP Installation for Mac

## 1. Open Terminal

On your Mac, open **Terminal**.

---

## 2. Install PHP Using Homebrew

Run this command:

```bash
brew install php
```

---

## 3. Check PHP Version

After installation, check if PHP is working:

```bash
php -v
```

If the PHP version appears, PHP is installed correctly.

---

## 4. Start PHP Built-in Server

Go to your project folder first.

Example:

```bash
cd Desktop/myproject
```

Then run:

```bash
php -S localhost:8000
```

---

## 5. Open in Browser

Open your browser and go to:

```text
http://localhost:8000
```

---

## Important Note

If the `php` command is not found, try restarting Terminal.

Then check again:

```bash
php -v
```

If it still does not work, run:

```bash
brew link php
```

Then check PHP again:

```bash
php -v
```

### Composer
```plaintext
https://getcomposer.org/download/
```
# Composer Installation for Mac

## 1. Open Terminal

On your Mac, open **Terminal**.

---

## 2. Install Homebrew

Composer is easiest to install using **Homebrew**.

Run this command:

```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

After installation, check if Homebrew is installed:

```bash
brew -v
```

---

## 3. Install Composer

Run this command:

```bash
brew install composer
```

---

## 4. Check Composer Version

After installation, check if Composer is working:

```bash
composer -V
```

If the Composer version appears, Composer is installed correctly.

---

## Important Note

Composer needs PHP to work.

If Composer shows an error about PHP, install PHP using:

```bash
brew install php
```

Then check PHP:

```bash
php -v
```

After that, check Composer again:

```bash
composer -V
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

