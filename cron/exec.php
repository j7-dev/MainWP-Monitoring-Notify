<?php
use J7\MainWP_Monitoring_Notify_Extension\Utils\Functions;

require_once __DIR__ . '/bootstrap.php';

Functions::exec_crontab_task();
