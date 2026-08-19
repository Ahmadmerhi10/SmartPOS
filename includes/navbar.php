<!-- Page Content Wrapper -->
<div id="page-content-wrapper" class="w-100">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-3">
        <div class="container-fluid">
            <span class="navbar-brand fw-bold text-secondary">SmartPOS System</span>
            
            <div class="d-flex align-items-center ms-auto gap-3">
                <!-- Language Selector -->
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="langDropdown" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-globe me-1"></i> English
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item active" href="?lang=en"><i class="fa-solid fa-check me-2"></i>English</a></li>
                        <li><a class="dropdown-item" href="?lang=ar">العربية (Arabic)</a></li>
                    </ul>
                </div>

                <!-- User Menu -->
                <div class="dropdown">
                    <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" id="userMenu" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-user me-1"></i> Account
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="fa-solid fa-gear me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="../auth/logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    <div class="container-fluid p-4">