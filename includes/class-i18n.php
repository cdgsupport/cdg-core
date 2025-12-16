<?php
declare(strict_types=1);

/**
 * Define the internationalization functionality.
 *
 * @package QuickTools
 * @since 1.0.0
 */
class Quick_Tools_i18n {

    /**
     * Load the plugin text domain for translation.
     */
    public function load_plugin_textdomain(): void {
        load_plugin_textdomain(
            'quick-tools',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}
