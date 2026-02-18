<!-- Sidebar -->
<div class="bg-white border-end shadow-sm" id="sidebar-wrapper">
    <div class="sidebar-heading bg-primary text-white text-center py-3">
        <i class="fas fa-graduation-cap me-2"></i>
        <strong>School MS</strong>
    </div>
    
    <?php
    // Define menu items
    $admin_menu = [
        'dashboard' => ['title' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'color' => 'primary'],
        'students' => ['title' => 'Students', 'icon' => 'fas fa-users', 'color' => 'info'],
        'teachers' => ['title' => 'Teachers', 'icon' => 'fas fa-chalkboard-teacher', 'color' => 'success'],
        'classes' => ['title' => 'Classes', 'icon' => 'fas fa-door-open', 'color' => 'warning'],
        'subjects' => ['title' => 'Subjects', 'icon' => 'fas fa-book', 'color' => 'danger'],
        'exams' => ['title' => 'Exams', 'icon' => 'fas fa-clipboard-list', 'color' => 'dark'],
        'attendance' => ['title' => 'Attendance', 'icon' => 'fas fa-calendar-check', 'color' => 'secondary'],
        'results' => ['title' => 'Results', 'icon' => 'fas fa-chart-bar', 'color' => 'info'],
        'reports' => ['title' => 'Reports', 'icon' => 'fas fa-file-alt', 'color' => 'primary']
    ];
    
    $teacher_menu = [
        'dashboard' => ['title' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'color' => 'primary'],
        'classes' => ['title' => 'My Classes', 'icon' => 'fas fa-door-open', 'color' => 'info'],
        'attendance' => ['title' => 'Attendance', 'icon' => 'fas fa-calendar-check', 'color' => 'success'],
        'exams' => ['title' => 'Exams', 'icon' => 'fas fa-clipboard-list', 'color' => 'warning'],
        'results' => ['title' => 'Results', 'icon' => 'fas fa-chart-bar', 'color' => 'danger']
    ];
    
    $student_menu = [
        'dashboard' => ['title' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'color' => 'primary'],
        'profile' => ['title' => 'Profile', 'icon' => 'fas fa-user', 'color' => 'info'],
        'attendance' => ['title' => 'Attendance', 'icon' => 'fas fa-calendar-check', 'color' => 'success'],
        'exams' => ['title' => 'Exams', 'icon' => 'fas fa-clipboard-list', 'color' => 'warning'],
        'results' => ['title' => 'Results', 'icon' => 'fas fa-chart-bar', 'color' => 'danger']
    ];
    
    $session = Session::getInstance();
    $userRole = $session->getUserRole();
    $currentRoute = $_SERVER['REQUEST_URI'] ?? '';
    
    // Get menu based on user role
    $menu = [];
    switch ($userRole) {
        case ROLE_ADMIN:
            $menu = $admin_menu;
            break;
        case ROLE_TEACHER:
            $menu = $teacher_menu;
            break;
        case ROLE_STUDENT:
            $menu = $student_menu;
            break;
    }
    ?>
    
    <div class="sidebar-menu">
        <?php foreach ($menu as $route => $item):
            $isActive = strpos($currentRoute, $route) !== false;
            $url = ($userRole === ROLE_ADMIN ? 'admin/' : ($userRole === ROLE_TEACHER ? 'teacher/' : 'student/')) . $route;
            if ($route === 'dashboard') {
                $url = $userRole;
            }
            
            $colorClass = $isActive ? 'active' : '';
            $bgClass = $isActive ? 'bg-' . $item['color'] : 'text-' . $item['color'];
        ?>
            <a href="<?php echo $url; ?>" 
               class="sidebar-menu-item <?php echo $colorClass; ?> <?php echo $bgClass; ?> hover-<?php echo $item['color']; ?>">
                <i class="<?php echo $item['icon']; ?> me-3"></i>
                <span><?php echo $item['title']; ?></span>
                <?php if ($isActive): ?>
                    <i class="fas fa-chevron-right ms-auto"></i>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
    
    <!-- User Profile Section -->
    <div class="sidebar-footer p-3 border-top bg-light">
        <div class="d-flex align-items-center mb-3">
            <div class="flex-shrink-0">
                <img src="<?php echo PUBLIC_PATH; ?>/assets/images/default-avatar.png" 
                     alt="User Avatar" 
                     class="rounded-circle" 
                     width="40" 
                     height="40">
            </div>
            <div class="flex-grow-1 ms-3">
                <div class="fw-bold text-dark"><?php echo ucfirst($session->get('user.first_name') ?? 'User'); ?></div>
                <small class="text-muted d-block"><?php echo ucfirst($userRole); ?></small>
            </div>
        </div>
        <div class="d-grid gap-2">
            <a href="profile" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-user me-1"></i>Profile
            </a>
            <a href="/School management/public/logout" class="btn btn-sm btn-outline-danger">
                <i class="fas fa-sign-out-alt me-1"></i>Logout
            </a>
        </div>
    </div>
</div>
