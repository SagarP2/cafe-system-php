<?php
session_start();
session_unset();     // Clear all session variables
session_destroy();   // Destroy the session
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
echo json_encode(['status' => 'success']);
exit();
?>