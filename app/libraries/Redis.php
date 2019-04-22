<?php
namespace CodeIgniter\libraries;

use Predis\Client;
use Exception;
use RedisException;
use StaticInstanceTrait;

defined('BASEPATH') OR exit('No direct script access allowed');

class Redis {
  use StaticInstanceTrait;

  const MAX_RETRY = 3;
  const MAX_RETRY_WAIT = 3;

  protected $redis;
  protected $retry_forever = false;

  public function __construct() {
    $this->connect();
  }

  public function connect() {
    $ci = &get_instance();
    $ci->load->config('redis');

    if (is_array($ci->config->item('redis_default'))) {
      $config = $ci->config->item('redis_default');
    }
    else {
      // Original config style
      $config = array(
        'host' => $ci->config->item('redis_host'),
        'port' => $ci->config->item('redis_port'),
        'password' => $ci->config->item('redis_password'),
        'database' => $ci->config->item('redis_database'),
      );
    }
    if (!$this->redis) {
      $this->redis = new Client($config);
    }
  }

  public function setRetryForever() {
    $this->retry_forever = true;
  }

  public function setDb($db = 0) {
    $this->redis->select($db);
  }

  public function __call($name, $args)
  {
    $retry_count = $retry_wait = 0;
    do {
      try {
        return call_user_func_array([$this->redis, $name], $args);
      } catch (RedisException $re) {
        $retry_count += 1;
        try {
          $this->connect();
        } catch (Exception $e) {
          log_message("error", sprintf("redis connect error: %s, retry@%d", $e->getMessage(), $retry_count));
          sleep($retry_wait);
          $retry_wait = min(self::MAX_RETRY_WAIT, $retry_wait + 1);
        }
      }
    } while ($this->retry_forever or $retry_count < self::MAX_RETRY);
  }
}
