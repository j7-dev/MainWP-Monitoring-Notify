<?php

declare (strict_types = 1);

namespace J7\MainWP_Monitoring_Notify_Extension\Utils;

use J7\MainWP_Monitoring_Notify_Extension\Bootstrap;

abstract class Functions
{

    public static function get_http_status_code(string $url)
    {
        $headers     = \get_headers($url);
        $status_line = $headers[ 0 ] ?? '';
        \preg_match('/\d{3}/', $status_line, $match);
        $status_code = $match[ 0 ] ?? '';

        usleep(100000);
        return $status_code;
    }

    public static function get_sites()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mainwp_wp';

        $results = $wpdb->get_results("SELECT name, url FROM $table_name"); // 選取 name 和 url 欄位

        return $results;
    }

    public static function get_site_urls(array $sites)
    {
        $site_urls = [  ];
        foreach ($sites as $site) {
            $site_urls[  ] = $site->url;
        }

        return $site_urls;
    }

    public static function get_token()
    {
        if (\class_exists('J7\MainWP_Monitoring_Notify_Extension\Bootstrap')) {
            $token = Bootstrap::get_instance()->line_token;
        } else {
            $token = \get_option('mainwp_monitoring_notify_line_token', '');
        }

        return $token;
    }

    public static function get_only_notify_when_site_offline()
    {
        if (\class_exists('J7\MainWP_Monitoring_Notify_Extension\Bootstrap')) {
            $only_notify_when_site_offline = Bootstrap::get_instance()->only_notify_when_site_offline;
        } else {
            $only_notify_when_site_offline = (bool) \get_option('mainwp_monitoring_notify_only_notify_when_site_offline', '0');
        }

        return $only_notify_when_site_offline;
    }

    public static function get_message($site, string $http_response_code = "", )
    {
        $site_name = $site->name ?? '<抓不到網站名稱>';
        $msg       = "";
        // 如果是2或3開頭
        if (substr((string) $http_response_code, 0, 1) === "2" || substr((string) $http_response_code, 0, 1) === "3") {
            $msg .= "✅ {$http_response_code} - 網站 {$site_name} 正常運作中\n";
        } else {
            $http_response_code = empty($http_response_code) ? "<無法取得 http 狀態碼>" : $http_response_code;
            $msg .= "\n";
            $msg .= "🔴 {$http_response_code} - 網站 {$site_name} 狀態異常\n";
            $msg .= "{$site->url} 請盡速確認或聯繫網站管理員\n";
            $msg .= "\n";
        }
        return $msg;
    }

    public static function get_service_status(string $service)
    {
        // 执行 "service $service status" 命令并捕获输出
        $service_status = \shell_exec('/usr/sbin/service ' . $service . ' status') ?? '';

        // 使用正则表达式从输出中提取Active状态
        $pattern = '/Active: (\w+)/';
        \preg_match($pattern, $service_status, $matches);

        $msg = "";
        if (isset($matches[ 1 ])) {
            $activeStatus = $matches[ 1 ];
            $activeLabel  = ($activeStatus === 'active') ? '🟢 ' . $activeStatus : '🔴 ' . $activeStatus;
            $msg .= $service . " 狀態：$activeLabel";
        } else {
            $msg .= "無法獲取 $service 狀態";
        }
        return $msg;
    }

    public static function getLoadColor($load)
    {
        if ($load > 4) {
            return '🔴 ' . $load;
        } else if ($load > 2) {
            return '🟡 ' . $load;
        } else {
            return '🟢 ' . $load;
        }
    }

    public static function get_system_info()
    {
        $cpuUsage    = \exec("top -bn 1 | awk '/Cpu\(s\):/ {print $2 + $4}'");
        $memoryUsage = \exec("free | awk '/Mem:/ {print $3/$2 * 100.0}'");

        $msg = "";
        $msg .= "目前 CPU 使用率：$cpuUsage%";
        $msg .= "\n";
        $msg .= "目前 RAM 使用率：$memoryUsage%";
        $msg .= "\n";
        if (function_exists('sys_getloadavg')) {
            $loadAvg = \sys_getloadavg();
            $msg .= "Load Average：" . self::getLoadColor($loadAvg[ 0 ]) . " " . self::getLoadColor($loadAvg[ 1 ]) . " " . self::getLoadColor($loadAvg[ 2 ]) . "\n";
        }

        $msg .= "\n";
        $msg .= self::get_service_status('nginx') . "\n";
        $msg .= self::get_service_status('mysql') . "\n";
        // $msg .= self::get_service_status('php7.4-fpm') . "\n";
        // $msg .= self::get_service_status('php8.2-fpm') . "\n";

        return $msg;
    }

    public static function exec_crontab_task()
    {
        if (!\class_exists('KS\Line\LineNotify')) {
            echo 'KS\Line\LineNotify is not enabled';
            return;
        }
        $sites = self::get_sites();
        $msg   = "\n\n";
        $msg .= self::get_system_info();
        $msg .= "\n\n";
        $is_all_site_ok = true;
        foreach ($sites as $site) {
            $http_status_code = self::get_http_status_code($site->url) ?? '';
            $msg .= self::get_message($site, $http_status_code);
            $is_all_site_ok = ($http_status_code === '200') ? $is_all_site_ok : false;
        }

        $only_notify_when_site_offline = self::get_only_notify_when_site_offline();
        if ($is_all_site_ok && $only_notify_when_site_offline) {
            echo 'All sites are online';
            return;
        }
        $token = self::get_token();
        $ln    = new \KS\Line\LineNotify($token);
        $ln->send($msg);
    }

}
