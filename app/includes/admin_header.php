<?php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$nav = [
    ['page' => 'dashboard', 'href' => 'dashboard.php', 'icon' => 'bxs-dashboard',    'label' => 'Dashboard',       'section' => 'Main'],
    ['page' => 'records',   'href' => 'records.php',   'icon' => 'bxs-user-detail',  'label' => 'Patient Records', 'section' => 'Main'],
    ['page' => 'medicine',  'href' => 'medicine.php',  'icon' => 'bx-capsule',       'label' => 'Medicine',        'section' => 'Main'],
    ['page' => 'symptoms',  'href' => 'symptoms.php',  'icon' => 'bx-heart',         'label' => 'Symptoms',        'section' => 'Main'],
    ['page' => 'lab',       'href' => 'lab.php',       'icon' => 'bx-test-tube',     'label' => 'Lab Results',     'section' => 'Main'],
    ['page' => 'analytics', 'href' => 'analytics.php', 'icon' => 'bx-bar-chart-alt-2','label' => 'Analytics',      'section' => 'Reports'],
    ['page' => 'logs',      'href' => 'logs.php',      'icon' => 'bx-list-ul',       'label' => 'Activity Logs',   'section' => 'Reports'],
];
$currentSection = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Admin'); ?> — TSHS Clinic</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.css">
    <?php if (!empty($pageHeadExtra)) echo $pageHeadExtra; ?>
    <script src="../js/admin-shared.js" defer></script>
</head>
<body>

<div id="sidebarBackdrop" class="sidebar-backdrop"></div>

<div class="admin-wrapper">

    <!-- ══ SIDEBAR ══ -->
    <aside class="sidebar" id="sidebar">

        <div class="sidebar-brand">
            <div class="brand-icon"><i class='bx bx-plus-medical'></i></div>
            <span class="brand-name">TSHS Clinic</span>
        </div>

        <nav class="sidebar-nav">
            <?php foreach ($nav as $item):
                if ($item['section'] !== $currentSection):
                    $currentSection = $item['section'];
                    echo "<div class='nav-section'>{$item['section']}</div>";
                endif;
                $active = $currentPage === $item['page'] ? ' active' : '';
            ?>
            <a href="<?php echo $item['href']; ?>"
               class="nav-item<?php echo $active; ?>"
               data-label="<?php echo $item['label']; ?>">
                <i class='bx <?php echo $item['icon']; ?>'></i>
                <span><?php echo $item['label']; ?></span>
            </a>
            <?php endforeach; ?>
        </nav>

        <div class="sidebar-footer">
            <a href="../../routes/auth_api.php?action=logout"
               class="nav-item logout"
               data-label="Logout">
                <i class='bx bx-log-out'></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- ══ MAIN AREA ══ -->
    <div class="main-area">

        <!-- ── TOPBAR ── -->
        <header class="topbar">
            <button class="sidebar-toggle" id="sidebarToggle" title="Toggle sidebar">
                <i class='bx bx-menu'></i>
            </button>

            <div class="topbar-search">
                <i class='bx bx-search'></i>
                <input type="text" id="topbarSearch" placeholder="Search anything…">
            </div>

            <div class="topbar-right">
                <?php include __DIR__ . '/notification_bell.php'; ?>

                <div class="user-chip">
                    <div class="avatar">
                        <?php echo strtoupper(substr($user['username'] ?? 'A', 0, 1)); ?>
                    </div>
                    <div class="user-meta">
                        <span class="user-name"><?php echo htmlspecialchars($user['fullname'] ?? 'Admin'); ?></span>
                        <span class="user-role">Administrator</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- ── PAGE CONTENT ── -->
        <main class="page-content">
