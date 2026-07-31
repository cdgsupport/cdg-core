<?php
/**
 * Roles
 *
 * Opt-in (Roles tab, "Enable Custom Roles") registration of three CDG
 * client-site roles, plus an optional filter that hides native WordPress
 * roles from the role-assignment dropdown so future users are always put
 * into one of these three buckets.
 *
 * - Agency  (cdg_agency)         — CDG staff. Clone of Administrator.
 *                                   Always bypasses sidebar hide rules.
 * - Manager (cdg_client_manager) — Administrator capabilities minus
 *                                   plugin/theme install-and-edit, user
 *                                   management, core updates, and the
 *                                   file editor.
 * - Staff   (cdg_client_staff)   — Clone of Editor. Content only.
 *
 * (Role slugs keep their original "client_manager" / "client_staff" values
 * for stability — only the display labels changed.)
 *
 * Both behaviors are off by default. Native roles (administrator, editor,
 * author, contributor, subscriber) always remain registered — WordPress
 * core and other plugins assume they exist — the "Hide Default WordPress
 * Roles" setting only affects the Add User / Edit User / Bulk Edit
 * dropdowns. Existing users keep whatever role they already have; nothing
 * here changes an existing account's role automatically. Turning "Enable
 * Custom Roles" back off after it's been on does not remove the roles —
 * it just stops the plugin from managing them further.
 *
 * @package CDG_Core
 * @since 1.8.0
 */

declare(strict_types=1);

class CDG_Core_Roles
{
    public const AGENCY         = 'cdg_agency';
    public const CLIENT_MANAGER = 'cdg_client_manager';
    public const CLIENT_STAFF   = 'cdg_client_staff';

    /**
     * Capabilities stripped from Administrator's live capability set to
     * build Manager. Anything not listed here — including caps added
     * by third-party plugins (Divi, Gravity Forms, Wordfence, Yoast, etc.)
     * riding on top of Administrator — is kept.
     *
     * @var string[]
     */
    private const MANAGER_BLOCKLIST = [
        // Plugins
        'activate_plugins',
        'install_plugins',
        'update_plugins',
        'delete_plugins',
        'edit_plugins',
        // Themes
        'install_themes',
        'update_themes',
        'delete_themes',
        'edit_themes',
        'switch_themes',
        // Users
        'create_users',
        'add_users',
        'edit_users',
        'delete_users',
        'remove_users',
        'promote_users',
        'list_users',
        // Core / files
        'update_core',
        'edit_files',
        // Multisite (defensive; irrelevant on single-site installs)
        'manage_network',
        'manage_network_users',
        'manage_network_plugins',
        'manage_network_themes',
        'manage_network_options',
        'manage_sites',
        'upgrade_network',
        'setup_network',
        'create_sites',
        'delete_sites',
    ];

    /** Native WordPress roles hidden from the role-assignment dropdown. */
    private const NATIVE_ROLES = [
        'administrator',
        'editor',
        'author',
        'contributor',
        'subscriber',
    ];

    /** @var CDG_Core */
    private CDG_Core $plugin;

    public function __construct(CDG_Core $plugin)
    {
        $this->plugin = $plugin;

        // Self-healing: (re)create any of the 3 roles if missing. Cheap —
        // get_role() reads the already-loaded WP_Roles object in memory.
        add_action('init', [$this, 'maybe_register_roles']);

        // Hide native roles from Add User / Edit User / Bulk Edit dropdowns.
        add_filter('editable_roles', [$this, 'hide_native_roles']);
    }

    /**
     * Register the 3 roles if the feature is enabled and any of them are
     * missing. No-op (and never removes anything) when the setting is off.
     */
    public function maybe_register_roles(): void
    {
        if (!$this->plugin->get_setting('enable_custom_roles')) {
            return;
        }

        if (
            get_role(self::AGENCY) &&
            get_role(self::CLIENT_MANAGER) &&
            get_role(self::CLIENT_STAFF)
        ) {
            return;
        }

        self::register_roles();
    }

    /**
     * (Re)create all 3 roles from the current live Administrator/Editor
     * capability sets. Safe to call more than once — add_role() after
     * remove_role() fully replaces the role's capability set each time.
     */
    public static function register_roles(): void
    {
        $admin  = get_role('administrator');
        $editor = get_role('editor');

        if (!$admin || !$editor) {
            // Unexpected on a normal WordPress install — bail rather than
            // create roles with no capabilities.
            return;
        }

        // Agency: exact clone of Administrator.
        remove_role(self::AGENCY);
        add_role(self::AGENCY, __('Agency', 'cdg-core'), $admin->capabilities);

        // Manager: Administrator minus the blocklist above.
        $manager_caps = $admin->capabilities;
        foreach (self::MANAGER_BLOCKLIST as $cap) {
            unset($manager_caps[$cap]);
        }
        remove_role(self::CLIENT_MANAGER);
        add_role(self::CLIENT_MANAGER, __('Manager', 'cdg-core'), $manager_caps);

        // Staff: exact clone of Editor.
        remove_role(self::CLIENT_STAFF);
        add_role(self::CLIENT_STAFF, __('Staff', 'cdg-core'), $editor->capabilities);
    }

    /**
     * Remove native WordPress roles from the editable-roles list used by
     * the Add User, Edit User, and Bulk Edit screens. The roles themselves
     * stay registered; this only affects what can be newly assigned. Only
     * takes effect when both "Enable Custom Roles" and "Hide Default
     * WordPress Roles" are turned on in the Roles tab.
     *
     * @param array<string, array<string, mixed>> $roles
     * @return array<string, array<string, mixed>>
     */
    public function hide_native_roles(array $roles): array
    {
        if (
            !$this->plugin->get_setting('enable_custom_roles') ||
            !$this->plugin->get_setting('hide_native_roles')
        ) {
            return $roles;
        }

        foreach (self::NATIVE_ROLES as $role) {
            unset($roles[$role]);
        }
        return $roles;
    }

    /**
     * Roles that can be targeted by the Sidebar tab's hide/rename/order
     * controls, in display order.
     *
     * @return array<string, string> role slug => label
     */
    public static function target_roles(): array
    {
        return [
            self::CLIENT_MANAGER => __('Manager', 'cdg-core'),
            self::CLIENT_STAFF   => __('Staff', 'cdg-core'),
        ];
    }
}
