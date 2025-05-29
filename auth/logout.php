<?php
/*
* Inventra - Logout Handler
* Handles user logout process
*/

session_start();
require_once '../includes/auth.php';

// Log the user out
logoutUser();
?> 