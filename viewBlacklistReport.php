<?php
// Nikolozi Gagua
// C00303433
// This page shows a report of blacklisted companies. I’ve added pagination and security to make it better.
// Name: viewBlacklistReport.php

// Only allow managers
require_once 'auth.php';
require_manager_login();

// Get sorting and pagination parameters
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 10; // Show 10 companies per page

// Set button states
$dateDisabled = ($sort === 'date') ? 'disabled' : '';
$companyDisabled = ($sort === 'company') ? 'disabled' : '';
$amountDisabled = ($sort === 'amount') ? 'disabled' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Character encoding -->
    <meta charset="UTF-8">
    <title>Blacklist Report</title>
    <!-- Link to styles -->
    <link rel="stylesheet" href="style/styles.css">
</head>
<body>
    <!-- Navigation -->
    <?php include 'header.php'; ?>

    <main>
        <h1>Blacklist Report</h1>
        <p>This report shows details for companies that are currently blacklisted.</p>

        <!-- Sort buttons -->
        <div class="sort-buttons" style="text-align:center; margin-bottom:20px;">
            <a href="viewBlacklistReport.php?sort=date&page=<?php echo $page; ?>">
                <button <?php echo $dateDisabled; ?>>Date</button>
            </a>
            <a href="viewBlacklistReport.php?sort=company&page=<?php echo $page; ?>">
                <button <?php echo $companyDisabled; ?>>Company</button>
            </a>
            <a href="viewBlacklistReport.php?sort=amount&page=<?php echo $page; ?>">
                <button <?php echo $amountDisabled; ?>>Amount Owed</button>
            </a>
        </div>

        <!-- Include the database logic -->
        <?php include 'viewBlacklistReportDB.php'; ?>

        <!-- Show message if no companies -->
        <?php if (empty($reportData)): ?>
            <p>No companies are currently blacklisted.</p>
        <?php else: ?>
            <!-- Display the report table -->
            <table>
                <tr>
                    <th>Date Blacklisted</th>
                    <th>Company Name</th>
                    <th>Amount Owed at Blacklist Date</th>
                    <th>Amount Owed at Present</th>
                    <th>Previous Blacklistings</th>
                </tr>
                <?php foreach ($reportData as $row): 
                    // Format the date nicely
                    $dateBlacklisted = date("Y-m-d", strtotime($row['DateBlacklisted']));
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($dateBlacklisted); ?></td>
                    <td><?php echo htmlspecialchars($row['CompanyName']); ?></td>
                    <td><?php echo number_format($row['AmountOwedAtBlacklist'], 2); ?></td>
                    <td><?php echo number_format($row['AmountOwedAtPresent'], 2); ?></td>
                    <td><?php echo intval($row['NumberOfTimesPreviouslyBlacklisted']); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>

            <!-- Pagination links -->
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="viewBlacklistReport.php?sort=<?php echo $sort; ?>&page=<?php echo $page - 1; ?>">Previous</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="viewBlacklistReport.php?sort=<?php echo $sort; ?>&page=<?php echo $i; ?>" class="<?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="viewBlacklistReport.php?sort=<?php echo $sort; ?>&page=<?php echo $page + 1; ?>">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>