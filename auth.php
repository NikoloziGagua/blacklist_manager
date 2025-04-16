<?php
// Nikolozi Gagua
// C00303433
// This file logs managers out safely. I’ve added a bit of extra security to make it robust.
// Name: logout.php

// Start the session
require_once 'auth.php';

// Log the logout action
include 'db.inc.php';
$sql = "INSERT INTO AuditLog (Action, PerformedBy, PerformedAt) VALUES (?, ?, NOW())";
if ($stmt = $con->prepare($sql)) {
    $actionLog = "Logged out";
    $performedBy = $_SESSION['username'] ?? 'Unknown';
    $stmt->bind_param("ss", $actionLog, $performedBy);
    $stmt->execute();
    $stmt->close();
}
mysqli_close($con);

// Destroy the session
session_destroy();

// Redirect to login
header("Location: managerLogin.php");
exit;
?>