<?php

/**
 * Plugin Name: MainWP Monitoring Notify
 * Plugin URI: https://mainwp.com
 * Description: The MainWP Monitoring Notify extension allows you to send notifications via Line Notify when your site goes offline.
 * Version: 1.3.0
 * Author: J7
 * Author URI: https://github.com/j7-dev
 * Documentation URI:
 */

namespace J7\MainWP_Monitoring_Notify_Extension;

require_once __DIR__ . '/utils/index.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

class Bootstrap
{
    public static $instance = null;
    public static $childKey = false;
    public $plugin_handle   = 'mainwp-monitoring-notify-extension';
    public $update_action   = 'mainwp_monitoring_notify_update';

    const LINE_TOKEN_FIELD_NAME                    = Utils::SNAKE . '_line_token';
    const INTERVAL_IN_MINUTE_FIELD_NAME            = Utils::SNAKE . '_interval_in_minute';
    const ONLY_NOTIFY_WHEN_SITE_OFFLINE_FIELD_NAME = Utils::SNAKE . '_only_notify_when_site_offline';
    const HIDE_HEALTHY_SITES_FIELD_NAME            = Utils::SNAKE . '_hide_healthy_sites';
    const SHOW_SYSTEM_INFO_FIELD_NAME              = Utils::SNAKE . '_show_system_info';

    const RUN_TEST_ACTION = 'run_test';
    const CRON_ACTION     = 'mainwp_monitoring_notify_cron';

    public function __construct()
    {

        require __DIR__ . '/vendor/autoload.php';

        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        \add_action('admin_init', array(&$this, 'admin_init'));
        \add_action('wp_ajax_' . $this->update_action, [ $this, 'update_callback' ]);
        \add_action('wp_ajax_nopriv_' . $this->update_action, [ $this, 'update_callback' ]);
        \add_action('admin_post_' . self::RUN_TEST_ACTION, [ $this, self::RUN_TEST_ACTION . '_callback' ]);

        if (!wp_next_scheduled(self::CRON_ACTION)) {
            wp_schedule_event(time(), 'every_five_minutes', self::CRON_ACTION);
        }
        \add_action(self::CRON_ACTION, [ $this, self::CRON_ACTION . '_callback' ]);
        \add_filter('cron_schedules', [ $this, 'notify_cron_schedule' ]);
    }

    public static function get_instance()
    {

        if (null == self::$instance) {
            self::$instance = new Bootstrap();
        }

        return self::$instance;
    }

    public function handle_offline_site($site)
    {
        global $mainWPMonitoringNotifyExtensionActivator;
        $wp_cron_enabled = apply_filters(Utils::SNAKE . '_wp_cron_enabled', $mainWPMonitoringNotifyExtensionActivator->wp_cron_enabled);

        if (!$wp_cron_enabled) {
            return;
        }

        $msg = '';
        if ($site->http_response_code == "200") {
            $msg .= "\n✅ 檢查所有網站都正常運作中\n";
        } else {
            $code        = $site->http_response_code;
            $code_string = \MainWP\Dashboard\MainWP_Utility::get_http_codes($code);
            if (!empty($code_string)) {
                $code .= ' - ' . $code_string;
            }
            $msg .= "\n⚠️ 偵測到網站異常" . $site->name . '  🔴' . $code . "\n";
            $msg .= "請確認並聯繫網站管理員\n";
            $msg .= $site->url . "\n\n";
        }
        $line_token = \get_option(Bootstrap::LINE_TOKEN_FIELD_NAME, '');

        $ln = new \KS\Line\LineNotify($line_token);
        $ln->send($msg);
    }

    public function admin_init()
    {
        $page = isset($_GET[ 'page' ]) ? $_GET[ 'page' ] : '';
        if (stripos($page, "Mainwp-Monitoring-Notify") === false) {
            return;
        }

        wp_enqueue_script($this->plugin_handle, Utils::get_plugin_url() . '/assets/js/main.js', array('jquery'), Utils::get_plugin_ver());

        $data = [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce($this->plugin_handle),
            'action'   => $this->update_action,
         ];
        wp_localize_script($this->plugin_handle, 'info', $data);
    }

    public function update_callback()
    {
        \check_ajax_referer($this->plugin_handle, 'nonce');
        $line_token                    = \sanitize_text_field($_POST[ self::LINE_TOKEN_FIELD_NAME ]);
        $interval_in_minute            = \sanitize_text_field($_POST[ self::INTERVAL_IN_MINUTE_FIELD_NAME ]);
        $only_notify_when_site_offline = \sanitize_text_field($_POST[ self::ONLY_NOTIFY_WHEN_SITE_OFFLINE_FIELD_NAME ]);
        $hide_healthy_sites            = \sanitize_text_field($_POST[ self::HIDE_HEALTHY_SITES_FIELD_NAME ]);
        $show_system_info              = \sanitize_text_field($_POST[ self::SHOW_SYSTEM_INFO_FIELD_NAME ]);

        if (!empty($line_token)) {
            \update_option(self::LINE_TOKEN_FIELD_NAME, $line_token);
        }
        if ($interval_in_minute > 0) {
            \update_option(self::INTERVAL_IN_MINUTE_FIELD_NAME, $interval_in_minute);
        }
        \update_option(self::ONLY_NOTIFY_WHEN_SITE_OFFLINE_FIELD_NAME, $only_notify_when_site_offline);
        \update_option(self::HIDE_HEALTHY_SITES_FIELD_NAME, $hide_healthy_sites);
        \update_option(self::SHOW_SYSTEM_INFO_FIELD_NAME, $show_system_info);

        $res = array(
            'status'  => 'success',
            'message' => '保存成功',
            'data'    => [
                'line_token'                    => $line_token,
                'interval_in_minute'            => $interval_in_minute,
                'only_notify_when_site_offline' => $only_notify_when_site_offline,
                'hide_healthy_sites'            => $hide_healthy_sites,
                'show_system_info'              => $show_system_info,
             ],
        );

        \wp_send_json($res);

        die();
    }

    public function notify_cron_schedule($schedules)
    {
        $interval_in_minute                               = \get_option(self::INTERVAL_IN_MINUTE_FIELD_NAME, 5);
        $schedules[ self::INTERVAL_IN_MINUTE_FIELD_NAME ] = array(
            'interval' => $interval_in_minute * 60, // 時間以秒為單位，300秒等於5分鐘
            'display' => Utils::APP_NAME . '每 ' . $interval_in_minute . ' 分鐘檢查一次',
        );

        return $schedules;
    }

    public function mainwp_monitoring_notify_cron_callback()
    {
        Utils\Functions::exec_crontab_task();
    }
    public static function run_test_callback()
    {
        Utils\Functions::exec_crontab_task();
    }
}

class Activator
{
    protected $mainwpMainActivated = false;
    public $wp_cron_enabled        = true;
    protected $childEnabled        = false;
    protected $childKey            = false;
    protected $childFile;
    protected $plugin_handle = 'mainwp-monitoring-notify-extension';
    protected $product_id    = 'MainWP Monitoring Notify Extension';

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
        $this->childEnabled        = apply_filters('mainwp_extension_enabled_check', __FILE__);
        $this->childKey            = $this->childEnabled[ 'key' ];
        if (function_exists('mainwp_current_user_can') && !mainwp_current_user_can('extension', 'mainwp-monitoring-notify-extension')) {
            return;
        }
        new Bootstrap();

        $this->plugin_update_checker();
    }

    /**
     * wp plugin 更新檢查 update checker
     */
    public function plugin_update_checker(): void
    {
        $updateChecker = PucFactory::buildUpdateChecker(
            Utils::GITHUB_REPO,
            __FILE__,
            Utils::KEBAB
        );
        $updateChecker->setBranch('master');
        $updateChecker->getVcsApi()->enableReleaseAssets();
    }

    public function get_this_extension($pArray)
    {
        $pArray[  ] = array(
            'plugin'           => __FILE__,
            'api'              => $this->plugin_handle,
            'mainwp'           => true,
            'callback'         => array(&$this, 'settings'),
            'apiManager'       => true,
            'on_load_callback' => array(__NAMESPACE__ . '\Settings', 'on_load_page'),
            'name'             => 'Line Notify',
        );

        return $pArray;
    }

    public function settings()
    {
        do_action('mainwp_pageheader_extensions', __FILE__);
        Settings::render_tabs();
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
            'software_version' => Utils::get_plugin_ver(),
        );
        do_action('mainwp_activate_extension', $this->plugin_handle, $options);
    }

    public function deactivate()
    {
        do_action('mainwp_deactivate_extension', $this->plugin_handle);
    }
}

global $mainWPMonitoringNotifyExtensionActivator;
$mainWPMonitoringNotifyExtensionActivator = new Activator();
