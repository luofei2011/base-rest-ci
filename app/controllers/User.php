<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use CodeIgniter\libraries\Utils;
use CodeIgniter\libraries\CConst;
use CodeIgniter\libraries\Redis;
use CodeIgniter\models\_models\User as UserModel;

require_once APPPATH . "third_party/wxapp_aes/wxBizDataCrypt.php";

class User extends MY_Controller {
  public function __construct() {
    parent::__construct();
  }

  public function login_post() {
    $code = $this->post('code');
    $res = $this->code2session($code);
    $user = UserModel::where(['openid' => $res->openid])->first();
    if (!$user) {
      $this->error("用户未注册", CConst::USER_NOT_EXISTS);
    }
    $token = md5($res->session_key);
    Redis::instance()->set($token, json_encode($user));
    $ttl = (intval(getenv("TOKEN_EXPIRE_DAY")) * 24 * 3600);
    Redis::instance()->expire($token, $ttl);
    $user->token = $token;
    $this->success($user);
  }

  public function view_get() {
    $this->success($this->user);
  }

  /**
   * 注册
   */
  public function register_post() {
    $params = $this->buildParams(['iv', 'encrypted', 'code'], 'post', null);
    $session = $this->code2session($params['code']);
    $appid = getenv('WX_APPID');

    $pc = new WXBizDataCrypt($appid, $session->session_key);
    $errCode = $pc->decryptData($params['encrypted'], $params['iv'], $data);

    if ($errCode == 0) {
      $user = json_decode($data);
      $userData = [
        "user_id" => $this->getUuid(),
        "nickname" => $user->nickName,
        "openid" => $user->openId,
        "gender" => $user->gender,
        "avatar" => $user->avatarUrl,
        "country" => $user->country,
        "province" => $user->province,
        "city" => $user->city,
        "create_time" => time(),
      ];
      UserModel::insert($userData);
      $this->success($userData);
    }
    else {
      $this->error("注册失败", $errCode);
    }
  }

  private function code2session($code) {
    $appid = getenv("WX_APPID");
    $appsecret = getenv("WX_APPSECRET");
    $res = Utils::request("https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$appsecret}&js_code={$code}&grant_type=authorization_code");
    if (isset($res->errcode) && $res->errcode != 0) {
      $this->error("登录失败");
    }
    return $res;
  }
}
