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
	public static $instance = null;
	public static $childKey = false;
	public $line_token;
	public $plugin_handle = 'mainwp-monitoring-notify-extension';
	public $update_action = 'mainwp_monitoring_notify_update';
	public $plugin_slug;
	public $plugin_url;
	public static $ver;

	//3qXsuC4zQ7V0BdlamXpDBHZeeZ8AQOBLQdEgoOtoHwx
	public function __construct()
	{

		require __DIR__ . '/vendor/autoload.php';
		
		if ( ! function_exists( 'get_plugins' ) ) {
        		require_once ABSPATH . 'wp-admin/includes/plugin.php';
    		}
		$plugin_data = get_plugin_data(__FILE__);
		$this->ver = $plugin_data['Version'];
		$this->plugin_url  = plugin_dir_url(__FILE__);
		$this->plugin_slug = plugin_basename(__FILE__);

		$this->line_token = get_option('mainwp_monitoring_notify_line_token', '');


		add_action('admin_init', array(&$this, 'admin_init'));
		add_action('mainwp_after_notice_sites_uptime_monitoring_individual', [$this, 'handle_offline_site']);
		add_action('wp_ajax_' . $this->update_action, [$this, 'update_callback']);
		add_action('wp_ajax_nopriv_' . $this->update_action, [$this, 'update_callback']);
	}

	static function get_instance()
	{

		if (null == self::$instance) {
			self::$instance = new MainWP_Monitoring_Notify_Extension();
		}

		return self::$instance;
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

		$ln = new KS\Line\LineNotify(self::$line_token);
		$ln->send($msg);
	}



	public function admin_init()
	{
		$page = isset($_GET['page']) ? $_GET['page'] : '';
		if (stripos($page, "Mainwp-Monitoring-Notify") === false) return;

		wp_enqueue_script($this->plugin_handle, $this->plugin_url . 'assets/js/main.js', array('jquery'), $this->ver);

		$data = [
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce($this->plugin_handle),
			'action' => $this->update_action
		];
		wp_localize_script($this->plugin_handle, 'info', $data);
	}

	public function update_callback()
	{
		check_ajax_referer($this->plugin_handle, 'nonce');
		$mainwp_monitoring_notify_line_token = sanitize_text_field($_POST['mainwp_monitoring_notify_line_token']);
		if (!empty($mainwp_monitoring_notify_line_token)) {
			update_option('mainwp_monitoring_notify_line_token', $mainwp_monitoring_notify_line_token);
		}

		$res = array(
			'status' => 'success',
			'message' => '保存成功',
		);

		wp_send_json($res);

		die();
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

	public function __construct()
	{
		$this->includes();

		$this->childFile = __FILE__;


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
		MainWP_Monitoring_Notify_Settings::render_tabs();
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
			'software_version' => MainWP_Monitoring_Notify_Extension::$ver,
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
