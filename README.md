=== Quick Tools ===
Contributors: Crawford Design Group
Tags: dashboard, documentation, admin, custom post types, workflow
Requires at least: 6.0
Tested up to: 6.4
Requires PHP: 8.0
Stable tag: 1.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Streamline your WordPress admin workflow with configurable documentation widgets and quick custom post type creation tools.

== Description ==

Quick Tools enhances your WordPress admin experience by providing two powerful features with customizable display options:

**📚 Documentation System**
* Create internal documentation for website editors
* Organized by categories (Getting Started, Advanced, Troubleshooting)
* Two module styles:
  - **Informative**: Detailed widgets per category with documentation items, excerpts, and management buttons
  - **Minimal**: Single widget with quick-access buttons for each category
* Dashboard widgets show documentation by category
* Admin-only editing (Editors and above can view)
* Built-in search functionality
* Import/export capabilities for easy backup and transfer

**⚡ CPT Dashboard Widgets**
* Quick-add widgets for custom post types
* Two module styles:
  - **Informative**: Individual widgets per post type with statistics, recent posts, and management options
  - **Minimal**: Single widget with "Add Post" buttons for all selected post types
* Large, prominent creation buttons
* Post statistics at-a-glance (Informative style)
* Recent posts with quick edit access (Informative style)
* Configurable number of items displayed

**Key Features:**
* Choose between Informative and Minimal display styles separately for Documentation and CPT widgets
* Clean, intuitive admin interface
* Fully configurable settings
* Responsive design
* Translation ready
* Performance optimized
* No frontend impact
* PHP 8.0+ optimized with type declarations

Perfect for agencies, developers, and anyone managing multiple WordPress sites who needs to provide clear documentation and streamlined workflows for content editors.

== Installation ==

1. Upload the `quick-tools` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to **Quick Tools** in your admin menu to configure settings
4. Choose your preferred module styles (Informative or Minimal) for Documentation and CPT widgets
5. Start creating documentation and selecting custom post types for dashboard widgets

== Frequently Asked Questions ==

= What's the difference between Informative and Minimal module styles? =

**Informative Style**: Provides detailed widgets with full information. For documentation, each category gets its own widget showing items with excerpts. For CPTs, each post type gets a widget with statistics, recent posts, and management buttons.

**Minimal Style**: Provides a cleaner, simpler interface. Both documentation and CPTs are consolidated into single widgets with just quick-action buttons for each item.

= Who can edit documentation? =

Only users with administrator privileges can add or edit documentation. Editors and above can view documentation but cannot modify it.

= Can I use different styles for Documentation and CPT widgets? =

Yes! You can independently choose Informative or Minimal style for Documentation widgets and CPT widgets. Mix and match based on your preferences.

= How many custom post types can I add to the dashboard? =

There's no limit to the number of custom post types you can select. In Informative style, each gets its own widget. In Minimal style, all selected CPTs appear as buttons in a single widget.

= Can I export my documentation to use on other sites? =

Absolutely! The Import/Export feature allows you to backup your documentation or transfer it to other sites running Quick Tools.

= Does this plugin affect my website's frontend performance? =

No. Quick Tools is purely an admin enhancement and has no impact on your website's frontend performance or appearance.

= Will this work with my existing custom post types? =

Yes! Quick Tools automatically detects all available custom post types (created by themes or other plugins) and allows you to select which ones should have dashboard widgets.

== Screenshots ==

1. Main settings page with tabbed interface
2. Module style selection for Documentation and CPT widgets
3. Informative style documentation widgets (one per category)
4. Minimal style documentation widget (single widget with category buttons)
5. Informative style CPT widgets with statistics
6. Minimal style CPT widget (single widget with all CPT buttons)
7. Import/export interface for documentation backup
8. Documentation editor with category assignment

== Changelog ==

= 1.2.0 =
* Added category archive pages for documentation in minimal style
* Improved informative style documentation widget with 2-column grid layout
* Removed edit pencil icon from informative documentation widgets
* Enhanced responsive design for documentation buttons
* Better handling of long documentation titles in grid layout
* Added documentation view counts and last viewed tracking on category pages
* Improved button layout to accommodate longer titles without breaking

= 1.1.0 =
* Added module style options (Informative and Minimal) for both Documentation and CPT widgets
* Separate style selection for Documentation and CPT widgets
* Updated permission system: Only admins can edit documentation, editors+ can view
* Minimal style consolidates multiple widgets into single, cleaner widgets
* Improved PHP 8.0+ compatibility with type declarations
* Enhanced UI for module style selection
* Added JavaScript to show/hide relevant options based on style selection
* Better organization of dashboard widgets based on selected styles based on style selection
* Better organization of dashboard widgets based on selected styles

= 1.0.0 =
* Initial release
* Documentation system with categorized dashboard widgets
* Custom post type quick-add dashboard widgets
* Import/export functionality
* Built-in search capabilities
* Responsive admin interface
* Translation support

== Upgrade Notice ==

= 1.2.0 =
Improved dashboard widget layouts! Documentation now uses a clean 2-column grid in informative style, and minimal style category buttons now link to proper archive pages.

= 1.1.0 =
New module styles! Choose between Informative (detailed) and Minimal (clean) display options for your dashboard widgets. PHP 8.0+ now required.

= 1.0.0 =
Initial release of Quick Tools. Activate to start streamlining your WordPress admin workflow.

== Developer Information ==

**Plugin Structure:**
```
quick-tools/
├── quick-tools.php (main plugin file)
├── includes/
│   ├── class-quick-tools.php
│   ├── class-documentation.php
│   ├── class-cpt-dashboard.php
│   ├── class-admin.php
│   ├── class-loader.php
│   ├── class-i18n.php
│   ├── class-activator.php
│   └── class-deactivator.php
├── admin/
│   ├── css/admin-style.css
│   ├── js/admin-script.js
│   └── views/
│       ├── admin-page.php
│       ├── documentation-tab.php
│       ├── cpt-dashboard-tab.php
│       └── import-export-tab.php
└── languages/
    └── quick-tools.pot
```

**Custom Post Type:** `qt_documentation`
**Taxonomy:** `qt_doc_category`

**Module Style Constants:**
* `Quick_Tools_Documentation::MODULE_STYLE_INFORMATIVE`
* `Quick_Tools_Documentation::MODULE_STYLE_MINIMAL`
* `Quick_Tools_CPT_Dashboard::MODULE_STYLE_INFORMATIVE`
* `Quick_Tools_CPT_Dashboard::MODULE_STYLE_MINIMAL`

**Settings Keys:**
* `documentation_module_style` - Style for documentation widgets
* `cpt_module_style` - Style for CPT widgets

**Hooks Available:**
* `qt_cpt_custom_actions` - Add custom actions to CPT widgets
* `qt_documentation_capability` - Filter admin capability for documentation
* `qt_documentation_view_capability` - Filter view capability for documentation
* Standard WordPress post type and taxonomy hooks

**Requirements:**
* WordPress 6.0+
* PHP 8.0+
* Administrator privileges for documentation management
* Editor+ privileges for documentation viewing

== Support ==

For support, documentation, or customization services, visit [Crawford Design Group](https://crawforddesigngp.com).

This plugin is actively maintained and supported. We welcome feedback and feature requests.
