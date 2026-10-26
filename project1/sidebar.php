<?php

function navActive($fileNames, $currentPage) {
    $fileNames = (array) $fileNames;
    return in_array($currentPage, $fileNames, true) ? 'active' : '';
}
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <i class="fa-solid fa-chart-line"></i>
        <span class="brand-text">MyShareApp</span>
    </div>

    <ul class="sidebar-nav">
        <li>
            <a href="dashboard.php" class="sidebar-link <?php echo navActive('dashboard.php', $currentPage); ?>">
                <i class="fa-solid fa-gauge-high"></i>
                <span class="link-text">Dashboard</span>
            </a>
        </li>
        <li>
            <a href="users.php" class="sidebar-link <?php echo navActive('users.php', $currentPage); ?>">
                <i class="fa-solid fa-users"></i>
                <span class="link-text">Users</span>
            </a>
        </li>
        <li>
            <a href="newss.php" class="sidebar-link <?php echo navActive(['news.php', 'add_news.php', 'edit_news.php'], $currentPage); ?>">
                <i class="fa-solid fa-newspaper"></i>
                <span class="link-text">News</span>
            </a>
        </li>
        <li>
            <a href="contacts.php" class="sidebar-link <?php echo navActive(['contacts.php', 'view_contact.php'], $currentPage); ?>">
                <i class="fa-solid fa-envelope"></i>
                <span class="link-text">Contact Messages</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <a href="logout.php" class="sidebar-link">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span class="link-text">Logout</span>
        </a>
    </div>
</aside>

<div class="main-content" id="mainContent">
    <header class="topnavbar">
        <div class="topnavbar-left">
            <button class="navbar-menu-btn" id="navbarMenuBtn" aria-label="Toggle sidebar">
                <i class="fa-solid fa-bars"></i>
            </button>
            <button class="sidebar-toggle-btn" id="sidebarToggleBtn" aria-label="Collapse sidebar" title="Collapse sidebar">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <span class="topnavbar-title">Admin Dashboard</span>
        </div>

        <div class="topnavbar-right">
            <div class="navbar-user">
                <i class="fa-solid fa-user"></i>
                <span><?php echo $adminUsername; ?></span>
            </div>
            <a href="logout.php" class="btn-logout">
                <i class="fa-solid fa-right-from-bracket"></i>
                Logout
            </a>
        </div>
    </header>

    <main class="page-content">
