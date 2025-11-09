<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
// Redirect to dashboard.php
header("Location: dashboard.php");
exit();
?>