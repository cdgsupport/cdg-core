<?php
if (!defined('WPINC')) die;
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'documentation';
?>

<div class="wrap qt-admin-page">
    <div class="qt-header">
        <h1><?php echo get_admin_page_title(); ?></h1>
        <p class="qt-description"><?php _e('Manage your documentation and custom post type shortcuts.', 'quick-tools'); ?></p>
    </div>

    <?php
    if (get_option('quick_tools_activated')) {
        delete_option('quick_tools_activated');
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Quick Tools activated! Configure settings below.', 'quick-tools') . '</p></div>';
    }
    ?>

    <div class="qt-grid">
        <div class="qt-main">
            <nav class="qt-nav-tab-wrapper">
                <a href="?page=quick-tools&tab=documentation" 
                   class="qt-nav-tab <?php echo $active_tab === 'documentation' ? 'active' : ''; ?>">
                    <span class="dashicons dashicons-media-document"></span>
                    <?php _e('Documentation', 'quick-tools'); ?>
                </a>
                <a href="?page=quick-tools&tab=cpt-dashboard" 
                   class="qt-nav-tab <?php echo $active_tab === 'cpt-dashboard' ? 'active' : ''; ?>">
                    <span class="dashicons dashicons-admin-post"></span>
                    <?php _e('CPT Locations', 'quick-tools'); ?>
                </a>
                <a href="?page=quick-tools&tab=import-export" 
                   class="qt-nav-tab <?php echo $active_tab === 'import-export' ? 'active' : ''; ?>">
                    <span class="dashicons dashicons-download"></span>
                    <?php _e('Import/Export', 'quick-tools'); ?>
                </a>
            </nav>

            <div class="qt-tab-content">
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

        <div class="qt-sidebar">
            <div class="qt-card qt-sidebar-card">
                <div class="qt-card-header">
                    <?php _e('Quick Stats', 'quick-tools'); ?>
                </div>
                <ul class="qt-list-group">
                    <?php
                    $doc_count = wp_count_posts('qt_documentation');
                    $cat_count = wp_count_terms(array('taxonomy' => 'qt_documentation_category'));
                    ?>
                    <li class="qt-list-item">
                        Documentation
                        <span class="qt-badge"><?php echo $doc_count->publish ?? 0; ?></span>
                    </li>
                    <li class="qt-list-item">
                        Categories
                        <span class="qt-badge"><?php echo is_wp_error($cat_count) ? 0 : $cat_count; ?></span>
                    </li>
                </ul>
            </div>

            <div class="qt-card qt-sidebar-card">
                <div class="qt-card-header">
                    <?php _e('Support', 'quick-tools'); ?>
                </div>
                <div style="padding: 10px;">
                    <p class="description" style="margin-top:0;">Built by Crawford Design Group.</p>
                    <a href="https://crawforddesigngp.com" target="_blank" class="button button-secondary" style="width:100%; text-align:center;">Visit Website</a>
                </div>
            </div>
        </div>
    </div>
</div>
