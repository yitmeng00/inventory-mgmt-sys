<?php
require_once 'lib/jwt_helper.php';
JWTHelper::clearTokenCookie();
header('Location: /login.php');
exit;
