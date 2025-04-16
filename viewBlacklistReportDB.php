<?php
// Nikolozi Gagua
// C00303433
// This file fetches blacklisted companies for the report. I’ve added pagination to handle lots of data.
// Name: viewBlacklistReportDB.php

// Connect to the database
include 'db.inc.php';

// Get sorting and pagination parameters
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 10;

// Calculate offset
$offset = ($page - 1) * $perPage;

// Count total records
$countQuery = "SELECT COUNT(*) as total 
               FROM Company c 
               JOIN Blacklist b ON c.Company_ID = b.Company_ID 
               WHERE c.BLflag = 1";
$countResult = mysqli_query($con, $countQuery);
$totalRecords = $countResult ? mysqli_fetch_assoc($countResult)['total'] : 0;
$totalPages = ceil($totalRecords / $perPage);

// Choose the query based on sort
switch ($sort) {
    case 'company':
        // Sort by company name
        $query = "SELECT b.DateBlacklisted, c.Name AS CompanyName, b.AmountOwed AS AmountOwedAtBlacklist, 
                         c.AmountOwed AS AmountOwedAtPresent, c.NumberOfTimesPreviouslyBlacklisted
                  FROM Company c
                  JOIN Blacklist b ON c.Company_ID = b.Company_ID
                  WHERE c.BLflag = 1
                  ORDER BY c.Name ASC
                  LIMIT $perPage OFFSET $offset";
        break;

    case 'amount':
        // Sort by amount owed
        $query = "SELECT b.DateBlacklisted, c.Name AS CompanyName, b.AmountOwed AS AmountOwedAtBlacklist, 
                         c.AmountOwed AS AmountOwedAtPresent, c.NumberOfTimesPreviouslyBlacklisted
                  FROM Company c
                  JOIN Blacklist b ON c.Company_ID = b.Company_ID
                  WHERE c.BLflag = 1
                  ORDER BY c.AmountOwed DESC
                  LIMIT $perPage OFFSET $offset";
        break;

    case 'date':
    default:
        // Sort by date
        $query = "SELECT b.DateBlacklisted, c.Name AS CompanyName, b.AmountOwed AS AmountOwedAtBlacklist, 
                         c.AmountOwed AS AmountOwedAtPresent, c.NumberOfTimesPreviouslyBlacklisted
                  FROM Company c
                  JOIN Blacklist b ON c.Company_ID = b.Company_ID
                  WHERE c.BLflag = 1
                  ORDER BY b.DateBlacklisted DESC
                  LIMIT $perPage OFFSET $offset";
        break;
}

// Run the query
$result = mysqli_query($con, $query);
$reportData = [];

if ($result) {
    // Collect the data
    while ($row = mysqli_fetch_assoc($result)) {
        $reportData[] = $row;
    }
}

// Close the connection
mysqli_close($con);
?>