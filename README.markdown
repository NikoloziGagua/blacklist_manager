# Blacklist Manager

Hey there! This is my PHP-based web app for managing a company blacklist, built as a student project. It lets managers log in securely, add or remove companies from a blacklist, view or edit blacklist details, and generate reports. I’ve packed it with features like secure password hashing, CSRF protection, audit logging, and a beefed-up sample dataset to show it off.

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
   git clone https://github.com/your-username/blacklist-manager.git
   cd blacklist-manager
   ```

   *Replace* `your-username` *with your actual GitHub username.*

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

4. **Set Up the Web Server**:

   - Move the `blacklist-manager` folder to your web server’s root (e.g., `/var/www/html/blacklist-manager` for Apache or `C:\xampp\htdocs\blacklist-manager` for XAMPP).
   - Ensure the `style/` folder is readable by the server.
   - Point your server to the project directory (update Apache/Nginx config if needed).

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
- 5 sample companies (e.g., TechTrend Innovations, Global Imports Ltd), with 2 blacklisted and 3 not.
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

## Future Improvements

I’ve got some ideas to level this up:

- **Password Reset**: Add a secure way for managers to reset passwords.
- **Search Filter**: Let users search companies in dropdowns or reports.
- **Email Alerts**: Notify managers when blacklist changes happen.
- **Fancier UI**: Maybe use Bootstrap for a modern look.
- **APIs**: Add REST endpoints for other apps to interact with the blacklist.

## Contributing

Love to hear your thoughts! To contribute:

- Open an issue for bugs or feature ideas.
- Submit a pull request with tested changes.

## License

Licensed under the MIT License. Use, modify, or share it freely, just keep the copyright notice.

## Author

**Nikolozi Gagua** (Student ID: C00303433)

This project was a blast to build! I learned heaps about PHP, MySQL, and web security. Hope you enjoy checking it out!

---

*Note*: The sample password hash in `schema.sql` is a placeholder. Generate your own with `password_hash()` for real users. Stay secure!