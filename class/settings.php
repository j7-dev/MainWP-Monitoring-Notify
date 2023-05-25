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
					<input type="hidden" name="monitoring_notify_submit_nonce" value="<?php echo wp_create_nonce('monitoring_notify_nonce'); ?>" />
					<input type="submit" name="submit" id="monitoring_notify_submit_btn" class="ui big green button" value="<?php esc_attr_e('Save Settings', 'mainwp-monitoring-notify-extension'); ?>" />
					<input type="button" name="mwp_monitoring_notify_reset_btn" id="mwp_monitoring_notify_reset_btn" class="ui big button" value="<?php esc_attr_e('Reset Settings', 'mainwp-monitoring-notify-extension'); ?>" />
				</div>
			</form>
		</div>
	<?php
	}

	public static function render_fields()
	{
	?>
		<div class="ui grid field">
			<label class="six wide column middle aligned"><?php _e('Line Notify Token', 'mainwp-monitoring-notify-extension'); ?></label>
			<div class="ten wide column" data-tooltip="<?php esc_attr_e('Enter your Line notify token', 'mainwp-monitoring-notify-extension'); ?>" data-inverted="" data-position="top left">
				<input type="text" name="mainwp_notify_line_token" id="mainwp_notify_line_token" value="" />
			</div>
		</div>

<?php

	}

	public static function on_load_page()
	{
	}
}
