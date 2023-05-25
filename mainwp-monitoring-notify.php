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

class MainWP_Monitoring_Notify_Extension
{
	public static $childKey = false;
	public static $token = '3qXsuC4zQ7V0BdlamXpDBHZeeZ8AQOBLQdEgoOtoHwx';
	public $plugin_handle = 'mainwp-monitoring-notify-extension';
	public $plugin_slug;
	protected $plugin_url;

	public function __construct()
	{
		require __DIR__ . '/vendor/autoload.php';

		$this->plugin_url  = plugin_dir_url(__FILE__);
		$this->plugin_slug = plugin_basename(__FILE__);
		add_action('mainwp_after_notice_sites_uptime_monitoring_individual', [$this, 'handle_offline_site']);
		// add_action('wp_head',  [$this, 'test']);
	}


	public function test()
	{
		$websites = MainWP\Dashboard\MainWP_DB::instance()->query(MainWP\Dashboard\MainWP_DB::instance()->get_sql_websites_for_current_user());
		// $offlineSites = array_filter($websites, [$this, "filter_offline_site_callback"]);
		var_dump($websites);
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



	public function admin_init()
	{
		wp_enqueue_script('mainwp-monitoring-notify-extension', $this->plugin_url . 'js/mainwp-monitoring-notify.js', array(), '1.2');
	}
}




class MainWP_Monitoring_Notify_Extension_Activator
{
	protected $mainwpMainActivated = false;
	protected $childEnabled = false;
	protected $childKey = false;
	protected $childFile;
	protected $plugin_handle = 'mainwp-monitoring-notify-extension';
	protected $product_id = 'MainWP Monitoring Notify Extension';
	protected $software_version = '4.1.3';

	public function __construct()
	{

		$this->childFile = __FILE__;

		$this->includes();
		register_activation_hook(__FILE__, array($this, 'activate'));
		register_deactivation_hook(__FILE__, array($this, 'deactivate'));

		add_filter('mainwp_getextensions', array(&$this, 'get_this_extension'));
		$this->mainwpMainActivated = apply_filters('mainwp_activated_check', false);

		if (false !== $this->mainwpMainActivated) {
			$this->activate_this_plugin();
		} else {
			add_action('mainwp_activated', array(&$this, 'activate_this_plugin'));
		}
		add_action('admin_notices', array(&$this, 'mainwp_error_notice'));
	}

	public function includes()
	{
		include_once 'class/settings.php';
	}

	public function activate_this_plugin()
	{
		$this->mainwpMainActivated = apply_filters('mainwp_activated_check', $this->mainwpMainActivated);
		$this->childEnabled = apply_filters('mainwp_extension_enabled_check', __FILE__);
		$this->childKey = $this->childEnabled['key'];
		if (function_exists('mainwp_current_user_can') && !mainwp_current_user_can('extension', 'mainwp-monitoring-notify-extension')) {
			return;
		}
		new MainWP_Monitoring_Notify_Extension();
	}

	public function get_this_extension($pArray)
	{
		$pArray[] = array(
			'plugin'     				=> __FILE__,
			'api'        				=> $this->plugin_handle,
			'mainwp'     				=> true,
			'callback'   				=> array(&$this, 'settings'),
			'apiManager' 				=> true,
			'on_load_callback' => array('MainWP_Monitoring_Notify_Settings', 'on_load_page'),
			'name' => 'Line Notify'
		);

		return $pArray;
	}

	public function settings()
	{
		do_action('mainwp_pageheader_extensions', __FILE__);
		MainWP_Monitoring_Notify_Settings::render_form();
		do_action('mainwp_pagefooter_extensions', __FILE__);
	}

	public function mainwp_error_notice()
	{
		global $current_screen;
		if ($current_screen->parent_base == 'plugins' && $this->mainwpMainActivated == false) {
			echo '<div class="error"><p>MainWP White Label Extension ' . __('requires <a href="https://mainwp.com/" target="_blank">MainWP Dashboard plugin</a> to be activated in order to work. Please install and activate <a href="https://mainwp.com/" target="_blank">MainWP Dashboard plugin</a> first.') . '</p></div>';
		}
	}

	public function get_child_key()
	{
		return $this->childKey;
	}

	public function get_child_file()
	{
		return $this->childFile;
	}

	public function activate()
	{
		$options = array(
			'product_id'       => $this->product_id,
			'software_version' => $this->software_version,
		);
		do_action('mainwp_activate_extension', $this->plugin_handle, $options);
	}

	public function deactivate()
	{
		do_action('mainwp_deactivate_extension', $this->plugin_handle);
	}
}

global $mainWPMonitoringNotifyExtensionActivator;
$mainWPMonitoringNotifyExtensionActivator = new MainWP_Monitoring_Notify_Extension_Activator();
