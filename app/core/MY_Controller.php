<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use CodeIgniter\libraries\REST_Controller;
use CodeIgniter\libraries\Redis;
use CodeIgniter\libraries\CConst;
use CodeIgniter\libraries\Snowflake;
use CodeIgniter\libraries\Oss;

class MY_Controller extends REST_Controller {
  const AUTH_PREFIX = "Bearer ";
  // 用户信息
  protected $user = false;

  // 不需要进行session校验的接口
  public static $noSession = [
    "user/login",
    "user/register",
    "common/sendSms",
  ];

  public function __construct() {
    parent::__construct();
    $this->redis = Redis::instance();
    $user = $this->redis->get($this->getSession());
    if ($user) {
      $this->user = json_decode($user);
      # 更新过期时间
      $ttl = (intval(getenv("TOKEN_EXPIRE_DAY")) * 24 * 3600);
      $this->redis->expire($this->getSession(), $ttl);
    }

    $directory = $this->router->directory;
    $class = $this->router->fetch_class();
    $method = $this->router->fetch_method();
    $router = $directory . "$class/$method";

    if (!$this->user && !in_array($router, self::$noSession)) {
      $this->error("无效的session", CConst::INVALID_SESSION);
    }

    $this->load->model('users');
    # 控制管理权限
    if (!in_array($router, self::$noSession) && $directory == "admin/" && !$this->users->isAdmin($this->user->user_id)) {
      $this->error("权限不足");
    }

    # load _models
  }

  public function buildParams($keys = [], $method = 'get', $dft = null, $throw = true) {
    $params = [];
    $method = strtolower($method);
    if (!in_array($method, ['get', 'post', 'put', 'delete'])) {
      throw new Exception("不支持的请求方式");
    }
    foreach ($keys as $key) {
      $value = $this->{$method}($key, true, $dft);
      if ($throw && !$value) {
        throw new Exception("$key required");
      }
      $params[$key] = $value;
    }

    return $params;
  }

  public function success($data = '') {
    $this->response([
      "status" => "success",
      "result" => $data,
    ]);
  }

  public function error($data = '', $code = 0) {
    $this->response([
      "status" => "error",
      "result" => [
        "error_code" => $code,
        "error_msg" => $data,
      ],
    ]);
  }

  public function getSession() {
    $headers = $this->input->request_headers();
    $authHeader = $headers['Authorization'] ?? self::AUTH_PREFIX;
    return substr($authHeader, strlen(self::AUTH_PREFIX));
  }

  public function getUuid($type = CConst::UUID_LABEL_USER) {
    $key = CConst::UUID_KEYS[$type] ?? '';
    if (!$key) {
      throw new Exception("uuid type invalid");
    }
    $now = time();
    $key = $key . ":$now";
    if ($this->redis->exists($key)) {
      $nowIndex = $this->redis->incr($key);
    }
    else {
      $nowIndex = 1;
      $this->redis->setex($key, 2, $nowIndex);
    }
    return date("YmdHis", $now) . $type . str_pad($nowIndex, 4, 0, STR_PAD_LEFT);
  }

  public function getSnowUuid() {
    $snowflake = new Snowflake(getenv("SNOW_FLAKE_MACHINE_ID"));
    return $snowflake->getId();
  }

  public function getUser($uuid) {
    $user = $this->users->getByUserid($uuid);
    return [
      'nickname' => $user->nickname ?? '',
      'user_id' => $user->user_id ?? '',
      'avatar' => Oss::getUrl($user->avatar ?? ''),
    ];
  }
}
