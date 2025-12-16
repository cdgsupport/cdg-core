<?php
if (!defined('WPINC')) die;

// Process Form
if (isset($_POST['submit_cpt'])) {
    if (!wp_verify_nonce($_POST['_wpnonce'], 'quick-tools-cpt-settings')) wp_die('Security check failed');
    
    $existing_settings = get_option('quick_tools_settings', array());
    
    $existing_settings['show_cpt_widgets'] = isset($_POST['show_cpt_widgets']) ? 1 : 0;
    $existing_settings['cpt_module_style'] = $_POST['cpt_module_style'] ?? 'informative';
    
    // Process complex CPT selection with locations
    $selected_cpts = array();
    if (isset($_POST['cpt_config']) && is_array($_POST['cpt_config'])) {
        foreach ($_POST['cpt_config'] as $cpt_slug => $config) {
            if (isset($config['enabled']) && $config['enabled'] == 1) {
                $selected_cpts[$cpt_slug] = array(
                    'location' => sanitize_text_field($config['location'])
                );
            }
        }
    }
    $existing_settings['selected_cpts'] = $selected_cpts;
    
    update_option('quick_tools_settings', $existing_settings);
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">Settings saved successfully!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
}

$settings = get_option('quick_tools_settings', array());
$post_types = Quick_Tools_CPT_Dashboard::get_available_post_types();
$available_pages = Quick_Tools_Admin::get_registered_options_pages();
$module_style = $settings['cpt_module_style'] ?? 'informative';

// Normalize current settings for the view
$current_cpts = array();
if (isset($settings['selected_cpts'])) {
    foreach ($settings['selected_cpts'] as $key => $val) {
        if (is_array($val)) $current_cpts[$key] = $val; // New format
        else $current_cpts[$val] = array('location' => 'dashboard'); // Old format
    }
}
?>

<form method="post" action="">
    <?php wp_nonce_field('quick-tools-cpt-settings'); ?>
    
    <div class="mb-4">
        <h4><?php _e('Global Settings', 'quick-tools'); ?></h4>
        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" name="show_cpt_widgets" value="1" id="show_cpt_widgets" 
                   <?php checked(isset($settings['show_cpt_widgets']) ? $settings['show_cpt_widgets'] : 1, 1); ?>>
            <label class="form-check-label" for="show_cpt_widgets">Enable Quick Add functionality</label>
        </div>

        <div class="mb-3">
            <label class="form-label">Dashboard Widget Style</label>
            <div class="btn-group w-100" role="group">
                <input type="radio" class="btn-check" name="cpt_module_style" id="style_informative" value="informative" <?php checked($module_style, 'informative'); ?>>
                <label class="btn btn-outline-primary" for="style_informative">
                    <strong>Informative</strong><br><small>Detailed stats & recent items</small>
                </label>

                <input type="radio" class="btn-check" name="cpt_module_style" id="style_minimal" value="minimal" <?php checked($module_style, 'minimal'); ?>>
                <label class="btn btn-outline-primary" for="style_minimal">
                    <strong>Minimal</strong><br><small>Simple buttons only</small>
                </label>
            </div>
            <div class="form-text text-muted mt-2">Note: Style only applies to Dashboard widgets. Options page buttons always appear as a notification bar.</div>
        </div>
    </div>

    <hr class="my-4">

    <div class="mb-4">
        <h4><?php _e('Post Type Configuration', 'quick-tools'); ?></h4>
        <p class="text-muted">Select post types and choose where their "Add New" button should appear.</p>

        <?php if (empty($post_types)) : ?>
            <div class="alert alert-warning">No custom post types found.</div>
        <?php else : ?>
            <div class="row g-3">
                <?php foreach ($post_types as $pt) : 
                    $is_enabled = array_key_exists($pt->name, $current_cpts);
                    $location = $is_enabled ? ($current_cpts[$pt->name]['location'] ?? 'dashboard') : 'dashboard';
                    $stats = Quick_Tools_CPT_Dashboard::get_post_type_stats($pt->name);
                ?>
                <div class="col-md-6 col-xl-4">
                    <div class="card h-100 <?php echo $is_enabled ? 'border-primary' : ''; ?>">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" 
                                       name="cpt_config[<?php echo esc_attr($pt->name); ?>][enabled]" 
                                       value="1" 
                                       id="cpt_<?php echo esc_attr($pt->name); ?>"
                                       <?php checked($is_enabled, true); ?>>
                                <label class="form-check-label fw-bold" for="cpt_<?php echo esc_attr($pt->name); ?>">
                                    <?php echo esc_html($pt->labels->name); ?>
                                </label>
                            </div>
                            <span class="badge bg-light text-dark border"><?php echo $pt->name; ?></span>
                        </div>
                        <div class="card-body">
                            <p class="small text-muted mb-2"><?php echo $pt->description ? esc_html($pt->description) : 'No description available.'; ?></p>
                            <div class="d-flex justify-content-between small mb-3 text-secondary">
                                <span><?php echo $stats['published']; ?> Published</span>
                                <span><?php echo $stats['draft']; ?> Drafts</span>
                            </div>
                            
                            <div class="mb-0">
                                <label class="form-label form-label-sm">Button Location</label>
                                <select class="form-select form-select-sm" name="cpt_config[<?php echo esc_attr($pt->name); ?>][location]">
                                    <option value="dashboard" <?php selected($location, 'dashboard'); ?>>Dashboard Widget</option>
                                    <?php if (!empty($available_pages)): ?>
                                        <optgroup label="Custom Options Pages">
                                            <?php foreach ($available_pages as $slug => $title): ?>
                                                <option value="<?php echo esc_attr($slug); ?>" <?php selected($location, $slug); ?>>
                                                    <?php echo esc_html($title); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <button type="submit" name="submit_cpt" class="btn btn-primary btn-lg">
        <i class="dashicons dashicons-saved"></i> Save Settings
    </button>
</form>
