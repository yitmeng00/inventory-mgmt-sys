<?php
require_once 'lib/jwt_helper.php';

$token = $_COOKIE['ims_token'] ?? null;
if ($token && JWTHelper::decode($token)) {
    header('Location: /dashboard.php');
} else {
    header('Location: /login.php');
}
exit;
