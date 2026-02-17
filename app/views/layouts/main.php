<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'School Management System'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?php echo PUBLIC_PATH; ?>/assets/css/main.css" rel="stylesheet">
    
    <!-- Dark Mode CSS -->
    <link href="<?php echo PUBLIC_PATH; ?>/assets/css/dark-mode.css" rel="stylesheet" id="dark-mode-css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo PUBLIC_PATH; ?>/assets/images/favicon.ico">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo generateCSRFToken(); ?>">
</head>
<body class="<?php echo $body_class ?? ''; ?>">
    
    <!-- Main Container -->
    <div class="d-flex" id="wrapper">
        
        <!-- Sidebar -->
        <?php if (isset($show_sidebar) && $show_sidebar): ?>
            <?php include APP_PATH . '/views/components/sidebar.php'; ?>
        <?php endif; ?>
        
        <!-- Page Content -->
        <div id="page-content-wrapper" class="<?php echo isset($show_sidebar) && $show_sidebar ? 'w-100' : ''; ?>">
            
            <!-- Top Navigation -->
            <?php if (isset($show_navbar) && $show_navbar): ?>
                <?php include APP_PATH . '/views/components/navbar.php'; ?>
            <?php endif; ?>
            
            <!-- Main Content -->
            <main class="container-fluid p-4">
                <?php displayFlashMessage(); ?>
                
                <?php if (isset($page_header) && $page_header): ?>
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2"><?php echo $page_header; ?></h1>
                        <?php if (isset($page_actions)): ?>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                <?php echo $page_actions; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Page Content -->
                <?php echo $content ?? ''; ?>
            </main>
            
            <!-- Footer -->
            <?php if (isset($show_footer) && $show_footer): ?>
                <?php include APP_PATH . '/views/components/footer.php'; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo PUBLIC_PATH; ?>/assets/js/main.js"></script>
    
    <!-- Dark Mode Toggle -->
    <script src="<?php echo PUBLIC_PATH; ?>/assets/js/dark-mode.js"></script>
    
    <!-- Page Specific Scripts -->
    <?php if (isset($page_scripts)): ?>
        <?php echo $page_scripts; ?>
    <?php endif; ?>
    
    <!-- Notification System -->
    <div id="notification-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1050;"></div>
    
</body>
</html>
