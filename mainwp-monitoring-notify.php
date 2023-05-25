<?php

/**
 * Plugin Name: MainWP Monitoring Notify
 * Plugin URI: https://mainwp.com
 * Description: The MainWP Monitoring Notify extension allows you to send notifications via Line Notify when your site goes offline.
 * Version: 1.0.0
 * Author: J7
 * Author URI: https://github.com/j7-dev
 * Documentation URI:
 */
require __DIR__ . '/vendor/autoload.php';

class MainWP_Monitoring_Notify_Extension
{
	public static $childKey = false;
	public static $token = '3qXsuC4zQ7V0BdlamXpDBHZeeZ8AQOBLQdEgoOtoHwx';


	public function __construct()
	{
		add_action('init', [$this, 'initialize']);
		add_filter('mainwp-getextensions', [$this, 'get_this_extension']);
		add_action('mainwp_after_notice_sites_uptime_monitoring_individual', [$this, 'handle_offline_site']);
		// add_action('wp_head',  [$this, 'test']);
	}

	public function initialize()
	{
		$mainWPActivated = apply_filters('mainwp-activated-check', false);

		if ($mainWPActivated !== false) {
			self::activate_this_plugin();
		} else {
			add_action('mainwp-activated', 'activate_this_plugin');
		}
	}

	public function test()
	{
		$websites = MainWP\Dashboard\MainWP_DB::instance()->query(MainWP\Dashboard\MainWP_DB::instance()->get_sql_websites_for_current_user());
		// $offlineSites = array_filter($websites, [$this, "filter_offline_site_callback"]);
		var_dump($websites);
	}

	public function get_this_extension($extensions)
	{
		$extensions[] = array(
			'plugin' => __FILE__,
			'callback' => [$this, 'plugin_extension_settings'],
			'apiManager' => false,
			'name' => 'Monitoring Notify',
		);
		return $extensions;
	}

	public function plugin_extension_settings()
	{
		do_action('mainwp-pageheader-extensions', __FILE__);
?>
		<h1>123156</h1>
<?php
		do_action('mainwp-pagefooter-extensions', __FILE__);
	}

	public function handle_offline_site($site)
	{


		$msg = '';
		if ($site->http_response_code == "200") {
			$msg .= "\n✅ 檢查所有網站都正常運作中\n";
		} else {
				$code = $site->http_response_code;
				$code_string = MainWP\Dashboard\MainWP_Utility::get_http_codes($code);
				if (!empty($code_string)) {
					$code .= ' - ' . $code_string;
				}
				$msg .= "\n⚠️ 偵測到網站異常" . $site->name  . '  🔴' . $code  . "\n";
				$msg .= "請確認並聯繫網站管理員\n";
				$msg .= $site->url . "\n\n";
		}

		$ln = new KS\Line\LineNotify(self::$token);
		$ln->send($msg);
	}

	public function filter_offline_site_callback($website)
	{
		//return $website->http_response_code !== "200";
		return true;
	}



	public static function activate_this_plugin()
	{
		global $childEnabled;
		$childEnabled = apply_filters('mainwp-extension-enabled-check', __FILE__);
		if (!$childEnabled) return;

		$childKey = $childEnabled['key'];

		//Code to initialize your plugin
	}
}

new MainWP_Monitoring_Notify_Extension();
