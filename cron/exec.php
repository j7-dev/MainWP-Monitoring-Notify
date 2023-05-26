<?php

require_once __DIR__ . '/bootstrap.php';

exec_crontab_task();
// $url = "https://test.yc-tech.co/";
// $status_code = get_http_status_code($url);
// $token = get_token();
// $ln = new KS\Line\LineNotify($token);
// $ln->send('[TEST] crontab 直接發送訊息');