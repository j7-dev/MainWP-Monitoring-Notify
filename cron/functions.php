<?php

function get_http_status_code($url)
{
	$headers = get_headers($url);
	$status_line = $headers[0];
	preg_match('/\d{3}/', $status_line, $match);
	$status_code = $match[0];
	return $status_code;
}


// $url = "https://test.yc-tech.co/";
// $status_code = get_http_status_code($url);
// var_dump($status_code === '200');
