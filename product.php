<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo '<script>alert("Your session has expired. Please log in again.");</script>';
    echo '<script>setTimeout(function(){ window.location.href = "login.php"; }, 500);</script>';
    // header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<?php
include("includes/header.php");
?>

<body data-page="product">
    <div class="ims__body-container d-flex">
        <?php include("includes/navbar.php"); ?>

        <main id="ims__main-product" class="overflow-y-scroll w-100 p-3">
        </main>
    </div>
</body>

</html>