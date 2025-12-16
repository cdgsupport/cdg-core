<?php
if (!defined('WPINC')) die;

if (isset($_POST['submit_documentation'])) {
    if (!wp_verify_nonce($_POST['_wpnonce'], 'quick-tools-documentation-settings')) {
        wp_die('Security check failed');
    }
    
    $existing_settings = get_option('quick_tools_settings', array());
    $existing_settings['show_documentation_widgets'] = isset($_POST['show_documentation_widgets']) ? 1 : 0;
    $existing_settings['show_documentation_status'] = isset($_POST['show_documentation_status']) ? 1 : 0;
    $existing_settings['documentation_widget_limit'] = isset($_POST['documentation_widget_limit']) ? 
        max(1, min(10, intval($_POST['documentation_widget_limit']))) : 5;
    $existing_settings['documentation_module_style'] = $_POST['documentation_module_style'] ?? 'informative';
    
    update_option('quick_tools_settings', $existing_settings);
    echo '<div class="notice notice-success is-dismissible inline"><p>Documentation settings saved!</p></div>';
}
$settings = get_option('quick_tools_settings', array());
$module_style = $settings['documentation_module_style'] ?? 'informative';
?>

<form method="post" action="">
    <?php wp_nonce_field('quick-tools-documentation-settings'); ?>
    
    <div style="display: flex; gap: 30px; flex-wrap: wrap;">
        
        <div style="flex: 1; min-width: 300px;">
            <h3><?php _e('Widget Settings', 'quick-tools'); ?></h3>
            
            <div class="qt-card">
                <div class="qt-card-body">
                    <div class="qt-toggle-wrapper">
                        <label class="qt-toggle">
                            <input type="checkbox" name="show_documentation_widgets" value="1" 
                                   <?php checked($settings['show_documentation_widgets'] ?? 1, 1); ?>>
                            <span class="qt-slider"></span>
                        </label>
                        <span class="qt-text-muted"><strong>Show widgets on dashboard</strong></span>
                    </div>

                    <div class="qt-mb-2">
                        <p class="qt-text-muted qt-mb-2"><strong>Visual Style</strong></p>
                        <div class="qt-btn-group">
                            <input type="radio" id="doc_style_info" name="documentation_module_style" value="informative" <?php checked($module_style, 'informative'); ?>>
                            <label for="doc_style_info">Informative</label>
                            
                            <input type="radio" id="doc_style_min" name="documentation_module_style" value="minimal" <?php checked($module_style, 'minimal'); ?>>
                            <label for="doc_style_min">Minimal</label>
                        </div>
                    </div>

                    <div class="qt-informative-options" <?php echo $module_style === 'minimal' ? 'style="display:none; margin-top:20px;"' : 'style="margin-top:20px;"'; ?>>
                        <div class="qt-mb-2">
                            <label>Items per Widget</label><br>
                            <input type="number" class="small-text" name="documentation_widget_limit" max="10" min="1" 
                                   value="<?php echo esc_attr($settings['documentation_widget_limit'] ?? 5); ?>">
                        </div>
                        <div class="qt-toggle-wrapper" style="margin-top: 15px;">
                            <label class="qt-toggle" style="transform:scale(0.8)">
                                <input type="checkbox" name="show_documentation_status" value="1" 
                                       <?php checked($settings['show_documentation_status'] ?? 1, 1); ?>>
                                <span class="qt-slider"></span>
                            </label>
                            <span class="qt-text-muted">Show Status Indicators</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="flex: 0 0 300px;">
            <h3><?php _e('Actions', 'quick-tools'); ?></h3>
            
            <div style="margin-bottom: 20px;">
                <a href="<?php echo admin_url('post-new.php?post_type=qt_documentation'); ?>" 
                   class="button button-primary button-large qt-full-width qt-mb-2" style="text-align:center; margin-bottom:10px;">
                    Add New Documentation
                </a>
                <a href="<?php echo admin_url('edit-tags.php?taxonomy=qt_documentation_category&post_type=qt_documentation'); ?>" 
                   class="button button-secondary qt-full-width" style="text-align:center;">
                    Manage Categories
                </a>
            </div>
        </div>
    </div>

    <p class="submit">
        <button type="submit" name="submit_documentation" class="button button-primary button-large">
            Save Settings
        </button>
    </p>
</form>

<script>
jQuery(document).ready(function($) {
    $('input[name="documentation_module_style"]').on('change', function() {
        if ($(this).val() === 'minimal') $('.qt-informative-options').slideUp(200);
        else $('.qt-informative-options').slideDown(200);
    });
});
</script>
