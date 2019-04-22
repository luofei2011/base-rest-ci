<?php
/**
 * @file worker.php
 * @url https://github.com/resque/php-resque
 * @wiki https://icewing.cc/post/background-jobs-and-phpresque-4.html
 * @auther luofei
 * php-resque队列消费者
 */

/**
 * USAGE:
 * QUEUE=default/* php worker.php
 *
 * KILL:
 * - stop: kill -USR2 PID
 * - continue: kill -CONT PID
 */

define('FCPATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

$dir = FCPATH . "app/jobs";
$jobs = glob($dir . "/*.php");

foreach ($jobs as $job) {
  require($job);
}

require FCPATH . "vendor/resque/php-resque/bin/resque";
