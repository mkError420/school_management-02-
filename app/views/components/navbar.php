<!-- Top Navigation -->
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
    <div class="container-fluid">
        
        <!-- Sidebar Toggle Button -->
        <button class="btn btn-link d-md-none rounded-circle me-2" id="sidebar-toggle">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- Brand -->
        <a class="navbar-brand d-md-none" href="#">
            <i class="fas fa-graduation-cap me-2"></i>
            <strong>School MS</strong>
        </a>
        
        <!-- Navbar Toggler -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Navbar Content -->
        <div class="collapse navbar-collapse" id="navbarNav">
            
            <!-- Search Bar -->
            <form class="d-none d-md-flex ms-4" action="search" method="GET">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" 
                           class="form-control bg-light border-0" 
                           placeholder="Search students, teachers, classes..." 
                           name="q"
                           value="<?php echo $_GET['q'] ?? ''; ?>">
                </div>
            </form>
            
            <!-- Right Side Items -->
            <ul class="navbar-nav ms-auto">
                
                <!-- Notifications -->
                <li class="nav-item dropdown">
                    <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notification-count">
                            3
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" id="notification-list">
                        <li><h6 class="dropdown-header">Notifications</h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="#">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-exclamation-circle text-warning"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-2">
                                        <small class="text-muted">2 hours ago</small>
                                        <div>New exam scheduled for Grade 10</div>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-user-plus text-success"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-2">
                                        <small class="text-muted">5 hours ago</small>
                                        <div>New student enrolled</div>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-chart-line text-info"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-2">
                                        <small class="text-muted">1 day ago</small>
                                        <div>Monthly report available</div>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center" href="#">View All Notifications</a></li>
                    </ul>
                </li>
                
                <!-- Dark Mode Toggle -->
                <li class="nav-item">
                    <button class="nav-link btn btn-link" id="dark-mode-toggle" title="Toggle Dark Mode">
                        <i class="fas fa-moon" id="dark-mode-icon"></i>
                    </button>
                </li>
                
                <!-- User Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <img src="<?php echo PUBLIC_PATH; ?>/assets/images/default-avatar.png" 
                             alt="User Avatar" 
                             class="rounded-circle me-2" 
                             width="32" 
                             height="32">
                        <span class="d-none d-md-inline"><?php echo $session->get('user.first_name'); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header"><?php echo ucfirst($session->getUserRole()); ?></h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="profile">
                                <i class="fas fa-user me-2"></i>Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="settings">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="help">
                                <i class="fas fa-question-circle me-2"></i>Help
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="logout">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </li>
                
            </ul>
        </div>
    </div>
</nav>
