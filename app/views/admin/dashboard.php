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

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Admin Dashboard</h1>
                <div class="text-muted">
                    Welcome back, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?>!
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Statistics Cards -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo $stats['total_students'] ?? 0; ?></h4>
                            <div class="small">Total Students</div>
                        </div>
                        <div class="fa-2x fa-graduation-cap"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo $stats['total_teachers'] ?? 0; ?></h4>
                            <div class="small">Total Teachers</div>
                        </div>
                        <div class="fa-2x fa-chalkboard-teacher"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo $stats['total_classes'] ?? 0; ?></h4>
                            <div class="small">Total Classes</div>
                        </div>
                        <div class="fa-2x fa-door-open"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo $stats['total_subjects'] ?? 0; ?></h4>
                            <div class="small">Total Subjects</div>
                        </div>
                        <div class="fa-2x fa-book"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="/School management/admin/students" class="btn btn-primary btn-block">
                                <i class="fas fa-users me-2"></i>Manage Students
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="/School management/admin/teachers" class="btn btn-success btn-block">
                                <i class="fas fa-chalkboard-teacher me-2"></i>Manage Teachers
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="/School management/admin/classes" class="btn btn-info btn-block">
                                <i class="fas fa-door-open me-2"></i>Manage Classes
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="/School management/admin/subjects" class="btn btn-warning btn-block">
                                <i class="fas fa-book me-2"></i>Manage Subjects
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Activities</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($recentActivities)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentActivities as $activity): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-circle text-<?php echo $activity['type'] ?? 'info'; ?> me-2"></i>
                                        <?php echo htmlspecialchars($activity['message'] ?? ''); ?>
                                    </div>
                                    <small class="text-muted"><?php echo $activity['time'] ?? ''; ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No recent activities</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Attendance Overview</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($attendanceOverview)): ?>
                        <div class="row text-center">
                            <div class="col-6">
                                <h4 class="text-success"><?php echo $attendanceOverview['present'] ?? 0; ?></h4>
                                <small>Present Today</small>
                            </div>
                            <div class="col-6">
                                <h4 class="text-danger"><?php echo $attendanceOverview['absent'] ?? 0; ?></h4>
                                <small>Absent Today</small>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No attendance data available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include APP_PATH . '/views/components/footer.php';
?>
