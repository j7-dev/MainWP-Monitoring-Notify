<?php

declare (strict_types = 1);

namespace J7\MainWP_Monitoring_Notify_Extension;

require_once __DIR__ . '/functions.php';

abstract class Utils
{
    const APP_NAME    = 'MainWP Monitoring Notify Extension';
    const KEBAB       = 'mainwp-monitoring-notify-extension';
    const SNAKE       = 'MainWP_Monitoring_Notify_Extension';
    const TEXT_DOMAIN = Utils::SNAKE;

    const DEFAULT_IMAGE = 'http://1.gravatar.com/avatar/1c39955b5fe5ae1bf51a77642f052848?s=96&d=mm&r=g';

    const GITHUB_REPO = 'https://github.com/j7-dev/MainWP-Monitoring-Notify';

    // public static function get_plugin_dir(): string
    // {
    //     $plugin_dir = \untrailingslashit(\wp_normalize_path(\plugin_dir_path(__DIR__ . '../')));
    //     return $plugin_dir;
    // }

    // public static function get_plugin_url(): string
    // {
    //     $plugin_url = \untrailingslashit(\plugin_dir_url(Utils::get_plugin_dir() . '/plugin.php'));
    //     return $plugin_url;
    // }

    // public static function get_plugin_ver(): string
    // {
    //     $plugin_data = \get_plugin_data(Utils::get_plugin_dir() . '/plugin.php');
    //     $plugin_ver  = $plugin_data[ 'Version' ];
    //     return $plugin_ver;
    // }
}
