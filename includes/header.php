<?php
$include_chartjs = $include_chartjs ?? false;
$page_title      = $page_title ?? 'Inventory Management System';
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> | IMS</title>
    <link rel="icon" type="image/x-icon" href="/assets/images/inventory-mgmt-sys-logo-small.png">

    <!-- Tailwind CSS (compiled) -->
    <link rel="stylesheet" href="/assets/css/tailwind.css">

    <!-- Custom styles (DataTables overrides, sidebar, toast, etc.) -->
    <link rel="stylesheet" href="/assets/css/main.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js" defer></script>

    <!-- XLSX (for Excel export) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.5/xlsx.full.min.js" defer></script>

    <?php if ($include_chartjs): ?>
        <!-- Chart.js (dashboard only) -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
    <?php endif; ?>

    <!-- App JS -->
    <script src="/assets/js/toast.js?v=2.0" defer></script>
    <script src="/assets/js/api.js?v=2.0" defer></script>
    <script src="/assets/js/app.js?v=2.0" defer></script>
</head>