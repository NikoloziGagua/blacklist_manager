<?php
// Nikolozi Gagua
// C00303433
// This is the login page for managers. I’ve added hashed passwords, CSRF protection, and a session timeout to make it more secure.
// Name: managerLogin.php

// Include our security and database files
require_once 'auth.php';
include 'db.inc.php';

// If the user clicked "Retry", reset their login attempts
if (isset($_GET['retry'])) {
    $_SESSION['loginAttempts'] = 0;
}

// Set up variables for errors and lockout status
$error = "";
$maxAttemptsReached = false;

// Make sure loginAttempts is initialized
if (!isset($_SESSION['loginAttempts'])) {
    $_SESSION['loginAttempts'] = 0;
}

// Show a message if the session timed out
if (isset($_GET['timeout'])) {
    $error = "Your session expired. Please log in again.";
}

// Check if the user has tried too many times
if ($_SESSION['loginAttempts'] >= 3) {
    $maxAttemptsReached = true;
    $error = "Maximum attempts reached. Please wait 30 seconds.";
}

// Handle the login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$maxAttemptsReached) {
    // First, check the CSRF token to prevent attacks
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = "Invalid CSRF token.";
    } else {
        // Get the username and password from the form
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        // Make sure both fields are filled
        if (empty($username) || empty($password)) {
            $_SESSION['loginAttempts']++;
            $error = "Please enter both username and password.";
        } else {
            // Look for the user in the database
            $sql = "SELECT Username, Password, Role FROM User WHERE Username = ?";
            if ($stmt = $con->prepare($sql)) {
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();

                // Check if we found exactly one user
                if ($result && $result->num_rows === 1) {
                    $row = $result->fetch_assoc();

                    // Verify the password and role
                    if (strtolower($row['Role']) === 'manager' && password_verify($password, $row['Password'])) {
                        // Success! Log them in and reset attempts
                        $_SESSION['managerLoggedIn'] = true;
                        $_SESSION['username'] = $username; // Store for audit logging
                        $_SESSION['loginAttempts'] = 0;
                        $_SESSION['last_activity'] = time();
                        header("Location: removeBlacklist.php");
                        exit;
                    } else {
                        // Wrong password or not a manager
                        $_SESSION['loginAttempts']++;
                        $error = "Incorrect password or you're not a manager.";
                    }
                } else {
                    // No user found
                    $_SESSION['loginAttempts']++;
                    $error = "User not found.";
                }
                $stmt->close();
            } else {
                $error = "Something went wrong with the database.";
            }
        }
    }

    // Lock out if max attempts reached
    if ($_SESSION['loginAttempts'] >= 3) {
        $maxAttemptsReached = true;
        $error = "Maximum attempts reached.";
    }
}

// Close the database connection
mysqli_close($con);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Set the character encoding -->
    <meta charset="UTF-8">
    <title>Manager Login</title>
    <!-- Link to our stylesheet -->
    <link rel="stylesheet" href="style/styles.css">
</head>
<body>
    <!-- Include the navigation header -->
    <?php include 'header.php'; ?>

    <main>
        <h1>Manager Login</h1>

        <!-- Show any error messages -->
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php if (!$maxAttemptsReached): ?>
                <p class="error">Remaining attempts: <?php echo 3 - $_SESSION['loginAttempts']; ?></p>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Show lockout timer if needed -->
        <?php if ($maxAttemptsReached): ?>
            <div id="lockout">
                <p class="error">
                    Maximum attempts reached. Please wait for 
                    <span id="countdown">30</span> seconds.
                </p>
                <button id="retryButton" disabled>Retry</button>
            </div>
        <?php else: ?>
            <!-- The login form -->
            <form method="POST" action="managerLogin.php">
                <!-- CSRF token for security -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                <p>
                    <label for="username">Username:</label>
                    <input type="text" name="username" id="username" required>
                </p>
                <p>
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" required>
                </p>
                <input type="submit" value="Login">
            </form>
        <?php endif; ?>
    </main>

    <!-- JavaScript for the lockout timer -->
    <?php if ($maxAttemptsReached): ?>
    <script>
        // Start a 30-second countdown
        let countdown = 30;
        const countdownEl = document.getElementById('countdown');
        const retryButton = document.getElementById('retryButton');

        // Update the countdown every second
        const timer = setInterval(() => {
            countdown--;
            countdownEl.textContent = countdown;
            // Enable the retry button when done
            if (countdown <= 0) {
                clearInterval(timer);
                retryButton.disabled = false;
            }
        }, 1000);

        // Reload the page with retry=1 when clicked
        retryButton.addEventListener('click', () => {
            if (!retryButton.disabled) {
                window.location.href = "managerLogin.php?retry=1";
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>