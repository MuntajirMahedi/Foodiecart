<?php
require_once '../config.php';

if (!isAdmin()) {
    // If not logged in as admin → redirect to admin login
    redirect('login.php');
}
?>
