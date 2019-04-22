<?php
namespace CodeIgniter\libraries;

use Resque;

class Queue {
  const JOB_CLASS_PREFIX = "JOB_";

  /**
   * 入队列
   * @param $jobName 任务名称 - 对应类名
   * @param $params 参数
   * @param $queue 队列名称
   */
  public static function enqueue($jobName, $params = [], $queue = "default") {
    # TODO runFunction()
    self::setBackend();
    return Resque::enqueue($queue, self::JOB_CLASS_PREFIX . $jobName, $params);
  }

  /**
   * 出队列
   */
  public static function dequeue() {
  }

  /**
   * 获取redis信息
   */
  private static function setBackend() {
    $ci = &get_instance();
    $ci->load->config('redis');
    $config = $ci->config->item('redis_default');
    $protocol = $config['host'] . ":" . $config['port'];
    Resque::setBackend($protocol);
  }
}
