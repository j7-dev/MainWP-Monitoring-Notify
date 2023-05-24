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

if (!defined('MAINWP_BRANDING_PLUGIN_FILE')) {
	define('MAINWP_BRANDING_PLUGIN_FILE', __FILE__);
}

class MainWP_Branding_Extension
{
	public static $instance = null;
	public $plugin_handle = 'mainwp-branding-extension';
	public $plugin_slug;
	protected $plugin_url;

	public function __construct()
	{

		$this->plugin_url  = plugin_dir_url(__FILE__);
		$this->plugin_slug = plugin_basename(__FILE__);

		add_action('admin_init', array(&$this, 'admin_init'));
		add_filter('plugin_row_meta', array(&$this, 'plugin_row_meta'), 10, 2);
		add_action('mainwp_delete_site', array(&$this, 'on_delete_site'), 10, 1);
		add_filter('mainwp_getsubpages_sites', array(&$this, 'managesites_subpage'), 10, 1);
		add_filter('mainwp_sync_extensions_options', array(&$this, 'mainwp_sync_extensions_options'), 10, 1);
		add_action('init', array(&$this, 'localization'));
		add_action('mainwp_applypluginsettings_mainwp-branding-extension', array(MainWP_Branding::get_instance(), 'mainwp_apply_plugin_settings'));
		MainWP_Branding_DB::get_instance()->install();
		MainWP_Branding::get_instance()->init();
	}

	static function get_instance()
	{

		if (null == self::$instance) {
			self::$instance = new MainWP_Branding_Extension();
		}

		return self::$instance;
	}

	public function localization()
	{
		load_plugin_textdomain('mainwp-branding-extension', false,  dirname(plugin_basename(__FILE__)) . '/languages/');
	}

	public function plugin_row_meta($plugin_meta, $plugin_file)
	{

		if ($this->plugin_slug != $plugin_file) {
			return $plugin_meta;
		}

		$slug     = basename($plugin_file, '.php');
		$api_data = get_option($slug . '_APIManAdder');
		if (!is_array($api_data) || !isset($api_data['activated_key']) || $api_data['activated_key'] != 'Activated' || !isset($api_data['api_key']) || empty($api_data['api_key'])) {
			return $plugin_meta;
		}

		$plugin_meta[] = '<a href="?do=checkUpgrade" title="Check for updates.">Check for updates now</a>';

		return $plugin_meta;
	}

	public function on_delete_site($website)
	{
		if ($website) {
			MainWP_Branding_DB::get_instance()->delete_branding($website->id);
		}
	}

	public function mainwp_sync_extensions_options($values = array())
	{
		$values['mainwp-branding-extension'] = array(
			'plugin_slug' => null,
		);
		return $values;
	}

	public function managesites_subpage($subPage)
	{
		$subPage[] = array(
			'title'            => __('White Label', 'mainwp'),
			'slug'             => 'WhiteLabel',
			'sitetab'          => true,
			'menu_hidden'      => true,
			'callback'         => array('MainWP_Branding', 'render'),
			'on_load_callback' => array('MainWP_Branding', 'on_load_page'),
		);
		return $subPage;
	}

	public function admin_init()
	{

		$output = MainWP_Branding::handle_settings_post();

		if (false !== $output) {
			$referer  = wp_get_referer();
			wp_redirect(add_query_arg(array('updated' => true), $referer));
			exit();
		}

		wp_enqueue_style('mainwp-branding-extension', $this->plugin_url . 'css/mainwp-branding.css');
		wp_enqueue_script('mainwp-branding-extension', $this->plugin_url . 'js/mainwp-branding.js', array(), '1.2');
	}
}


function mainwp_branding_extension_autoload($class_name)
{

	$allowedLoadingTypes = array('class', 'page');
	$class_name = str_replace('_', '-', strtolower($class_name));
	foreach ($allowedLoadingTypes as $allowedLoadingType) {
		$class_file = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . str_replace(basename(__FILE__), '', plugin_basename(__FILE__)) . $allowedLoadingType . DIRECTORY_SEPARATOR . $class_name . '.' . $allowedLoadingType . '.php';
		if (file_exists($class_file)) {
			require_once $class_file;
		}
	}
}

class MainWP_Branding_Extension_Activator
{
	protected $mainwpMainActivated = false;
	protected $childEnabled = false;
	protected $childKey = false;
	protected $childFile;
	protected $plugin_handle = 'mainwp-branding-extension';
	protected $product_id = 'MainWP Branding Extension';
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
		include_once 'class/mainwp-branding-db.class.php';
		include_once 'class/mainwp-branding.class.php';
	}

	public function activate_this_plugin()
	{
		$this->mainwpMainActivated = apply_filters('mainwp_activated_check', $this->mainwpMainActivated);
		$this->childEnabled = apply_filters('mainwp_extension_enabled_check', __FILE__);
		$this->childKey = $this->childEnabled['key'];
		if (function_exists('mainwp_current_user_can') && !mainwp_current_user_can('extension', 'mainwp-branding-extension')) {
			return;
		}
		new MainWP_Branding_Extension();
	}

	public function get_this_extension($pArray)
	{
		$pArray[] = array(
			'plugin'     				=> __FILE__,
			'api'        				=> $this->plugin_handle,
			'mainwp'     				=> true,
			'callback'   				=> array(&$this, 'settings'),
			'apiManager' 				=> true,
			'on_load_callback' => array('MainWP_Branding', 'on_load_page'),
		);

		return $pArray;
	}

	public function settings()
	{
		do_action('mainwp_pageheader_extensions', __FILE__);
		MainWP_Branding::render();
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

global $mainWPBrandingExtensionActivator;
$mainWPBrandingExtensionActivator = new MainWP_Branding_Extension_Activator();
