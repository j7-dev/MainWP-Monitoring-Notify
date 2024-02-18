<?php

declare (strict_types = 1);

namespace J7\MainWP_Monitoring_Notify_Extension\Utils;

use J7\MainWP_Monitoring_Notify_Extension\Bootstrap;

abstract class Functions
{

    public static function get_http_status_code(string $url)
    {
        // 初始化cURL會話
        $curl = curl_init($url);

        // 設置cURL選項
        curl_setopt($curl, CURLOPT_NOBODY, true); // 不下載body內容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // 返回結果為字符串，而非直接輸出
        curl_setopt($curl, CURLOPT_HEADER, true); // 啟用時會將頭文件的資訊作為數據流輸出
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); // 跟隨重定向

        // 執行cURL請求
        curl_exec($curl);

        // 獲取HTTP狀態碼
        $httpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // 等待 0.1 秒
        usleep(100000);
        return (string) $httpStatusCode;
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

    public static function get_site_message($site, string $http_response_code = "", )
    {
        $hide_healthy_sites = (bool) \get_option(Bootstrap::HIDE_HEALTHY_SITES_FIELD_NAME, '0');
        $site_name          = $site->name ?? '<抓不到網站名稱>';
        $msg                = "";
        // 如果是2或3開頭
        if (substr((string) $http_response_code, 0, 1) !== "2" && substr((string) $http_response_code, 0, 1) !== "3") {
            $http_response_code = empty($http_response_code) ? "<無法取得 http 狀態碼>" : $http_response_code;
            $msg .= "\n";
            $msg .= "🔴 {$http_response_code} - 網站 {$site_name} 狀態異常，";
            $msg .= "{$site->url} 請盡速確認或聯繫網站管理員\n\n";
        } else {
            if (!$hide_healthy_sites) {
                $msg .= "✅ {$http_response_code} - 網站 {$site_name} 正常運作中\n";
            }
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
            $msg .= "<無法獲取 $service 狀態>";
        }
        return $msg;
    }

    public static function getLoadColor($load)
    {
        if (empty($load)) {
            return '🔴 <無法取得 load average 資訊>';
        }
        $load = (float) $load;
        if ($load > 4) {
            return '🔴 ' . number_format($load, 2);
        } else if ($load > 2) {
            return '🟡 ' . number_format($load, 2);
        } else {
            return '🟢 ' . number_format($load, 2);
        }
    }

    public static function get_system_info()
    {
        $cpuUsage    = \exec("top -bn 1 | awk '/Cpu\(s\):/ {print $2 + $4}'");
        $cpuUsage    = empty($cpuUsage) ? "<無法取得 cpu 資訊>" : number_format((float) $cpuUsage, 2) . '%';
        $memoryUsage = \exec("free | awk '/Mem:/ {print $3/$2 * 100.0}'");
        $memoryUsage = empty($memoryUsage) ? "<無法取得 ram 資訊>" : number_format((float) $memoryUsage, 2) . '%';

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

        return $msg;
    }

    /**
     * 將字串分組，不超過 $max_string 為一組
     * 因為 LINE NOTIFY 有字數限制，每次最多發 1000 字
     * @return string[]
     */
    public static function split_string_to_array($string, $max_string)
    {
        $parts      = explode("\n", $string);
        $result     = [  ];
        $tempString = '';
        foreach ($parts as $part) {
            // 加上 \n 因為 explode 會移除它
            $partWithBreak = $part . "\n";

            // 檢查暫存字串長度加上當前部分的長度
            if (mb_strlen($tempString . $partWithBreak) <= $max_string) {
                // 如果總長度不超過 $max_string，則加到暫存字串
                $tempString .= $partWithBreak;
            } else {
                // 否則，將暫存字串添加到結果並重置暫存字串
                $result[  ] = $tempString;
                $tempString = $partWithBreak;
            }
        }

        // 確保添加最後的暫存字串如果它不為空
        if (!empty($tempString)) {
            $result[  ] = $tempString;
        }

        return $result;
    }

    public static function exec_crontab_task()
    {
        if (!\class_exists('KS\Line\LineNotify')) {
            echo 'KS\Line\LineNotify is not enabled';
            return;
        }
        $only_notify_when_site_offline = (bool) \get_option(Bootstrap::ONLY_NOTIFY_WHEN_SITE_OFFLINE_FIELD_NAME, '0');
        $show_system_info              = (bool) \get_option(Bootstrap::SHOW_SYSTEM_INFO_FIELD_NAME, '0');

        $sites = self::get_sites();
        $msg   = "";
        if ($show_system_info) {
            $msg .= self::get_system_info();
            $msg .= "\n\n";
        }
        $is_all_site_ok = true;
        foreach ($sites as $site) {
            $http_status_code = self::get_http_status_code($site->url) ?? '';
            $msg .= self::get_site_message($site, $http_status_code);
            $is_all_site_ok = ($http_status_code === '200') ? $is_all_site_ok : false;
        }

        if ($is_all_site_ok && $only_notify_when_site_offline) {
            echo 'All sites are online';
            return;
        }
        $line_token = \get_option(Bootstrap::LINE_TOKEN_FIELD_NAME, '');
        $ln         = new \KS\Line\LineNotify($line_token);
        $string_arr = self::split_string_to_array($msg, 800);

        $waiting_seconds = 3;

        foreach ($string_arr as $string) {
            $ln->send("\n\n" . $string);
            usleep($waiting_seconds * 1000000);
        }

    }

}
