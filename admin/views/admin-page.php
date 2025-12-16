<?php
if (!defined('WPINC')) die;
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'documentation';
?>

<div class="wrap qt-admin-wrapper">
    <div class="qt-header">
        <h1><?php echo get_admin_page_title(); ?></h1>
        <p class="qt-description"><?php _e('Manage your documentation and create custom shortcuts for your post types.', 'quick-tools'); ?></p>
    </div>

    <?php
    if (get_option('quick_tools_activated')) {
        delete_option('quick_tools_activated');
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Quick Tools activated! Configure settings below.', 'quick-tools') . '</p></div>';
    }
    ?>

    <div class="qt-layout-container">
        
        <div class="qt-main-column">
            
            <nav class="qt-nav-tab-wrapper">
                <a href="?page=quick-tools&tab=documentation" 
                   class="qt-nav-tab <?php echo $active_tab === 'documentation' ? 'active' : ''; ?>">
                    <span class="dashicons dashicons-media-document"></span>
                    <?php _e('Documentation', 'quick-tools'); ?>
                </a>
                <a href="?page=quick-tools&tab=cpt-dashboard" 
                   class="qt-nav-tab <?php echo $active_tab === 'cpt-dashboard' ? 'active' : ''; ?>">
                    <span class="dashicons dashicons-admin-post"></span>
                    <?php _e('Post Types & Buttons', 'quick-tools'); ?>
                </a>
                <a href="?page=quick-tools&tab=import-export" 
                   class="qt-nav-tab <?php echo $active_tab === 'import-export' ? 'active' : ''; ?>">
                    <span class="dashicons dashicons-download"></span>
                    <?php _e('Import/Export', 'quick-tools'); ?>
                </a>
            </nav>

            <div class="qt-card">
                <div class="qt-card-body">
                    <?php
                    switch ($active_tab) {
                        case 'documentation':
                            include_once QUICK_TOOLS_PLUGIN_DIR . 'admin/views/documentation-tab.php';
                            break;
                        case 'cpt-dashboard':
                            include_once QUICK_TOOLS_PLUGIN_DIR . 'admin/views/cpt-dashboard-tab.php';
                            break;
                        case 'import-export':
                            include_once QUICK_TOOLS_PLUGIN_DIR . 'admin/views/import-export-tab.php';
                            break;
                        default:
                            include_once QUICK_TOOLS_PLUGIN_DIR . 'admin/views/documentation-tab.php';
                            break;
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="qt-sidebar-column">
            
            <div class="qt-card">
                <div class="qt-card-header">
                    <?php _e('At a Glance', 'quick-tools'); ?>
                </div>
                <ul class="qt-list-group">
                    <?php
                    $doc_count = wp_count_posts('qt_documentation');
                    $cat_count = wp_count_terms(array('taxonomy' => 'qt_documentation_category'));
                    ?>
                    <li class="qt-list-item">
                        <span><span class="dashicons dashicons-media-document" style="color:#aaa;"></span> Documentation</span>
                        <span class="qt-badge"><?php echo $doc_count->publish ?? 0; ?></span>
                    </li>
                    <li class="qt-list-item">
                        <span><span class="dashicons dashicons-category" style="color:#aaa;"></span> Categories</span>
                        <span class="qt-badge"><?php echo is_wp_error($cat_count) ? 0 : $cat_count; ?></span>
                    </li>
                </ul>
            </div>

            <div class="qt-card">
                <div class="qt-card-header">
                    <?php _e('Plugin Support', 'quick-tools'); ?>
                </div>
                <div class="qt-card-body">
                    <p style="margin-top:0;">Need help or have a feature request?</p>
                    <a href="https://crawforddesigngp.com" target="_blank" class="button button-secondary qt-full-width" style="text-align:center;">
                        Visit Developer Website
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
