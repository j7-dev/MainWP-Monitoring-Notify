<?php

function get_http_status_code($url)
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

function get_site_urls()
{
	$sites = get_sites();
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


// $url = "https://test.yc-tech.co/";
// $status_code = get_http_status_code($url);

var_dump(get_token());
