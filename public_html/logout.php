<?php
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/auth.php';
logoutUser();
redirect('login.php');
