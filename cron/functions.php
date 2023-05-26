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

function exec_crontab_task()
{
	if (!class_exists('KS\Line\LineNotify')) return 'KS\Line\LineNotify is not enabled';
	$sites = get_sites();
	$msg = "\n\n";
	$is_all_site_ok = true;
	foreach ($sites as $site) {
		$http_status_code = get_http_status_code($site->url);
		$msg .= get_message($http_status_code, $site);
		$is_all_site_ok = ($http_status_code === '200') ? $is_all_site_ok : false;
	}
	$only_notify_when_site_offline = true;
	if ($is_all_site_ok && $only_notify_when_site_offline) return;
	$token = get_token();
	$ln = new KS\Line\LineNotify($token);
	$ln->send($msg);
}
