# Blacklist Manager

This is my PHP-based web app for managing a company blacklist, this was part of a student group projext. It lets managers log in securely, add or remove companies from a blacklist, view or edit blacklist details, and generate reports. I’ve packed it with features like secure password hashing, CSRF protection, audit logging, and a sample dataset to show it off.

## Features

- **Secure Login**: Managers log in with a username and password, with hashed passwords and a 3-attempt lockout for 30 seconds.
- **Add to Blacklist**: Pick a company, view its details, and blacklist it with a record of the amount owed.
- **Remove from Blacklist**: Select a blacklisted company and remove it, with a confirmation step to avoid oopsies.
- **View/Amend Blacklist**: Check or update details like amount owed or blacklist date for any blacklisted company.
- **Blacklist Reports**: Generate sortable reports (by date, name, or amount owed) with pagination for easy browsing.
- **Security Goodies**: CSRF tokens, 30-minute session timeouts, and input validation to keep things locked down.
- **Audit Logging**: Tracks who did what (e.g., added or removed companies) with timestamps.
- **AJAX Magic**: Smooth interface with no page reloads for fetching details or submitting actions.
- **Sample Data**: Comes with 5 sample companies (2 blacklisted, 3 not) to jump right into testing.
- **Responsive Design**: Looks decent on any screen, thanks to clean CSS styling.

## Prerequisites

To run this project, you’ll need:

- **PHP** 7.4 or higher (handles all the server-side logic).
- **MySQL** 5.7 or higher (stores users, companies, and blacklist data).
- **Web Server** like Apache or Nginx (I tested with Apache via XAMPP).
- **Git** (to clone the repo).
- A modern web browser (Chrome, Firefox, etc.).

## Installation

Here’s how to get the app running on your machine:

1. **Clone the Repository**:

   ```bash
   git clone https://github.com/NikoloziGagua/blacklist_manager.git
   cd blacklist_manager
   ```


2. **Set Up the Database**:

   - Create a MySQL database:

     ```sql
     CREATE DATABASE blacklist_manager;
     ```

   - Import the schema from `schema.sql`:

     ```bash
     mysql -u your_mysql_user -p blacklist_manager < schema.sql
     ```

     *Enter your MySQL password when prompted.*

   - The schema sets up tables for users, companies, blacklist records, and audit logs, plus 5 sample companies for testing.

3. **Configure Database Connection**:

   - Copy the example config file:

     ```bash
     cp db.inc.example.php db.inc.php
     ```

   - Edit `db.inc.php` with your database details:

     ```php
     $host = 'localhost';
     $username = 'your_mysql_user';
     $password = 'your_mysql_password';
     $database = 'blacklist_manager';
     ```

 4: **Web Server Setup**
This step involves configuring your web server (Apache/Nginx) to serve the Blacklist Manager app.

Option 1: Apache (XAMPP/WAMP/MAMP)
Move the Project Folder:

Copy the entire blacklist-manager folder to your web server's root directory:

Windows (XAMPP/WAMP): C:\xampp\htdocs\blacklist-manager

Linux/macOS (Apache): /var/www/html/blacklist-manager

Set Permissions:
Ensure the server can read files:

      chmod -R 755 /var/www/html/blacklist-manager  # Linux/macOS
(Not needed on Windows/XAMPP by default.)

Verify style/ Folder:

      Check that the style/ folder (containing CSS) is inside the project folder.

Test access by visiting:
      http://localhost/blacklist-manager/style/style.css
(Should show the CSS file, not a 404 error.)

--
Apache Configuration (if needed):

Usually, no config changes are required for XAMPP.

If you get errors, ensure mod_rewrite is enabled in httpd.conf:

      LoadModule rewrite_module modules/mod_rewrite.so

5. **Access the App**:

   - Open your browser and go to `http://localhost/blacklist-manager/managerLogin.php`.
   - Log in with the sample credentials:
     - **Username**: `test_manager`
     - **Password**: Contact the repo owner for the password (it’s hashed in `schema.sql`).
     - **Important**: For real use, create new users with hashed passwords using `password_hash()`.

## Database Schema

The app uses a MySQL database with four tables:

- **User**: Stores manager accounts (username, hashed password, role).
- **Company**: Holds company details (ID, name, address, credit limit, amount owed, blacklist status).
- **Blacklist**: Tracks blacklist records (company ID, date, amount owed).
- **AuditLog**: Logs actions like adding or removing companies, with timestamps and usernames.

The `schema.sql` file includes:

- 1 sample user (`test_manager`).
- 5 sample companies  with 2 blacklisted and 3 not.
- Blacklist records for the blacklisted companies, including multiple entries for one to show history.

Check `schema.sql` for the full setup and sample data.

## Security Notes

I’ve put a lot of effort into making this secure:

- **Hashed Passwords**: Uses PHP’s `password_hash()` to store passwords safely.
- **CSRF Protection**: Every form and AJAX request has a unique token to block attacks.
- **Session Timeout**: Inactive sessions expire after 30 minutes.
- **Input Validation**: Checks company IDs, amounts, and dates to prevent bad data.
- **Old Version Warning**: The original app stored plain-text passwords (yikes!). This version uses hashing, but always verify before going live.
- **Production Tips**: Use HTTPS, secure session cookies, and limit database permissions in a real deployment.

## Usage

1. **Log In**: Start at `managerLogin.php`. Enter credentials. Three wrong tries trigger a 30-second lockout.
2. **Add a Company**: Go to “Add to Blacklist,” pick a company from the dropdown, view details, and confirm.
3. **Remove a Company**: Use “Remove from Blacklist” to select and remove a blacklisted company.
4. **Edit Details**: Check “View/Amend Blacklist” to update blacklist info like amounts or dates.
5. **View Reports**: Hit “Blacklist Report” to see all blacklisted companies, sort by date/name/amount, and browse pages.
6. **Log Out**: Click “Logout” to end your session safely.

## Author

From this project i learned heaps about PHP, MySQL, and web security. Hope you enjoy checking it out!

---

