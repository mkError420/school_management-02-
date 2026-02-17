<!-- Sidebar -->
<div class="bg-white border-end" id="sidebar-wrapper">
    <div class="sidebar-heading bg-primary text-white text-center py-3">
        <i class="fas fa-graduation-cap me-2"></i>
        <strong>School MS</strong>
    </div>
    
    <div class="list-group list-group-flush">
        <?php
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
        
        foreach ($menu as $route => $item):
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
            <div class="flex-grow-1 ms-3">
                <div class="fw-bold"><?php echo $session->get('user.first_name') . ' ' . $session->get('user.last_name'); ?></div>
                <small class="text-muted"><?php echo ucfirst($userRole); ?></small>
            </div>
        </div>
        
        <div class="mt-3">
            <a href="profile" class="btn btn-sm btn-outline-primary me-2">
                <i class="fas fa-user me-1"></i>Profile
            </a>
            <a href="logout" class="btn btn-sm btn-outline-danger">
                <i class="fas fa-sign-out-alt me-1"></i>Logout
            </a>
        </div>
    </div>
</div>

<!-- Sidebar Toggle Button (for mobile) -->
<button class="btn btn-primary d-md-none" id="sidebar-toggle">
    <i class="fas fa-bars"></i>
</button>
