<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use CodeIgniter\libraries\Oss;
use CodeIgniter\libraries\Utils;
use CodeIgniter\libraries\Sms;

class Common extends MY_Controller {
  /**
   * 七牛OSS客户端上传附件前获取token
   * @method GET
   * @param filename 文件名称 - 主要取后缀名称
   */
  public function uploadToken_get() {
    $filename = $this->buildParams(['filename'], 'get')['filename'];
    $token = Oss::getToken($filename);
    $this->success($token);
  }

  /**
   * 发送短信
   * @method POST
   * @param mobile 手机号码
   */
  public function sendSms_post() {
    $mobile = $this->post('mobile');
    if (!$mobile || !Utils::isMobile($mobile)) {
      $this->error("手机号码格式不正确");
    }
    $imei = $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'];
    list($status, $message) = Sms::sendVerifyCode($mobile, $imei);
    if (!$status) {
      $this->error("发送失败, 请重试");
    }
    $this->success('发送成功');
  }
}
