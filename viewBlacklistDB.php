<?php
// Nikolozi Gagua
// C00303433
// This backend file handles viewing or updating blacklist data. I’ve added security to keep it safe.
// Name: viewBlacklistDB.php

// Only allow managers
require_once 'auth.php';
require_manager_login();

// Connect to the database
include 'db.inc.php';

// Check for an action
if (!isset($_POST['action'])) {
    echo json_encode(['error' => 'Invalid request.']);
    exit;
}

// Verify CSRF token
if (!verify_csrf_token($_POST['csrf_token'])) {
    echo json_encode(['error' => 'Invalid CSRF token.']);
    exit;
}

$action = $_POST['action'];

if ($action === 'fetchDetails') {
    // Need a company ID
    if (!isset($_POST['companyId'])) {
        echo json_encode(['error' => 'Company ID missing.']);
        exit;
    }

    // Validate the ID
    $companyId = filter_var($_POST['companyId'], FILTER_VALIDATE_INT);
    if ($companyId === false) {
        echo json_encode(['error' => 'Invalid company ID.']);
        exit;
    }

    // Fetch the latest blacklist record
    $sql = "
        SELECT 
            c.Company_ID,
            c.Name AS CompanyName,
            c.Address,
            c.CreditLimit,
            c.AmountOwed AS PresentAmountOwed,
            b.BL_ID,
            b.DateBlacklisted,
            b.AmountOwed AS AmountOwedAtBlacklist,
            (
                SELECT COUNT(*)
                FROM Blacklist b2
                WHERE b2.Company_ID = c.Company_ID
                  AND b2.DateBlacklisted < b.DateBlacklisted
            ) AS TimesPreviouslyBlacklisted
        FROM Company c
        JOIN Blacklist b ON c.Company_ID = b.Company_ID
        WHERE c.Company_ID = ?
        ORDER BY b.DateBlacklisted DESC
        LIMIT 1
    ";

    // Prepare the query
    if ($stmt = $con->prepare($sql)) {
        $stmt->bind_param("i", $companyId);
        $stmt->execute();
        $result = $stmt->get_result();

        // Send back the data
        if ($result && $result->num_rows > 0) {
            echo json_encode($result->fetch_assoc());
        } else {
            echo json_encode(['error' => 'No blacklist record found.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['error' => 'Database error.']);
    }

} elseif ($action === 'amendBlacklist') {
    // Check all required fields
    $requiredFields = [
        'companyId', 'companyName', 'companyAddress',
        'creditLimit', 'presentAmountOwed', 'dateBlacklisted',
        'amountOwedAtBlacklist', 'blId'
    ];

    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field])) {
            echo json_encode(['error' => "Missing field: $field"]);
            exit;
        }
    }

    // Validate inputs
    $companyId = filter_var($_POST['companyId'], FILTER_VALIDATE_INT);
    $companyName = trim($_POST['companyName']);
    $companyAddress = trim($_POST['companyAddress']);
    $creditLimit = filter_var($_POST['creditLimit'], FILTER_VALIDATE_FLOAT);
    $presentAmountOwed = filter_var($_POST['presentAmountOwed'], FILTER_VALIDATE_FLOAT);
    $dateBlacklisted = $_POST['dateBlacklisted'];
    $amountOwedAtBlacklist = filter_var($_POST['amountOwedAtBlacklist'], FILTER_VALIDATE_FLOAT);
    $blId = filter_var($_POST['blId'], FILTER_VALIDATE_INT);

    if ($companyId === false || $creditLimit === false || $presentAmountOwed === false || 
        $amountOwedAtBlacklist === false || $blId === false || 
        $presentAmountOwed < 0 || $amountOwedAtBlacklist < 0 ||
        !preg_match("/^\d{4}-\d{2}-\d{2}$/", $dateBlacklisted)) {
        echo json_encode(['error' => 'Invalid input data.']);
        exit;
    }

    // Start a transaction
    $con->begin_transaction();

    // Update company details
    $sql1 = "UPDATE Company 
             SET Name = ?, Address = ?, CreditLimit = ?, AmountOwed = ?
             WHERE Company_ID = ?";
    if ($stmt1 = $con->prepare($sql1)) {
        $stmt1->bind_param("ssddi", $companyName, $companyAddress, $creditLimit, $presentAmountOwed, $companyId);
        $success1 = $stmt1->execute();
        $stmt1->close();
    } else {
        $con->rollback();
        echo json_encode(['error' => 'Database error: Company update.']);
        exit;
    }

    // Update blacklist record
    $sql2 = "UPDATE Blacklist 
             SET DateBlacklisted = ?, AmountOwed = ? 
             WHERE BL_ID = ?";
    if ($stmt2 = $con->prepare($sql2)) {
        $stmt2->bind_param("sdi", $dateBlacklisted, $amountOwedAtBlacklist, $blId);
        $success2 = $stmt2->execute();
        $stmt2->close();
    } else {
        $con->rollback();
        echo json_encode(['error' => 'Database error: Blacklist update.']);
        exit;
    }

    // Log the action
    $sql3 = "INSERT INTO AuditLog (Action, Company_ID, PerformedBy, PerformedAt) VALUES (?, ?, ?, NOW())";
    if ($stmt3 = $con->prepare($sql3)) {
        $actionLog = "Amended blacklist details";
        $performedBy = $_SESSION['username'] ?? 'Unknown';
        $stmt3->bind_param("sis", $actionLog, $companyId, $performedBy);
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
        echo json_encode(['success' => true]);
    } else {
        $con->rollback();
        echo json_encode(['error' => 'Failed to update blacklist details.']);
    }

} else {
    // Unknown action
    echo json_encode(['error' => 'Unknown action.']);
}

// Close the connection
$con->close();
?>