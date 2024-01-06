<?php
session_start();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<?php
include("includes/header.php");
?>

<body data-page="login">
    <div class="ims__body-container d-flex align-items-center justify-content-center ">
        <main id="ims__main-login">
        </main>
    </div>
</body>

</html>