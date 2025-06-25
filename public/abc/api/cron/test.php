<?php

/**
 * тестирование задания крон
 */

$file = 'cron/test.txt';
$str = 1;

$fp = fopen(ROOT_DIR.$file, 'w');
fwrite($fp,$str);
fclose($fp);

die();