<?php

function get_http_status_code(string $url)
{
	$headers = get_headers($url);
	$status_line = $headers[0];
	preg_match('/\d{3}/', $status_line, $match);
	$status_code = $match[0];
	return $status_code;
}

function get_sites()
{
	global $wpdb;
	$table_name = $wpdb->prefix . 'mainwp_wp';

	$results = $wpdb->get_results("SELECT name, url FROM $table_name"); // 選取 name 和 url 欄位

	return $results;
}

function get_site_urls(array $sites)
{
	$site_urls = [];
	foreach ($sites as $site) {
		$site_urls[] = $site->url;
	}

	return $site_urls;
}

function get_token()
{
	if (class_exists('MainWP_Monitoring_Notify_Extension')) {
		$token = MainWP_Monitoring_Notify_Extension::get_instance()->line_token;
	} else {
		$token = get_option('mainwp_monitoring_notify_line_token', '');
	}

	return $token;
}

function get_only_notify_when_site_offline()
{
	if (class_exists('MainWP_Monitoring_Notify_Extension')) {
		$only_notify_when_site_offline = MainWP_Monitoring_Notify_Extension::get_instance()->only_notify_when_site_offline;
	} else {
		$only_notify_when_site_offline = (bool) get_option('mainwp_monitoring_notify_only_notify_when_site_offline', '0');
	}

	return $only_notify_when_site_offline;
}

function get_message(string $http_response_code, $site)
{
	$msg = "";
	if ($http_response_code === '200') {
		$msg .= "✅ {$http_response_code} - 網站 {$site->name} 正常運作中\n";
	} else {
		$msg .= "\n";
		$msg .= "🔴 {$http_response_code} - 網站 {$site->name} 狀態異常\n";
		$msg .= "{$site->url} 請盡速確認或聯繫網站管理員\n";
		$msg .= "\n";
	}
	return $msg;
}

function get_service_status(string $service)
{
	// 执行 "service $service status" 命令并捕获输出
	$service_status = shell_exec('service ' . $service . ' status');

	// 使用正则表达式从输出中提取Active状态
	$pattern = '/Active: (\w+)/';
	preg_match($pattern, $service_status, $matches);

	$msg = "";
	if (isset($matches[1])) {
		$activeStatus = $matches[1];
		$msg .= $service . " 狀態：$activeStatus";
	} else {
		$msg .= "無法獲取 $service 狀態";
	}
	return $msg;
}



function get_system_info()
{
	$cpuUsage = exec("top -bn 1 | awk '/Cpu\(s\):/ {print $2 + $4}'");
	$memoryUsage = exec("free | awk '/Mem:/ {print $3/$2 * 100.0}'");
	$loadAvg = sys_getloadavg();
	function getLoadColor($load)
	{
		if ($load > 80) {
			return '🔴 ' . $load;
		} else if ($load > 50) {
			return '🟡 ' . $load;
		} else {
			return '🟢 ' . $load;
		}
	}

	$msg = "";
	$msg .= "\n\n\n";
	$msg .= "CPU 使用率：$cpuUsage%";
	$msg .= "\n";
	$msg .= "RAM 使用率：$memoryUsage%";
	$msg .= "\n";
	$msg .= "Load Average：" . getLoadColor($loadAvg[0]) . " " . getLoadColor($loadAvg[1]) . " " . getLoadColor($loadAvg[2]) . "\n";
	$msg .= "\n";
	$msg .= get_service_status('nginx') . "\n";
	$msg .= get_service_status('mysql') . "\n";
	$msg .= get_service_status('php7.4-fpm') . "\n";
	$msg .= get_service_status('php8.2-fpm') . "\n";

	return $msg;
}

function exec_crontab_task()
{
	if (!class_exists('KS\Line\LineNotify')) {
		echo 'KS\Line\LineNotify is not enabled';
		return;
	}
	$sites = get_sites();
	$msg = "\n\n";
	$is_all_site_ok = true;
	foreach ($sites as $site) {
		$http_status_code = get_http_status_code($site->url);
		$msg .= get_message($http_status_code, $site);
		$is_all_site_ok = ($http_status_code === '200') ? $is_all_site_ok : false;
	}

	$msg .= get_system_info();


	$only_notify_when_site_offline = get_only_notify_when_site_offline();
	if ($is_all_site_ok && $only_notify_when_site_offline) {
		echo 'All sites are online';
		return;
	}
	$token = get_token();
	$ln = new KS\Line\LineNotify($token);
	$ln->send($msg);
}
