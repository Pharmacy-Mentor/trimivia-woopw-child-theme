(function ($) {
  "use strict";

  function parseRawValue(rawValue) {
    if (!rawValue) {
      return [];
    }

    return rawValue
      .split(/\r?\n/)
      .map(function (line) {
        var parts = line.split("|");
        return {
          icon: (parts[0] || "").trim(),
          url: (parts[1] || "").trim(),
          label: (parts[2] || "").trim(),
        };
      })
      .filter(function (item) {
        return item.icon || item.url || item.label;
      });
  }

  function serializeItems($control) {
    var lines = [];

    $control.find(".trimvia-social-item").each(function () {
      var $item = $(this);
      var icon = ($item.find(".trimvia-social-icon").val() || "").trim();
      var url = ($item.find(".trimvia-social-link").val() || "").trim();
      var label = ($item.find(".trimvia-social-label").val() || "").trim();

      if (!icon || !url) {
        return;
      }

      lines.push([icon, url, label].join("|"));
    });

    $control.find(".trimvia-social-raw").val(lines.join("\n")).trigger("change");
  }

  function createItem($control, data) {
    var templateHtml = $control.find(".trimvia-social-template").html();
    if (!templateHtml) {
      return;
    }

    var $item = $(templateHtml.trim());

    if (data && data.icon) {
      $item.find(".trimvia-social-icon").val(data.icon);
    }
    if (data && data.url) {
      $item.find(".trimvia-social-link").val(data.url);
    }
    if (data && data.label) {
      $item.find(".trimvia-social-label").val(data.label);
    }

    $control.find(".trimvia-social-items").append($item);
  }

  function initControl($control) {
    var currentValue = $control.find(".trimvia-social-raw").val() || "";
    var items = parseRawValue(currentValue);

    if (items.length) {
      items.forEach(function (item) {
        createItem($control, item);
      });
    }

    $control.on("click", ".trimvia-social-add", function (event) {
      event.preventDefault();
      createItem($control, null);
      serializeItems($control);
    });

    $control.on("click", ".trimvia-social-remove", function (event) {
      event.preventDefault();
      $(this).closest(".trimvia-social-item").remove();
      serializeItems($control);
    });

    $control.on("change input", ".trimvia-social-icon, .trimvia-social-link, .trimvia-social-label", function () {
      serializeItems($control);
    });
  }

  function initAllControls() {
    $(".trimvia-social-repeater-control").each(function () {
      initControl($(this));
    });
  }

  $(initAllControls);
})(jQuery);
