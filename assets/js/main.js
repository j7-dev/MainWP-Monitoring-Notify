(function ($) {
  init();

  function init() {
    waitRendered("#monitoring_notify_submit_btn", function () {
      const saveBtn = $(this);

      saveBtn.click(function (e) {
        e.preventDefault();
        e.stopPropagation();
        saveBtn.addClass("loading");
        updateSettings();
      });
    });
  }

  function updateSettings() {
    const saveBtn = $("#monitoring_notify_submit_btn");
    const responseMsg = $("#response_msg");
    const data = {
      action: info.action,
      nonce: info.nonce,
      mainwp_monitoring_notify_line_token: $(
        "#mainwp_monitoring_notify_line_token"
      ).val(),
      mainwp_monitoring_notify_interval_in_minute: $(
        "#mainwp_monitoring_notify_interval_in_minute"
      ).val(),
      mainwp_monitoring_notify_only_notify_when_site_offline: $(
        "#mainwp_monitoring_notify_only_notify_when_site_offline"
      ).is(":checked")
        ? 1
        : 0,
      mainwp_monitoring_notify_hide_healthy_sites: $(
        "#mainwp_monitoring_notify_hide_healthy_sites"
      ).is(":checked")
        ? 1
        : 0,
      mainwp_monitoring_notify_show_system_info: $(
        "#mainwp_monitoring_notify_show_system_info"
      ).is(":checked")
        ? 1
        : 0,
    };
    $.post(info?.ajax_url, data, function (response) {
      saveBtn.removeClass("loading");
      if (response?.status === "success") {
        responseMsg.html(renderSuccessMessage());
      } else {
        responseMsg.html(renderErrorMessage());
        console.log(response);
      }
    });
  }

  function waitRendered(selector, callback) {
    const interval = setInterval(function () {
      if ($(selector).length > 0) {
        callback.bind($(selector))();
        clearInterval(interval);
      }
    }, 300);
  }

  function renderSuccessMessage(message = "保存成功") {
    return `<div class="ui green check icon message">${message}</div>`;
  }
  function renderErrorMessage(message = "保存失敗，請稍後再試或洽管理員") {
    return `<div class="ui red close icon message">${message}</div>`;
  }
})(jQuery);
