<?php
require_once 'lib/jwt_helper.php';
$auth_user  = JWTHelper::authenticate();
$page_title = 'Dashboard';
$include_chartjs = true;
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'includes/header.php'; ?>

<body data-page="dashboard">
    <div class="ims__body-container d-flex">
        <?php include("includes/navbar.php"); ?>

        <main id="ims__main-dashboard" class="overflow-y-scroll w-100 p-3">
        </main>
    </div>
</body>

</html>