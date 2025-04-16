<?php
// Nikolozi Gagua
// C00303433
// This page lets managers remove companies from the blacklist. I’ve added security and a nicer interface.
// Name: removeBlacklist.php

// Only allow logged-in managers
require_once 'auth.php';
require_manager_login();

// Connect to the database
include 'db.inc.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Set character encoding -->
    <meta charset="UTF-8">
    <title>Remove Company from Blacklist</title>
    <!-- Link to styles -->
    <link rel="stylesheet" href="style/styles.css">
</head>
<body>
    <!-- Include navigation -->
    <?php include 'header.php'; ?>

    <main>
        <h1>Remove Company from Blacklist</h1>
        <!-- Logout link for convenience -->
        <p><a href="logout.php" class="logout-button">Logout</a></p>
        <!-- Area for messages -->
        <div id="message" class="message"></div>
        <!-- Loading spinner -->
        <div id="loading" class="loading">Loading...</div>

        <!-- Form to select a company -->
        <form id="removeBlacklistForm" method="POST">
            <!-- CSRF token -->
            <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
            <p>
                <label for="companyID">Select Company:</label>
                <select name="companyID" id="companyID" required>
                    <option value="">-- Choose a Company --</option>
                    <?php
                    // Get all blacklisted companies
                    $sql = "SELECT Company_ID, Name FROM Company WHERE BLflag = 1 ORDER BY Name ASC";
                    $result = mysqli_query($con, $sql);

                    // Populate the dropdown
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<option value='" . $row['Company_ID'] . "'>" . htmlspecialchars($row['Name']) . "</option>";
                    }

                    // Close the connection
                    mysqli_close($con);
                    ?>
                </select>
            </p>
            <!-- Button to fetch details -->
            <button type="button" id="viewDetailsButton">View Details</button>
            <!-- Hidden details section -->
            <div id="companyDetails" style="display:none; margin-top:20px;">
                <p><label for="companyIdDisplay">Company ID:</label><input type="text" id="companyIdDisplay" readonly></p>
                <p><label for="companyNameDisplay">Company Name:</label><input type="text" id="companyNameDisplay" readonly></p>
                <p><label for="companyAddressDisplay">Address:</label><input type="text" id="companyAddressDisplay" readonly></p>
                <p><label for="creditLimitDisplay">Credit Limit:</label><input type="number" id="creditLimitDisplay" readonly></p>
                <p><label for="amountOwedDisplay">Amount Owed:</label><input type="number" id="amountOwedDisplay" readonly></p>
                <p><label for="timesBlacklistedDisplay">Times Previously Blacklisted:</label><input type="number" id="timesBlacklistedDisplay" readonly></p>
                <p><label for="dateBlacklistedDisplay">Date Blacklisted:</label><input type="date" id="dateBlacklistedDisplay" readonly></p>
                <p><label for="amountOwedAtBlacklistDisplay">Amount Owed at Blacklist Date:</label><input type="number" id="amountOwedAtBlacklistDisplay" readonly></p>
            </div>
            <!-- Hidden action field -->
            <input type="hidden" name="action" value="removeFromBlacklist">
            <br>
            <!-- Submit button -->
            <input type="submit" value="Remove from Blacklist">
        </form>
    </main>
    <script>
        // Get references to elements
        const companySelect = document.getElementById('companyID');
        const detailsDiv = document.getElementById('companyDetails');
        const messageDiv = document.getElementById('message');
        const loadingDiv = document.getElementById('loading');

        // Clear details on company change
        companySelect.addEventListener('change', () => {
            detailsDiv.style.display = 'none';
            messageDiv.innerHTML = '';
            messageDiv.classList.remove('error', 'success');
        });

        // Fetch details when "View Details" is clicked
        document.getElementById('viewDetailsButton').addEventListener('click', () => {
            const companyID = companySelect.value;
            if (!companyID) {
                messageDiv.innerHTML = 'Please select a company.';
                messageDiv.classList.add('error');
                return;
            }

            loadingDiv.style.display = 'block';
            const formData = new URLSearchParams();
            formData.append('action', 'fetchDetails');
            formData.append('companyID', companyID);
            formData.append('csrf_token', document.getElementById('csrf_token').value);

            fetch('removeBlacklistDB.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                loadingDiv.style.display = 'none';
                messageDiv.innerHTML = '';
                messageDiv.classList.remove('error', 'success');
                if (data.error) {
                    messageDiv.innerHTML = data.error;
                    messageDiv.classList.add('error');
                } else {
                    // Fill in the details
                    document.getElementById('companyIdDisplay').value = data.Company_ID || '';
                    document.getElementById('companyNameDisplay').value = data.Name || '';
                    document.getElementById('companyAddressDisplay').value = data.Address || '';
                    document.getElementById('creditLimitDisplay').value = data.CreditLimit || 0;
                    document.getElementById('amountOwedDisplay').value = data.AmountOwed || 0;
                    document.getElementById('timesBlacklistedDisplay').value = data.NumberOfTimesPreviouslyBlacklisted || 0;
                    document.getElementById('dateBlacklistedDisplay').value = data.DateBlacklisted || '';
                    document.getElementById('amountOwedAtBlacklistDisplay').value = data.AmountOwedAtBlacklist || 0;
                    detailsDiv.style.display = 'block';
                }
            })
            .catch(error => {
                loadingDiv.style.display = 'none';
                messageDiv.innerHTML = 'Error: ' + error.message;
                messageDiv.classList.add('error');
            });
        });

        // Handle form submission
        document.getElementById('removeBlacklistForm').addEventListener('submit', (event) => {
            event.preventDefault();
            if (!confirm('Are you sure you want to remove this company from the blacklist?')) return;

            const companyID = companySelect.value;
            loadingDiv.style.display = 'block';

            const formData = new URLSearchParams();
            formData.append('action', 'removeFromBlacklist');
            formData.append('companyID', companyID);
            formData.append('csrf_token', document.getElementById('csrf_token').value);

            fetch('removeBlacklistDB.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                loadingDiv.style.display = 'none';
                messageDiv.classList.remove('error', 'success');
                if (data.error) {
                    messageDiv.innerHTML = data.error;
                    messageDiv.classList.add('error');
                } else if (data.success) {
                    messageDiv.innerHTML = data.message;
                    messageDiv.classList.add('success');
                    detailsDiv.style.display = 'none';
                    // Remove the company from the dropdown
                    let optionToRemove = companySelect.querySelector('option[value="' + companyID + '"]');
                    if (optionToRemove) optionToRemove.remove();
                    // Clear message after 5 seconds
                    setTimeout(() => {
                        messageDiv.innerHTML = '';
                        messageDiv.classList.remove('success');
                    }, 5000);
                }
            })
            .catch(error => {
                loadingDiv.style.display = 'none';
                messageDiv.innerHTML = 'Error: ' + error.message;
                messageDiv.classList.add('error');
            });
        });
    </script>
</body>
</html>