<?php
// Nikolozi Gagua
// C00303433
// This file handles removing companies from the blacklist or fetching their details. I’ve added security and logging.
// Name: removeBlacklistDB.php

// Only allow logged-in managers
require_once 'auth.php';
require_manager_login();

// Connect to the database
include 'db.inc.php';

// Set consistent timezone
date_default_timezone_set("UTC");

// Decide what to do
$action = isset($_POST['action']) ? $_POST['action'] : 'removeFromBlacklist';

// Verify CSRF token
if (!verify_csrf_token($_POST['csrf_token'])) {
    echo json_encode(['error' => 'Invalid CSRF token.']);
    exit;
}

// Fetch company details
if ($action === 'fetchDetails') {
    // Check for company ID
    if (!isset($_POST['companyID'])) {
        echo json_encode(['error' => 'No company ID provided.']);
        exit;
    }

    // Validate the ID
    $companyID = filter_var($_POST['companyID'], FILTER_VALIDATE_INT);
    if ($companyID === false) {
        echo json_encode(['error' => 'Invalid company ID.']);
        exit;
    }

    // Get the latest blacklist record
    $sql = "
        SELECT
            c.Company_ID,
            c.Name,
            c.Address,
            c.CreditLimit,
            c.AmountOwed,
            c.NumberOfTimesPreviouslyBlacklisted,
            b.DateBlacklisted,
            b.AmountOwed AS AmountOwedAtBlacklist
        FROM Company c
        JOIN Blacklist b ON c.Company_ID = b.Company_ID
        WHERE c.Company_ID = ?
          AND c.BLflag = 1
        ORDER BY b.DateBlacklisted DESC
        LIMIT 1
    ";

    // Prepare the query
    if ($stmt = $con->prepare($sql)) {
        $stmt->bind_param("i", $companyID);
        $stmt->execute();
        $result = $stmt->get_result();

        // Send back the data or an error
        if ($result && $result->num_rows > 0) {
            echo json_encode($result->fetch_assoc());
        } else {
            echo json_encode(['error' => 'Company not found or not blacklisted.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['error' => 'Database error.']);
    }

// Remove from blacklist
} elseif ($action === 'removeFromBlacklist') {
    // Check for company ID
    if (!isset($_POST['companyID'])) {
        echo json_encode(['error' => 'Missing company ID.']);
        exit;
    }

    // Validate the ID
    $companyID = filter_var($_POST['companyID'], FILTER_VALIDATE_INT);
    if ($companyID === false) {
        echo json_encode(['error' => 'Invalid company ID.']);
        exit;
    }

    // Start a transaction
    $con->begin_transaction();

    // Update the company’s blacklist status
    $sql1 = "UPDATE Company SET BLflag = 0 WHERE Company_ID = ? AND BLflag = 1";
    if ($stmt1 = $con->prepare($sql1)) {
        $stmt1->bind_param("i", $companyID);
        $success1 = $stmt1->execute();
        $stmt1->close();
    } else {
        $con->rollback();
        echo json_encode(['error' => 'Database error: Company update.']);
        exit;
    }

    // Delete the blacklist record
    $sql2 = "DELETE FROM Blacklist WHERE Company_ID = ?";
    if ($stmt2 = $con->prepare($sql2)) {
        $stmt2->bind_param("i", $companyID);
        $success2 = $stmt2->execute();
        $stmt2->close();
    } else {
        $con->rollback();
        echo json_encode(['error' => 'Database error: Blacklist delete.']);
        exit;
    }

    // Log the action
    $sql3 = "INSERT INTO AuditLog (Action, Company_ID, PerformedBy, PerformedAt) VALUES (?, ?, ?, NOW())";
    if ($stmt3 = $con->prepare($sql3)) {
        $actionLog = "Removed from blacklist";
        $performedBy = $_SESSION['username'] ?? 'Unknown';
        $stmt3->bind_param("sis", $actionLog, $companyID, $performedBy);
        $success3 = $stmt3->execute();
        $stmt3->close();
    } else {
        $con->rollback();
        echo json_encode(['error' => 'Database error: Audit log.']);
        exit;
    }

    // Commit if successful
    if ($success1 && $success2 && $success3) {
        $con->commit();
        echo json_encode(['success' => true, 'message' => 'Company successfully removed from blacklist.']);
    } else {
        $con->rollback();
        echo json_encode(['error' => 'Error removing company from blacklist.']);
    }

} else {
    // Unknown action
    echo json_encode(['error' => 'Unknown action.']);
}

// Close the connection
$con->close();
?>