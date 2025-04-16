<?php
// Nikolozi Gagua
// C00303433
// This is the header for all pages, like a navigation bar at the top. It shows different links depending on if you're logged in.

// Include our security guard to check sessions
require_once 'auth.php';
?>
<!DOCTYPE html>
<header>
    <nav>
        <!-- A simple title for the app -->
        <h2>Blacklist Manager</h2>
        <ul>
            <?php if (isset($_SESSION['managerLoggedIn']) && $_SESSION['managerLoggedIn']): ?>
                <!-- Show these links only for logged-in managers -->
                <li><a href="addBlacklist.php">Add to Blacklist</a></li>
                <li><a href="removeBlacklist.php">Remove from Blacklist</a></li>
                <li><a href="viewBlacklist.php">View/Amend Blacklist</a></li>
                <li><a href="viewBlacklistReport.php">Blacklist Report</a></li>
                <li><a href="logout.php">Logout</a></li>
            <?php else: ?>
                <!-- If not logged in, just show the login link -->
                <li><a href="managerLogin.php">Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>