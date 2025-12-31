<?php
/**
 * SVG Support Class
 *
 * Handles SVG upload support with security sanitization.
 *
 * @package CDG_Core
 * @since 1.1.0
 */

declare(strict_types=1);

class CDG_Core_SVG_Support
{
    /**
     * Plugin instance
     *
     * @var CDG_Core
     */
    private CDG_Core $plugin;

    /**
     * Allowed SVG elements
     *
     * @var array<string>
     */
    private array $allowed_elements = [
        "svg",
        "g",
        "path",
        "circle",
        "ellipse",
        "rect",
        "line",
        "polyline",
        "polygon",
        "text",
        "tspan",
        "textpath",
        "defs",
        "use",
        "symbol",
        "clippath",
        "lineargradient",
        "radialgradient",
        "stop",
        "mask",
        "pattern",
        "image",
        "title",
        "desc",
        "metadata",
        "switch",
        "foreignobject",
        "a",
    ];

    /**
     * Allowed SVG attributes
     *
     * @var array<string>
     */
    private array $allowed_attributes = [
        // Core attributes
        "id",
        "class",
        "style",
        "lang",
        "tabindex",

        // Presentation attributes
        "fill",
        "fill-opacity",
        "fill-rule",
        "stroke",
        "stroke-dasharray",
        "stroke-dashoffset",
        "stroke-linecap",
        "stroke-linejoin",
        "stroke-miterlimit",
        "stroke-opacity",
        "stroke-width",
        "color",
        "opacity",
        "visibility",
        "display",

        // Geometry attributes
        "x",
        "y",
        "x1",
        "y1",
        "x2",
        "y2",
        "cx",
        "cy",
        "r",
        "rx",
        "ry",
        "width",
        "height",
        "d",
        "points",
        "transform",
        "viewbox",
        "preserveaspectratio",
        "xmlns",
        "xmlns:xlink",
        "version",

        // Gradient attributes
        "offset",
        "stop-color",
        "stop-opacity",
        "gradientunits",
        "gradienttransform",
        "spreadmethod",
        "fx",
        "fy",

        // Text attributes
        "font-family",
        "font-size",
        "font-style",
        "font-weight",
        "text-anchor",
        "dominant-baseline",
        "alignment-baseline",
        "letter-spacing",
        "word-spacing",
        "text-decoration",
        "dx",
        "dy",
        "rotate",
        "textlength",
        "lengthadjust",

        // Reference attributes
        "href",
        "xlink:href",
        "clip-path",
        "mask",
        "filter",
        "marker-start",
        "marker-mid",
        "marker-end",

        // Other attributes
        "clip-rule",
        "enable-background",
        "patternunits",
        "patterntransform",
        "patterncontentunits",
        "maskcontentunits",
        "maskunits",
        "data-name",
        "role",
        "aria-hidden",
        "aria-label",
        "aria-labelledby",
        "aria-describedby",
        "focusable",
    ];

    /**
     * Dangerous patterns to remove
     *
     * @var array<string>
     */
    private array $dangerous_patterns = [
        // Script-related
        "/<script\b[^>]*>.*?<\/script>/is",
        "/on\w+\s*=/i",
        "/javascript\s*:/i",
        "/vbscript\s*:/i",
        "/data\s*:\s*(?!image\/)/i",

        // External references (potential XSS vectors)
        "/<foreignobject\b[^>]*>.*?<\/foreignobject>/is",

        // Embedding
        "/<embed\b[^>]*>/i",
        "/<object\b[^>]*>/i",
        "/<iframe\b[^>]*>/i",

        // PHP and server-side
        "/<\?php/i",
        "/<\?=/i",
        "/<\?/i",
        "/<\%/i",

        // Entity attacks
        "/<!ENTITY/i",
        "/<!DOCTYPE[^>]*\[/i",
        '/SYSTEM\s+["\'][^"\']*["\']/i',
        '/PUBLIC\s+["\'][^"\']*["\']/i',
    ];

    /**
     * Constructor
     *
     * @param CDG_Core $plugin Plugin instance
     */
    public function __construct(CDG_Core $plugin)
    {
        $this->plugin = $plugin;

        if ($this->plugin->get_setting("enable_svg_uploads")) {
            $this->setup_hooks();
        }
    }

    /**
     * Setup hooks
     *
     * @return void
     */
    private function setup_hooks(): void
    {
        // Add SVG to allowed mime types
        add_filter("upload_mimes", [$this, "allow_svg_upload"], 20);

        // Fix SVG mime type detection
        add_filter(
            "wp_check_filetype_and_ext",
            [$this, "fix_svg_mime_type"],
            10,
            5,
        );

        // Sanitize SVG on upload
        add_filter("wp_handle_upload_prefilter", [
            $this,
            "sanitize_svg_upload",
        ]);

        // Add SVG preview support in media library
        add_filter(
            "wp_prepare_attachment_for_js",
            [$this, "add_svg_preview"],
            10,
            3,
        );

        // Allow SVG in attachment display
        add_action("admin_head", [$this, "svg_admin_styles"]);
    }

    /**
     * Allow SVG upload
     *
     * @param array<string, string> $mimes Allowed mime types
     * @return array<string, string>
     */
    public function allow_svg_upload(array $mimes): array
    {
        // Only allow for users with upload capability
        if (!current_user_can("upload_files")) {
            return $mimes;
        }

        // Check if restricted to admins only
        if (
            $this->plugin->get_setting("svg_admin_only") &&
            !current_user_can("manage_options")
        ) {
            return $mimes;
        }

        $mimes["svg"] = "image/svg+xml";
        $mimes["svgz"] = "image/svg+xml";

        return $mimes;
    }

    /**
     * Fix SVG mime type detection
     *
     * WordPress sometimes fails to detect SVG mime types correctly.
     *
     * @param array<string, mixed> $data File data
     * @param string $file File path
     * @param string $filename File name
     * @param array<string, string>|null $mimes Allowed mime types
     * @param string|false $real_mime Real mime type
     * @return array<string, mixed>
     */
    public function fix_svg_mime_type(
        array $data,
        string $file,
        string $filename,
        ?array $mimes,
        $real_mime,
    ): array {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        if (strtolower($ext) === "svg" || strtolower($ext) === "svgz") {
            $data["ext"] = $ext;
            $data["type"] = "image/svg+xml";
        }

        return $data;
    }

    /**
     * Sanitize SVG on upload
     *
     * @param array<string, mixed> $file File data
     * @return array<string, mixed>
     */
    public function sanitize_svg_upload(array $file): array
    {
        if (!isset($file["type"]) || $file["type"] !== "image/svg+xml") {
            // Check file extension as fallback
            $ext = pathinfo($file["name"] ?? "", PATHINFO_EXTENSION);
            if (strtolower($ext) !== "svg" && strtolower($ext) !== "svgz") {
                return $file;
            }
        }

        // Verify the file exists
        if (!isset($file["tmp_name"]) || !file_exists($file["tmp_name"])) {
            return $file;
        }

        // Read SVG content
        $svg_content = file_get_contents($file["tmp_name"]);

        if ($svg_content === false) {
            $file["error"] = __("Failed to read SVG file.", "cdg-core");
            return $file;
        }

        // Validate it's actually an SVG
        if (!$this->is_valid_svg($svg_content)) {
            $file["error"] = __(
                "Invalid SVG file. The file does not contain valid SVG markup.",
                "cdg-core",
            );
            return $file;
        }

        // Sanitize the SVG
        $sanitized = $this->sanitize_svg($svg_content);

        if ($sanitized === false) {
            $file["error"] = __(
                "SVG file could not be sanitized. It may contain potentially dangerous content.",
                "cdg-core",
            );
            return $file;
        }

        // Write sanitized content back
        $result = file_put_contents($file["tmp_name"], $sanitized);

        if ($result === false) {
            $file["error"] = __(
                "Failed to save sanitized SVG file.",
                "cdg-core",
            );
            return $file;
        }

        return $file;
    }

    /**
     * Check if content is valid SVG
     *
     * @param string $content File content
     * @return bool
     */
    private function is_valid_svg(string $content): bool
    {
        // Check for SVG tag
        if (stripos($content, "<svg") === false) {
            return false;
        }

        // Try to parse as XML
        libxml_use_internal_errors(true);

        $doc = new DOMDocument();
        $doc->formatOutput = false;
        $doc->preserveWhiteSpace = true;

        // Load the SVG
        $loaded = $doc->loadXML($content, LIBXML_NONET | LIBXML_NOBLANKS);

        if (!$loaded) {
            // Try with HTML parser for malformed SVGs
            $loaded = $doc->loadHTML(
                '<?xml encoding="UTF-8">' . $content,
                LIBXML_NONET | LIBXML_NOBLANKS | LIBXML_HTML_NOIMPLIED,
            );
        }

        libxml_clear_errors();
        libxml_use_internal_errors(false);

        if (!$loaded) {
            return false;
        }

        // Check if root element is SVG
        $svg_elements = $doc->getElementsByTagName("svg");

        return $svg_elements->length > 0;
    }

    /**
     * Sanitize SVG content
     *
     * @param string $content SVG content
     * @return string|false Sanitized content or false on failure
     */
    private function sanitize_svg(string $content)
    {
        // First pass: remove dangerous patterns
        foreach ($this->dangerous_patterns as $pattern) {
            $content = preg_replace($pattern, "", $content);
            if ($content === null) {
                return false;
            }
        }

        // Parse the SVG
        libxml_use_internal_errors(true);

        $doc = new DOMDocument();
        $doc->formatOutput = false;
        $doc->preserveWhiteSpace = true;

        // Disable external entities for security
        $loaded = $doc->loadXML(
            $content,
            LIBXML_NONET | LIBXML_NOBLANKS | LIBXML_NOENT,
        );

        if (!$loaded) {
            libxml_clear_errors();
            libxml_use_internal_errors(false);
            return false;
        }

        libxml_clear_errors();
        libxml_use_internal_errors(false);

        // Get all elements
        $all_elements = $doc->getElementsByTagName("*");

        // Collect elements to remove (can't modify during iteration)
        $elements_to_remove = [];

        foreach ($all_elements as $element) {
            /** @var DOMElement $element */
            $tag_name = strtolower($element->tagName);

            // Remove disallowed elements
            if (!in_array($tag_name, $this->allowed_elements, true)) {
                $elements_to_remove[] = $element;
                continue;
            }

            // Remove disallowed attributes
            $attributes_to_remove = [];

            foreach ($element->attributes as $attr) {
                /** @var DOMAttr $attr */
                $attr_name = strtolower($attr->name);

                // Check if attribute is allowed
                if (!in_array($attr_name, $this->allowed_attributes, true)) {
                    // Allow data-* attributes (except data:)
                    if (strpos($attr_name, "data-") !== 0) {
                        $attributes_to_remove[] = $attr->name;
                        continue;
                    }
                }

                // Check attribute value for dangerous content
                $attr_value = $attr->value;

                // Remove javascript: and similar
                if (
                    preg_match(
                        "/^\s*(javascript|vbscript|data):/i",
                        $attr_value,
                    )
                ) {
                    $attributes_to_remove[] = $attr->name;
                    continue;
                }

                // Check for event handlers in attribute values
                if (preg_match("/on\w+\s*=/i", $attr_value)) {
                    $attributes_to_remove[] = $attr->name;
                }
            }

            foreach ($attributes_to_remove as $attr_name) {
                $element->removeAttribute($attr_name);
            }
        }

        // Remove collected elements
        foreach ($elements_to_remove as $element) {
            if ($element->parentNode) {
                $element->parentNode->removeChild($element);
            }
        }

        // Return sanitized SVG
        $svg_nodes = $doc->getElementsByTagName("svg");

        if ($svg_nodes->length === 0) {
            return false;
        }

        // Save only the SVG content
        $sanitized = $doc->saveXML($svg_nodes->item(0));

        // Remove XML declaration if present
        $sanitized = preg_replace("/^<\?xml[^>]*\?>\s*/i", "", $sanitized);

        return $sanitized;
    }

    /**
     * Add SVG preview support in media library
     *
     * @param array<string, mixed> $response Attachment response
     * @param WP_Post $attachment Attachment post
     * @param array<int>|false $meta Attachment meta
     * @return array<string, mixed>
     */
    public function add_svg_preview(
        array $response,
        WP_Post $attachment,
        $meta,
    ): array {
        if ($response["mime"] !== "image/svg+xml") {
            return $response;
        }

        $svg_url = wp_get_attachment_url($attachment->ID);

        if (!$svg_url) {
            return $response;
        }

        // Set dimensions if not already set
        if (empty($response["width"]) || empty($response["height"])) {
            $dimensions = $this->get_svg_dimensions($attachment->ID);

            if ($dimensions) {
                $response["width"] = $dimensions["width"];
                $response["height"] = $dimensions["height"];
            } else {
                // Default dimensions for display
                $response["width"] = 200;
                $response["height"] = 200;
            }
        }

        // Set image sizes for media library grid
        $response["sizes"] = [
            "full" => [
                "url" => $svg_url,
                "width" => $response["width"],
                "height" => $response["height"],
                "orientation" =>
                    $response["width"] > $response["height"]
                        ? "landscape"
                        : "portrait",
            ],
            "thumbnail" => [
                "url" => $svg_url,
                "width" => 150,
                "height" => 150,
                "orientation" => "portrait",
            ],
            "medium" => [
                "url" => $svg_url,
                "width" => 300,
                "height" => 300,
                "orientation" => "portrait",
            ],
        ];

        return $response;
    }

    /**
     * Get SVG dimensions
     *
     * @param int $attachment_id Attachment ID
     * @return array<string, int>|null
     */
    private function get_svg_dimensions(int $attachment_id): ?array
    {
        $file = get_attached_file($attachment_id);

        if (!$file || !file_exists($file)) {
            return null;
        }

        $svg_content = file_get_contents($file);

        if ($svg_content === false) {
            return null;
        }

        // Try to extract dimensions from SVG
        libxml_use_internal_errors(true);

        $doc = new DOMDocument();
        $loaded = $doc->loadXML($svg_content, LIBXML_NONET);

        libxml_clear_errors();
        libxml_use_internal_errors(false);

        if (!$loaded) {
            return null;
        }

        $svg = $doc->getElementsByTagName("svg")->item(0);

        if (!$svg) {
            return null;
        }

        /** @var DOMElement $svg */
        $width = $svg->getAttribute("width");
        $height = $svg->getAttribute("height");

        // Try viewBox if width/height not available
        if (empty($width) || empty($height)) {
            $viewbox = $svg->getAttribute("viewBox");

            if ($viewbox) {
                $parts = preg_split("/[\s,]+/", trim($viewbox));

                if (count($parts) >= 4) {
                    $width = $parts[2];
                    $height = $parts[3];
                }
            }
        }

        // Parse numeric values
        $width = (int) preg_replace("/[^0-9.]/", "", $width);
        $height = (int) preg_replace("/[^0-9.]/", "", $height);

        if ($width > 0 && $height > 0) {
            return [
                "width" => $width,
                "height" => $height,
            ];
        }

        return null;
    }

    /**
     * Add SVG admin styles for media library
     *
     * @return void
     */
    public function svg_admin_styles(): void
    {
        echo '<style>
            .attachment-preview .thumbnail img[src$=".svg"],
            .attachment-preview .thumbnail img[src$=".svgz"],
            .media-frame-content img[src$=".svg"],
            .media-frame-content img[src$=".svgz"] {
                width: 100%;
                height: auto;
                max-width: 100%;
            }

            .attachment.type-image.subtype-svg .thumbnail {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .attachment.type-image.subtype-svg .thumbnail img {
                max-width: 80%;
                max-height: 80%;
            }

            .media-modal .thumbnail img[src$=".svg"],
            .media-modal .thumbnail img[src$=".svgz"] {
                width: 100%;
                height: auto;
            }
        </style>';
    }

    /**
     * Check if SVG uploads are enabled
     *
     * @return bool
     */
    public static function is_enabled(): bool
    {
        if (!function_exists("cdg_core")) {
            return false;
        }

        return (bool) cdg_core()->get_setting("enable_svg_uploads");
    }
}
