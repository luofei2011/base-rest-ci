<?php
namespace CodeIgniter\libraries;

use Exception;
use CodeIgniter\libraries\Redis;
use CodeIgniter\libraries\Utils;
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

class Sms {
  const EXPIRE_SEC = 600;    // 过期时间间隔
  const RESEND_SEC = 60;     // 重发时间间隔
  const ONE_DAY_FREQ = 5;    // 每日向同一个手机号发短信的次数
  const ONE_DAY_IMEI_COUNT = 3; // 每日向同一个手机号发送短信的IMEI个数

  const VC_PREFIX = "vc_";
  const VC_LIMIT_PREFIX = "vc_limit_";


  /**
   * 向指定手机号发送验证码
   * @param $mobile
   * @param $imei
   * @return bool
   */
  public static function sendVerifyCode($mobile, $imei) {
    if(!Utils::isMobile($mobile)) {
      throw new Exception("手机号码格式不正确");
    }

    $vcKey = self::VC_PREFIX . $mobile;
    $limitKey = self::VC_LIMIT_PREFIX . $mobile;

    // 验证码重发限制
    $data = json_decode(Redis::instance()->get($vcKey), true);
    if($data && time() < $data['resend_expire']) {
      throw new Exception("短信已在1分钟内发出，请耐心等待");
    }

    // 手机号及IMEI限制
    $sendCnt = Redis::instance()->zScore($limitKey, $imei);
    if($sendCnt && $sendCnt >= self::ONE_DAY_FREQ) {
      throw new Exception("没收到短信？请稍等或者检查短信是否被屏蔽");
    }
    $imeiCnt = Redis::instance()->zCard($limitKey);
    if($imeiCnt >= self::ONE_DAY_IMEI_COUNT && !$sendCnt) {
      throw new Exception("已超过验证码发送设备限制");
    }

    // 获取验证码
    if(!$data) {
      $vc = strval(rand(100000, 999999));
      $data = array('vc' => $vc, 'resend_expire' => 0);
      Redis::instance()->set($vcKey, json_encode($data));
      Redis::instance()->expire($vcKey, self::EXPIRE_SEC); // 设置验证码过期时间
    }
    $vc = $data['vc'];

    list($status, $message) = self::send($mobile, ['code' => $vc]);
    if($status) {
      // 重设重发时限
      $data['resend_expire'] = time() + self::RESEND_SEC;
      $ttl = Redis::instance()->ttl($vcKey);
      Redis::instance()->set($vcKey, json_encode($data));
      Redis::instance()->expire($vcKey, $ttl);

      // 设置手机号与IMEI限制
      Redis::instance()->zIncrBy($limitKey, 1, $imei);
      Redis::instance()->expireAt($limitKey, strtotime(date('Y-m-d',strtotime('+1 day'))));
    }
    return [$status, $message];
  }

  /**
   * 向指定手机号发送短信
   * @param $mobile
   * @param $content
   * @return bool
   */
  public static function send($mobile, $params){
    AlibabaCloud::accessKeyClient(getenv("ALISMS_ACCESS_KEY"), getenv("ALISMS_ACCESS_SECRET"))
      ->regionId('cn-hangzhou') // replace regionId as you need
      ->asGlobalClient();

    try {
      $result = AlibabaCloud::rpcRequest()
        ->product('Dysmsapi')
        // ->scheme('https') // https | http
        ->version('2017-05-25')
        ->action('SendSms')
        ->method('POST')
        ->options([
          'query' => [
            'PhoneNumbers' => $mobile,
            'SignName' => getenv("ALISMS_SIGNNAME"),
            'TemplateCode' => getenv("ALISMS_TEMPLATE_CODE"),
            'TemplateParam' => json_encode($params),
          ],
        ])
        ->request();
      return [true, $result->toArray()];
    } catch (ClientException $e) {
      return [false, $e->getErrorMessage()];
    } catch (ServerException $e) {
      return [false, $e->getErrorMessage()];
    }
    return [false, false];
  }

  /**
   * 验证短信验证码
   * @param $mobile
   * @param $vc
   * @return bool
   */
  public static function checkVerifyCode($mobile, $vc) {
    $vcKey = self::VC_PREFIX . $mobile;
    $vcData = json_decode(Redis::instance()->get($vcKey), true);
    if($vcData && $vcData['vc'] === $vc) {
      return true;
    }
    return false;
  }

  /**
   * 清除验证码
   * @param $mobile
   */
  public static function cleanVerifyCode($mobile) {
    $vcKey = self::VC_PREFIX . $mobile;
    $limitKey = self::VC_LIMIT_PREFIX . $mobile;
    Redis::instance()->del($vcKey);
    Redis::instance()->del($limitKey);
  }
}
