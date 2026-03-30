<?php
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/auth.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}
redirect('login.php');
