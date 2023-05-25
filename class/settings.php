<?php

class MainWP_Monitoring_Notify_Settings
{
	public function __construct()
	{
	}

	public static function render_form()
	{
?>
		<div class="ui segment">
			<form method="post" enctype="multipart/form-data" id="mainwp-monitoring-notify-settings-page-form" class="ui form">
				<?php self::render_fields(); ?>
				<div class="mainwp-form-footer">
					<div class="ui divider"></div>
					<button id="monitoring_notify_submit_btn" class="ui big green button"><?php _e('Save Settings', 'mainwp-monitoring-notify-extension'); ?></button>
				</div>
				<div id="response_msg" style="margin-top:2rem"></div>

			</form>
		</div>
	<?php
	}

	public static function render_fields()
	{
		$line_token = MainWP_Monitoring_Notify_Extension::get_instance()->line_token;
		$base_url = MainWP_Monitoring_Notify_Extension::get_instance()->plugin_url;
	?>
		<div class="ui grid field">
			<label class="six wide column middle aligned"><?php _e('Line Notify Token', 'mainwp-monitoring-notify-extension'); ?></label>
			<div class="ten wide column" data-tooltip="<?php esc_attr_e('Enter your Line notify token', 'mainwp-monitoring-notify-extension'); ?>" data-inverted="" data-position="top left">
				<input type="text" name="mainwp_monitoring_notify_line_token" id="mainwp_monitoring_notify_line_token" value="<?= $line_token ?>" />
			</div>
		</div>
		<div class="ui grid field">
			<label class="six wide column middle aligned"></label>
			<div class="ten wide column">
				<ol>
					<li style="color: #999;">請先前往 <a href="https://notify-bot.line.me/zh_TW/" target="_blank">LINE Notify</a> 並登入你的 LINE</li>
					<li style="color: #999;">
						<p>
							點擊 <code>Generate token</code> 然後選擇想要通知的聊天室</p>
						<p><a href="<?= $base_url . '/assets/image/choose_chatroom.png' ?>" target="_blank"><img style="width:10rem;" src="<?= $base_url . '/assets/image/choose_chatroom.png' ?>" /></a></p>
					</li>
					<li style="color: #999;">
						<p>
							複製 <code>token</code> 然後貼到這邊就完成了 🎉🎉🎉</p>
						<p><a href="<?= $base_url . '/assets/image/get_token.png' ?>" target="_blank"><img style="width:10rem;" src="<?= $base_url . '/assets/image/get_token.png' ?>" /></a></p>
					</li>
				</ol>
			</div>
		</div>

<?php

	}

	public static function on_load_page()
	{
	}
}
