<?php
/**
 * Admin Dashboard View
 * School Management System
 */

// Set page variables
$page_title = 'Admin Dashboard - School Management System';
$show_sidebar = true;
$show_navbar = true;
$show_footer = true;

// Include header
include APP_PATH . '/views/layouts/main.php';
?>

<div class="dashboard-container">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="dashboard-title">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Admin Dashboard
                </h1>
                <p class="dashboard-subtitle">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?>!</p>
            </div>
            <div class="col-md-4 text-end">
                <span class="status-badge">
                    <i class="fas fa-circle me-1"></i>
                    System Online
                </span>
            </div>
        </div>
    </div>

    <!-- Statistics Section -->
    <div class="stats-section">
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo $stats['total_students'] ?? 0; ?></h3>
                        <p class="stat-label">Total Students</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stat-card stat-success">
                    <div class="stat-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo $stats['total_teachers'] ?? 0; ?></h3>
                        <p class="stat-label">Total Teachers</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stat-card stat-info">
                    <div class="stat-icon">
                        <i class="fas fa-door-open"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo $stats['total_classes'] ?? 0; ?></h3>
                        <p class="stat-label">Total Classes</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stat-card stat-warning">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo $stats['total_subjects'] ?? 0; ?></h3>
                        <p class="stat-label">Total Subjects</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Section -->
    <div class="actions-section">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-bolt me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3 col-6">
                        <a href="/School management/admin/students" class="action-btn action-primary">
                            <i class="fas fa-users"></i>
                            <span>Manage Students</span>
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="/School management/admin/teachers" class="action-btn action-success">
                            <i class="fas fa-chalkboard-teacher"></i>
                            <span>Manage Teachers</span>
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="/School management/admin/classes" class="action-btn action-info">
                            <i class="fas fa-door-open"></i>
                            <span>Manage Classes</span>
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="/School management/admin/subjects" class="action-btn action-warning">
                            <i class="fas fa-book"></i>
                            <span>Manage Subjects</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Cards Section -->
    <div class="info-section">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-history me-2"></i>
                            Recent Activities
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recentActivities)): ?>
                            <div class="activity-list">
                                <?php foreach ($recentActivities as $activity): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon">
                                            <i class="fas fa-circle text-<?php echo $activity['type'] ?? 'info'; ?>"></i>
                                        </div>
                                        <div class="activity-content">
                                            <p class="activity-text"><?php echo htmlspecialchars($activity['message'] ?? ''); ?></p>
                                            <small class="activity-time"><?php echo $activity['time'] ?? ''; ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-inbox fa-3x"></i>
                                <p>No recent activities</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-calendar-check me-2"></i>
                            Attendance Overview
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($attendanceOverview)): ?>
                            <div class="attendance-stats">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="attendance-card attendance-present">
                                            <div class="attendance-icon">
                                                <i class="fas fa-user-check"></i>
                                            </div>
                                            <div class="attendance-info">
                                                <h4><?php echo $attendanceOverview['present'] ?? 0; ?></h4>
                                                <p>Present Today</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="attendance-card attendance-absent">
                                            <div class="attendance-icon">
                                                <i class="fas fa-user-times"></i>
                                            </div>
                                            <div class="attendance-info">
                                                <h4><?php echo $attendanceOverview['absent'] ?? 0; ?></h4>
                                                <p>Absent Today</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-chart-line fa-3x"></i>
                                <p>No attendance data available</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
?>
