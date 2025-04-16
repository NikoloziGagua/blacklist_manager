<?php
// Nikolozi Gagua
// C00303433
// This page lets managers view or edit blacklist details. I’ve added security to make it safer.
// Name: viewBlacklist.php

// Only allow managers
require_once 'auth.php';
require_manager_login();

// Connect to the database
include 'db.inc.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Character encoding -->
    <meta charset="UTF-8">
    <title>Amend/View Blacklist</title>
    <!-- Link to styles -->
    <link rel="stylesheet" href="style/styles.css">
</head>
<body>
    <!-- Navigation -->
    <?php include 'header.php'; ?>

    <main>
        <h1>Amend/View Blacklist</h1>
        <h4>Select a blacklisted company to view or amend its details</h4>
        <!-- Messages go here -->
        <div id="message" class="message"></div>
        <!-- Loading spinner -->
        <div id="loading" class="loading">Loading...</div>

        <!-- Form to select and edit a company -->
        <form id="amendBlacklistForm" method="POST">
            <!-- CSRF token -->
            <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
            <p>
                <label for="companySelect">Select Company:</label>
                <select id="companySelect" name="companySelect" required>
                    <option value="">Select a company</option>
                    <?php
                    // Get companies with blacklist records
                    $sql = "
                        SELECT DISTINCT c.Company_ID, c.Name 
                        FROM Company c
                        JOIN Blacklist b ON c.Company_ID = b.Company_ID
                        WHERE c.deleteFlag = 0
                        ORDER BY c.Name ASC
                    ";
                    $result = mysqli_query($con, $sql);
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<option value='" . $row['Company_ID'] . "'>" . htmlspecialchars($row['Name']) . "</option>";
                    }
                    mysqli_close($con);
                    ?>
                </select>
            </p>
            <!-- Button to toggle view/edit mode -->
            <button type="button" id="toggleDetailsButton">View Details</button>
            <!-- Details section -->
            <div id="blacklistDetails" style="display:none;">
                <p>
                    <label for="companyName">Company Name:</label>
                    <input type="text" id="companyName" name="companyName" disabled>
                </p>
                <p>
                    <label for="companyAddress">Company Address:</label>
                    <input type="text" id="companyAddress" name="companyAddress" disabled>
                </p>
                <p>
                    <label for="creditLimit">Credit Limit:</label>
                    <input type="number" id="creditLimit" name="creditLimit" step="0.01" disabled>
                </p>
                <p>
                    <label for="presentAmountOwed">Amount Owed (Present):</label>
                    <input type="number" id="presentAmountOwed" name="presentAmountOwed" step="0.01" disabled>
                </p>
                <p>
                    <label for="timesBlacklisted">Times Previously Blacklisted:</label>
                    <input type="number" id="timesBlacklisted" name="timesBlacklisted" disabled>
                </p>
                <p>
                    <label for="dateBlacklisted">Date Blacklisted:</label>
                    <input type="date" id="dateBlacklisted" name="dateBlacklisted" disabled>
                </p>
                <p>
                    <label for="amountOwedAtBlacklist">Amount Owed at Blacklist Date:</label>
                    <input type="number" id="amountOwedAtBlacklist" name="amountOwedAtBlacklist" step="0.01" disabled>
                </p>
                <!-- Hidden blacklist ID -->
                <input type="hidden" name="blId" id="blId">
                <!-- Save button for edit mode -->
                <br>
                <button type="submit" id="saveChangesButton" style="display:none;">Save Changes</button>
            </div>
        </form>
    </main>
    <script>
        // Get references
        const companySelect = document.getElementById('companySelect');
        const detailsDiv = document.getElementById('blacklistDetails');
        const toggleButton = document.getElementById('toggleDetailsButton');
        const saveButton = document.getElementById('saveChangesButton');
        const messageDiv = document.getElementById('message');
        const loadingDiv = document.getElementById('loading');

        // Reset form when company changes
        companySelect.addEventListener('change', () => {
            const fields = [
                'companyName', 'companyAddress', 'creditLimit',
                'presentAmountOwed', 'timesBlacklisted', 'dateBlacklisted',
                'amountOwedAtBlacklist', 'blId'
            ];
            fields.forEach(id => {
                document.getElementById(id).value = '';
                document.getElementById(id).disabled = true;
            });
            detailsDiv.style.display = 'none';
            toggleButton.innerText = 'View Details';
            toggleButton.style.display = 'block';
            saveButton.style.display = 'none';
            messageDiv.innerHTML = '';
            messageDiv.classList.remove('success', 'error');
        });

        // Toggle view/edit mode
        toggleButton.onclick = () => {
            const companyId = companySelect.value;
            if (!companyId) {
                messageDiv.innerHTML = 'Please select a company.';
                messageDiv.classList.add('error');
                return;
            }

            if (toggleButton.innerText === 'View Details') {
                // Fetch details
                loadingDiv.style.display = 'block';
                const formData = new URLSearchParams();
                formData.append('action', 'fetchDetails');
                formData.append('companyId', companyId);
                formData.append('csrf_token', document.getElementById('csrf_token').value);

                fetch('viewBlacklistDB.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    loadingDiv.style.display = 'none';
                    if (data.error) {
                        messageDiv.innerHTML = data.error;
                        messageDiv.classList.add('error');
                    } else {
                        // Populate fields
                        document.getElementById('companyName').value = data.CompanyName || '';
                        document.getElementById('companyAddress').value = data.Address || '';
                        document.getElementById('creditLimit').value = data.CreditLimit || '';
                        document.getElementById('presentAmountOwed').value = data.PresentAmountOwed || '';
                        document.getElementById('timesBlacklisted').value = data.TimesPreviouslyBlacklisted || '';
                        document.getElementById('dateBlacklisted').value = data.DateBlacklisted || '';
                        document.getElementById('amountOwedAtBlacklist').value = data.AmountOwedAtBlacklist || '';
                        document.getElementById('blId').value = data.BL_ID || '';
                        detailsDiv.style.display = 'block';
                        toggleButton.innerText = 'Amend Details';
                    }
                })
                .catch(error => {
                    loadingDiv.style.display = 'none';
                    messageDiv.innerHTML = 'Error: ' + error.message;
                    messageDiv.classList.add('error');
                });
            } else {
                // Enable editable fields
                document.getElementById('presentAmountOwed').disabled = false;
                document.getElementById('timesBlacklisted').disabled = false;
                document.getElementById('dateBlacklisted').disabled = false;
                document.getElementById('amountOwedAtBlacklist').disabled = false;
                toggleButton.style.display = 'none';
                saveButton.style.display = 'block';
            }
        };

        // Handle form submission
        document.getElementById('amendBlacklistForm').onsubmit = (event) => {
            event.preventDefault();
            if (!confirm('Are you sure you want to amend the blacklist details?')) return;

            loadingDiv.style.display = 'block';
            const formData = new URLSearchParams();
            formData.append('companyId', companySelect.value);
            formData.append('companyName', document.getElementById('companyName').value);
            formData.append('companyAddress', document.getElementById('companyAddress').value);
            formData.append('creditLimit', document.getElementById('creditLimit').value);
            formData.append('presentAmountOwed', document.getElementById('presentAmountOwed').value);
            formData.append('timesBlacklisted', document.getElementById('timesBlacklisted').value);
            formData.append('dateBlacklisted', document.getElementById('dateBlacklisted').value);
            formData.append('amountOwedAtBlacklist', document.getElementById('amountOwedAtBlacklist').value);
            formData.append('blId', document.getElementById('blId').value);
            formData.append('action', 'amendBlacklist');
            formData.append('csrf_token', document.getElementById('csrf_token').value);

            fetch('viewBlacklistDB.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                loadingDiv.style.display = 'none';
                if (data.success) {
                    messageDiv.innerHTML = 'Blacklist details successfully amended.';
                    messageDiv.classList.add('success');
                    document.getElementById('presentAmountOwed').disabled = true;
                    document.getElementById('timesBlacklisted').disabled = true;
                    document.getElementById('dateBlacklisted').disabled = true;
                    document.getElementById('amountOwedAtBlacklist').disabled = true;
                    toggleButton.innerText = 'Amend Details';
                    toggleButton.style.display = 'block';
                    saveButton.style.display = 'none';
                    setTimeout(() => {
                        messageDiv.innerHTML = '';
                        messageDiv.classList.remove('success');
                    }, 5000);
                } else {
                    messageDiv.innerHTML = 'Error: ' + data.error;
                    messageDiv.classList.add('error');
                }
            })
            .catch(error => {
                loadingDiv.style.display = 'none';
                messageDiv.innerHTML = 'Error: ' + error.message;
                messageDiv.classList.add('error');
            });
        };
    </script>
</body>
</html>