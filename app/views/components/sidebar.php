<!-- Sidebar -->
<div class="bg-white border-end" id="sidebar-wrapper">
    <div class="sidebar-heading bg-primary text-white text-center py-3">
        <i class="fas fa-graduation-cap me-2"></i>
        <strong>School MS</strong>
    </div>
    
    <?php
    // Define menu items
    $admin_menu = [
        'dashboard' => ['title' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt'],
        'students' => ['title' => 'Students', 'icon' => 'fas fa-users'],
        'teachers' => ['title' => 'Teachers', 'icon' => 'fas fa-chalkboard-teacher'],
        'classes' => ['title' => 'Classes', 'icon' => 'fas fa-door-open'],
        'subjects' => ['title' => 'Subjects', 'icon' => 'fas fa-book'],
        'exams' => ['title' => 'Exams', 'icon' => 'fas fa-clipboard-list'],
        'attendance' => ['title' => 'Attendance', 'icon' => 'fas fa-calendar-check'],
        'results' => ['title' => 'Results', 'icon' => 'fas fa-chart-bar'],
        'reports' => ['title' => 'Reports', 'icon' => 'fas fa-file-alt']
    ];
    
    $teacher_menu = [
        'dashboard' => ['title' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt'],
        'classes' => ['title' => 'My Classes', 'icon' => 'fas fa-door-open'],
        'attendance' => ['title' => 'Attendance', 'icon' => 'fas fa-calendar-check'],
        'exams' => ['title' => 'Exams', 'icon' => 'fas fa-clipboard-list'],
        'results' => ['title' => 'Results', 'icon' => 'fas fa-chart-bar']
    ];
    
    $student_menu = [
        'dashboard' => ['title' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt'],
        'profile' => ['title' => 'Profile', 'icon' => 'fas fa-user'],
        'attendance' => ['title' => 'Attendance', 'icon' => 'fas fa-calendar-check'],
        'exams' => ['title' => 'Exams', 'icon' => 'fas fa-clipboard-list'],
        'results' => ['title' => 'Results', 'icon' => 'fas fa-chart-bar']
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
    
    <div class="list-group list-group-flush">
        <?php foreach ($menu as $route => $item):
            $isActive = strpos($currentRoute, $route) !== false;
            $url = ($userRole === ROLE_ADMIN ? 'admin/' : ($userRole === ROLE_TEACHER ? 'teacher/' : 'student/')) . $route;
            if ($route === 'dashboard') {
                $url = $userRole;
            }
        ?>
            <a href="<?php echo $url; ?>" class="list-group-item list-group-item-action <?php echo $isActive ? 'active' : ''; ?>">
                <i class="<?php echo $item['icon']; ?> me-2"></i>
                <?php echo $item['title']; ?>
            </a>
        <?php endforeach; ?>
    </div>
    
    <!-- User Profile Section -->
    <div class="sidebar-footer p-3 border-top">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
                <img src="<?php echo PUBLIC_PATH; ?>/assets/images/default-avatar.png" 
                     alt="User Avatar" 
                     class="rounded-circle" 
                     width="40" 
                     height="40">
            </div>
            <div class="flex-grow-1 ms-2">
                <div class="fw-bold"><?php echo ucfirst($session->get('user.first_name') ?? 'User'); ?></div>
                <small class="text-muted"><?php echo ucfirst($userRole); ?></small>
            </div>
        </div>
        <div class="mt-3">
            <a href="profile" class="btn btn-sm btn-outline-primary me-2">
                <i class="fas fa-user me-1"></i>Profile
            </a>
            <a href="/School management/public/logout" class="btn btn-sm btn-outline-danger">
                <i class="fas fa-sign-out-alt me-1"></i>Logout
            </a>
        </div>
    </div>
</div>
