<?php
namespace CodeIgniter\libraries;

use CodeIgniter\libraries\Snowflake;
use GuzzleHttp\Client;

class Utils {
  const SMS_EXPIRE_SEC = 600; // 10min
  const SMS_RESEND_SEC = 60;
  const SMS_ONE_DAY_FREQ = 5;
  const SMS_ONE_DAY_IMEI_COUNT = 3; // 最多能从三个ip发送过来

  /**
   * 文件后缀
   */
  public static function getExt($filename) {
    $exts = explode('.', $filename);
    $ext = '';
    if (count($exts) > 1) {
      $ext = '.' . end($exts);
    }
    return $ext;
  }

  /**
   * snowflake uuid
   */
  public static function getSnowUuid() {
    $snowflake = new Snowflake(getenv("SNOW_FLAKE_MACHINE_ID"));
    return $snowflake->getId();
  }

  /**
   * 判断是否为合法手机号
   * @param $mobile
   * @return bool
   */
  public static function isMobile($mobile) {
    if(preg_match('/^1\d{10}$/', $mobile))
      return true;
    return false;
  }

  /**
   * Returns the trailing name component of a path.
   * This method is similar to the php function `basename()` except that it will
   * treat both \ and / as directory separators, independent of the operating system.
   * This method was mainly created to work on php namespaces. When working with real
   * file paths, php's `basename()` should work fine for you.
   * Note: this method is not aware of the actual filesystem, or path components such as "..".
   *
   * @param string $path A path string.
   * @param string $suffix If the name component ends in suffix this will also be cut off.
   * @return string the trailing name component of the given path.
   * @see http://www.php.net/manual/en/function.basename.php
   */
  public static function basename($path, $suffix = '') {
    if (($len = mb_strlen($suffix)) > 0 && mb_substr($path, -$len) === $suffix) {
      $path = mb_substr($path, 0, -$len);
    }
    $path = rtrim(str_replace('\\', '/', $path), '/\\');
    if (($pos = mb_strrpos($path, '/')) !== false) {
      return mb_substr($path, $pos + 1);
    }

    return $path;
  }

  /**
   * Converts a CamelCase name into an ID in lowercase.
   * Words in the ID may be concatenated using the specified character (defaults to '-').
   * For example, 'PostTag' will be converted to 'post-tag'.
   * @param string $name the string to be converted
   * @param string $separator the character used to concatenate the words in the ID
   * @param bool|string $strict whether to insert a separator between two consecutive uppercase chars, defaults to false
   * @return string the resulting ID
   */
  public static function camel2id($name, $separator = '-', $strict = false) {
    $regex = $strict ? '/[A-Z]/' : '/(?<![A-Z])[A-Z]/';
    if ($separator === '_') {
      return strtolower(trim(preg_replace($regex, '_\0', $name), '_'));
    }

    return strtolower(trim(str_replace('_', $separator, preg_replace($regex, $separator . '\0', $name)), $separator));
  }

  /**
   * 生成一个对象
   */
  public static function arr2obj($data = null) {
    if ($data === null) return null;
    $instance = new static;
    foreach ($data as $key => $value) {
      $instance->$key = $value;
    }
    return $instance;
  }

  /**
   * 检测字符串是否是合法字符串
   */
  public static function is_date($str) {
    return date('Y/m/d', strtotime($str)) == $str;
  }

  /**
   * 格式化Object中的元素
   */
  public static function setAttributes($class, $params) {
    foreach ($params as $key => $value) {
      $class->$key = $value;
    }
    return $class;
  }

  /**
   * 发起请求
   */
  public static function request($url, $data = [], $method = "GET", $headers = []) {
    $client = new Client();
    $response = $client->request($method, $url);
    $body = $response->getBody()->getContents();
    return json_decode($body);
  }
}
