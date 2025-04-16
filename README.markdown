# Blacklist Manager

A PHP-based web application for managing a company blacklist, developed as part of a student group project. It enables managers to securely log in, add or remove companies from a blacklist, view or edit blacklist details, and generate reports. The app includes secure password hashing, CSRF protection, audit logging, and a sample dataset for demonstration.

## Features

- **Secure Login**: Managers log in with a username and password, with hashed passwords and a 3-attempt lockout for 30 seconds.
- **Add to Blacklist**: Select a company, view its details, and blacklist it with the amount owed recorded.
- **Remove from Blacklist**: Select a blacklisted company and remove it, with a confirmation step to prevent errors.
- **View/Amend Blacklist**: View or update details like amount owed or blacklist date for any blacklisted company.
- **Blacklist Reports**: Generate sortable reports (by date, name, or amount owed) with pagination for easy navigation.
- **Security Features**: CSRF tokens, 30-minute session timeouts, and input validation for enhanced security.
- **Audit Logging**: Tracks actions (e.g., adding or removing companies) with timestamps and usernames.
- **AJAX Functionality**: Smooth, no-reload interface for fetching details and submitting actions.
- **Sample Data**: Includes 5 sample companies (2 blacklisted, 3 not) for immediate testing.
- **Responsive Design**: Clean CSS styling ensures compatibility across devices.

## Prerequisites

- **PHP**: 7.4 or higher (for server-side logic).
- **MySQL**: 5.7 or higher (for storing users, companies, and blacklist data).
- **Web Server**: Apache or Nginx (tested with Apache via XAMPP).
- **Git**: To clone the repository.
- A modern web browser (e.g., Chrome, Firefox).

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/NikoloziGagua/blacklist_manager.git
cd blacklist_manager
```

### 2. Set Up the Database

#### Option A: Manual Database Setup
1. Log in to MySQL via terminal:
   ```bash
   mysql -u your_mysql_user -p
   ```
2. Create the database:
   ```sql
   CREATE DATABASE blacklist_manager;
   ```
3. Create the necessary tables by executing the following SQL commands:
   ```sql
   USE blacklist_manager;

   -- User table
   CREATE TABLE User (
       username VARCHAR(50) PRIMARY KEY,
       password VARCHAR(255) NOT NULL,
       role ENUM('manager') DEFAULT 'manager'
   );

   -- Company table
   CREATE TABLE Company (
       company_id INT PRIMARY KEY,
       name VARCHAR(100) NOT NULL,
       address VARCHAR(255),
       credit_limit DECIMAL(10,2),
       amount_owed DECIMAL(10,2),
       is_blacklisted BOOLEAN DEFAULT FALSE
   );

   -- Blacklist table
   CREATE TABLE Blacklist (
       blacklist_id INT AUTO_INCREMENT PRIMARY KEY,
       company_id INT,
       blacklist_date DATE NOT NULL,
       amount_owed DECIMAL(10,2),
       FOREIGN KEY (company_id) REFERENCES Company(company_id)
   );

   -- AuditLog table
   CREATE TABLE AuditLog (
       log_id INT AUTO_INCREMENT PRIMARY KEY,
       username VARCHAR(50),
       action VARCHAR(255) NOT NULL,
       company_id INT,
       log_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       FOREIGN KEY (username) REFERENCES User(username),
       FOREIGN KEY (company_id) REFERENCES Company(company_id)
   );

   -- Insert sample user
   INSERT INTO User (username, password, role) VALUES
   ('test_manager', '$2y$10$samplehashedpassword', 'manager');

   -- Insert sample companies
   INSERT INTO Company (company_id, name, address, credit_limit, amount_owed, is_blacklisted) VALUES
   (1, 'Company A', '123 Main St', 5000.00, 2000.00, TRUE),
   (2, 'Company B', '456 Oak Ave', 10000.00, 3000.00, TRUE),
   (3, 'Company C', '789 Pine Rd', 8000.00, 0.00, FALSE),
   (4, 'Company D', '321 Elm St', 6000.00, 0.00, FALSE),
   (5, 'Company E', '654 Birch Ln', 7000.00, 0.00, FALSE);

   -- Insert sample blacklist records
   INSERT INTO Blacklist (company_id, blacklist_date, amount_owed) VALUES
   (1, '2023-10-01', 2000.00),
   (2, '2023-11-15', 3000.00),
   (1, '2023-09-01', 1500.00);
   ```
   **Note**: Replace `$2y$10$samplehashedpassword` with a real hashed password generated using PHP’s `password_hash()`. Example:
   ```php
   echo password_hash('your_secure_password', PASSWORD_DEFAULT);
   ```

#### Option B: Import Schema File
1. Create the database:
   ```sql
   CREATE DATABASE blacklist_manager;
   ```
2. Import the provided `schema.sql`:
   ```bash
   mysql -u your_mysql_user -p blacklist_manager < schema.sql
   ```
   *Enter your MySQL password when prompted.*

   The `schema.sql` sets up tables and includes sample data: 1 user (`test_manager`), 5 companies (2 blacklisted), and blacklist records.

### 3. Configure Database Connection

1. Copy the example config file:
   ```bash
   cp db.inc.example.php db.inc.php
   ```
2. Edit `db.inc.php` with your database details:
   ```php
   $host = 'localhost';
   $username = 'your_mysql_user';
   $password = 'your_mysql_password';
   $database = 'blacklist_manager';
   ```

### 4. Web Server Setup

#### Option 1: Apache (XAMPP/WAMP/MAMP)
1. Move the project folder to your web server’s root:
   - **Windows (XAMPP/WAMP)**: `C:\xampp\htdocs\blacklist-manager`
   - **Linux/macOS (Apache)**: `/var/www/html/blacklist-manager`
2. Set permissions (Linux/macOS):
   ```bash
   chmod -R 755 /var/www/html/blacklist-manager
   ```
   (Not needed for Windows/XAMPP by default.)
3. Verify the `style/` folder (containing CSS) is in the project folder.
4. Test access: Visit `http://localhost/blacklist-manager/style/style.css` (should display the CSS file).
5. If needed, ensure `mod_rewrite` is enabled in Apache’s `httpd.conf`:
   ```apache
   LoadModule rewrite_module modules/mod_rewrite.so
   ```

### 5. Access the App

1. Open a browser and go to `http://localhost/blacklist-manager/managerLogin.php`.
2. Log in with the sample credentials:
   - **Username**: `test_manager`
   - **Password**: Contact the repo owner for the password (hashed in `schema.sql`).
   - **Note**: For production, create new users with hashed passwords using `password_hash()`.

## Database Schema

The app uses a MySQL database with four tables:
- **User**: Stores manager accounts (username, hashed password, role).
- **Company**: Stores company details (ID, name, address, credit limit, amount owed, blacklist status).
- **Blacklist**: Tracks blacklist records (company ID, date, amount owed).
- **AuditLog**: Logs actions (e.g., adding/removing companies) with timestamps and usernames.

The `schema.sql` includes:
- 1 sample user (`test_manager`).
- 5 sample companies (2 blacklisted, 3 not).
- Blacklist records, including multiple entries for one company to show history.

See `schema.sql` for the full setup and sample data.



## Usage

1. **Log In**: Visit `managerLogin.php`, enter credentials. Three failed attempts trigger a 30-second lockout.
2. **Add a Company**: Go to “Add to Blacklist,” select a company, view details, and confirm.
3. **Remove a Company**: Use “Remove from Blacklist” to select and remove a company.
4. **Edit Details**: Use “View/Amend Blacklist” to update blacklist details.
5. **View Reports**: Go to “Blacklist Report” to sort and browse blacklisted companies.
6. **Log Out**: Click “Logout” to end the session.

## Author
Nikolozi Gagua

This project taught me a lot about PHP, MySQL, and web security. Hope you enjoy exploring it!
