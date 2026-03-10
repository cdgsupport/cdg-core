/**
 * CDG Core Admin Scripts
 */

(function ($) {
  "use strict";

  $(document).ready(function () {
    // Toggle sub-options visibility based on parent checkbox

    // Documentation toggle
    $('input[name="enable_documentation"]')
      .on("change", function () {
        $(this)
          .closest("td")
          .find('.cdg-sub-options, div[style*="margin"]')
          .toggle(this.checked);
      })
      .trigger("change");

    // CPT Widgets toggle
    $('input[name="enable_cpt_widgets"]')
      .on("change", function () {
        $(this)
          .closest("td")
          .find('.cdg-sub-options, div[style*="margin"]')
          .toggle(this.checked);
      })
      .trigger("change");

    // Enable limited input when limited radio is selected
    $('input[name="post_revisions_mode"]')
      .on("change", function () {
        var limitInput = $('input[name="post_revisions_limit"]');
        limitInput.prop("disabled", this.value !== "limited");
      })
      .filter(":checked")
      .trigger("change");

    // Toggle SVG admin-only option visibility
    $('input[name="enable_svg_uploads"]')
      .on("change", function () {
        var adminOnlyRow = $('input[name="svg_admin_only"]').closest("tr");
        if (this.checked) {
          adminOnlyRow.show();
        } else {
          adminOnlyRow.hide();
        }
      })
      .trigger("change");

    // Toggle Font admin-only option visibility
    $('input[name="enable_font_uploads"]')
      .on("change", function () {
        var adminOnlyRow = $('input[name="font_admin_only"]').closest("tr");
        if (this.checked) {
          adminOnlyRow.show();
        } else {
          adminOnlyRow.hide();
        }
      })
      .trigger("change");

    // Toggle Lottie admin-only option visibility
    $('input[name="enable_lottie_uploads"]')
      .on("change", function () {
        var adminOnlyRow = $('input[name="lottie_admin_only"]').closest("tr");
        if (this.checked) {
          adminOnlyRow.show();
        } else {
          adminOnlyRow.hide();
        }
      })
      .trigger("change");

    // Toggle theme color custom field visibility
    $('input[name="theme_color_mode"]')
      .on("change", function () {
        var customRow = $('input[name="theme_color_hex"]').closest("tr");
        customRow.toggle(
          $('input[name="theme_color_mode"]:checked').val() === "custom"
        );
      })
      .filter(":checked")
      .trigger("change");

    // Live preview of theme color swatch
    $('input[name="theme_color_hex"]').on("input", function () {
      var val = $(this).val();
      if (/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/.test(val)) {
        $(this).siblings(".cdg-color-preview").css("background-color", val);
      }
    });
  });
})(jQuery);
