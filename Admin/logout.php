<?php
session_start(); // Start the session to access session variables

// Unset all session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect to the homepage (or wherever you'd like to redirect the user after logout)
header("Location: index.php");
exit();
?>
