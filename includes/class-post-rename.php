<?php
/**
 * Post Rename Class
 *
 * Rebrands the built-in "Post" post type across wp-admin — labels, sidebar
 * menu entry, and menu icon. Purely cosmetic: the underlying post_type slug,
 * rewrite rules, and permalinks are untouched, so nothing on the front end
 * or in the database changes.
 *
 * @package CDG_Core
 * @since 1.9.9
 */

declare(strict_types=1);

class CDG_Core_Post_Rename
{
  private CDG_Core $plugin;

  public function __construct(CDG_Core $plugin)
  {
    $this->plugin = $plugin;
    $this->setup_hooks();
  }

  private function setup_hooks(): void
  {
    if (!$this->plugin->get_setting("enable_post_rename")) {
      return;
    }

    add_filter("register_post_type_args", [$this, "filter_post_labels"], 10, 2);

    // Priority 1000: after CDG_Core_Plugin_Visibility's generic sidebar
    // rename (999), so this always wins if 'edit.php' is also targeted
    // by the generic Sidebar tab.
    add_action("admin_menu", [$this, "rename_sidebar_menu"], 1000);
  }

  /**
   * Rebuild the full label set for the built-in 'post' post type.
   *
   * Unlike a freshly-registered CPT that only sets name/singular_name and
   * lets WordPress auto-derive the rest, 'post' already ships every label
   * key explicitly (for translators). Nothing is "missing" for the
   * auto-fill logic to fill in, so every key has to be set here directly.
   *
   * @param mixed $args Post type registration args
   * @param string $post_type Post type name
   * @return array
   */
  public function filter_post_labels($args, $post_type): array
  {
    if ($post_type !== "post" || !is_array($args)) {
      return is_array($args) ? $args : [];
    }

    $singular = $this->singular();
    $plural = $this->plural();
    $lower_singular = strtolower($singular);
    $lower_plural = strtolower($plural);

    $args["label"] = $plural;
    $args["menu_icon"] = "dashicons-" . $this->icon();
    $args["labels"] = [
      "name" => $plural,
      "singular_name" => $singular,
      "menu_name" => $plural,
      "name_admin_bar" => $singular,
      "add_new" => sprintf(__("Add New %s", "cdg-core"), $singular),
      "add_new_item" => sprintf(__("Add New %s", "cdg-core"), $singular),
      "edit_item" => sprintf(__("Edit %s", "cdg-core"), $singular),
      "new_item" => sprintf(__("New %s", "cdg-core"), $singular),
      "view_item" => sprintf(__("View %s", "cdg-core"), $singular),
      "view_items" => sprintf(__("View %s", "cdg-core"), $plural),
      "search_items" => sprintf(__("Search %s", "cdg-core"), $plural),
      "not_found" => sprintf(__("No %s found.", "cdg-core"), $lower_plural),
      "not_found_in_trash" => sprintf(
        __("No %s found in Trash.", "cdg-core"),
        $lower_plural
      ),
      "all_items" => sprintf(__("All %s", "cdg-core"), $plural),
      "archives" => sprintf(__("%s Archives", "cdg-core"), $singular),
      "attributes" => sprintf(__("%s Attributes", "cdg-core"), $singular),
      "insert_into_item" => sprintf(
        __("Insert into %s", "cdg-core"),
        $lower_singular
      ),
      "uploaded_to_this_item" => sprintf(
        __("Uploaded to this %s", "cdg-core"),
        $lower_singular
      ),
      "featured_image" => __("Featured Image", "cdg-core"),
      "set_featured_image" => __("Set featured image", "cdg-core"),
      "remove_featured_image" => __("Remove featured image", "cdg-core"),
      "use_featured_image" => __("Use as featured image", "cdg-core"),
      "filter_items_list" => sprintf(
        __("Filter %s list", "cdg-core"),
        $lower_plural
      ),
      "items_list_navigation" => sprintf(
        __("%s list navigation", "cdg-core"),
        $plural
      ),
      "items_list" => sprintf(__("%s list", "cdg-core"), $plural),
      "item_published" => sprintf(__("%s published.", "cdg-core"), $singular),
      "item_published_privately" => sprintf(
        __("%s published privately.", "cdg-core"),
        $singular
      ),
      "item_reverted_to_draft" => sprintf(
        __("%s reverted to draft.", "cdg-core"),
        $singular
      ),
      "item_scheduled" => sprintf(__("%s scheduled.", "cdg-core"), $singular),
      "item_updated" => sprintf(__("%s updated.", "cdg-core"), $singular),
    ];

    return $args;
  }

  /**
   * Rewrite the "Posts" sidebar entry and its submenu.
   *
   * WordPress builds this menu from literal hardcoded strings in
   * wp-admin/menu.php, not from the post type object's labels — unlike
   * custom post types, 'post' and 'page' menu entries never read
   * get_post_type_object('post')->labels at render time. filter_post_labels()
   * above has no effect on this menu, so it needs its own direct rewrite.
   *
   * @return void
   */
  public function rename_sidebar_menu(): void
  {
    global $menu, $submenu;

    if (!is_array($menu)) {
      return;
    }

    $singular = $this->singular();
    $plural = $this->plural();
    $icon = "dashicons-" . $this->icon();

    foreach ($menu as $pos => $item) {
      if (($item[2] ?? "") === "edit.php") {
        $menu[$pos][0] = esc_html($plural);
        $menu[$pos][6] = esc_attr($icon);
        break;
      }
    }

    if (!isset($submenu["edit.php"]) || !is_array($submenu["edit.php"])) {
      return;
    }

    foreach ($submenu["edit.php"] as $pos => $item) {
      $slug = $item[2] ?? "";

      if ($slug === "edit.php") {
        /* translators: %s: plural label (e.g. "Articles") */
        $submenu["edit.php"][$pos][0] = esc_html(
          sprintf(__("All %s", "cdg-core"), $plural)
        );
      } elseif ($slug === "post-new.php") {
        /* translators: %s: singular label (e.g. "Article") */
        $submenu["edit.php"][$pos][0] = esc_html(
          sprintf(__("Add New %s", "cdg-core"), $singular)
        );
      }
    }
  }

  private function singular(): string
  {
    $value = trim((string) $this->plugin->get_setting("post_rename_singular"));
    return $value !== "" ? $value : "Post";
  }

  private function plural(): string
  {
    $value = trim((string) $this->plugin->get_setting("post_rename_plural"));
    return $value !== "" ? $value : "Posts";
  }

  private function icon(): string
  {
    $value = trim((string) $this->plugin->get_setting("post_rename_icon"));
    $value = $value !== "" ? $value : "admin-post";
    return preg_replace('/^dashicons-/', "", $value);
  }
}
