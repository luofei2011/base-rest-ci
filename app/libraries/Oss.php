<?php
namespace CodeIgniter\libraries;

use Exception;
use Qiniu\Auth;
use Qiniu\Config;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
use CodeIgniter\libraries\Utils;

class Oss {
  public static function getToken($filename) {
    $saveKey = date("Y/m/d/") . Utils::getSnowUuid() . Utils::getExt($filename);
    return (self::getAuth())->uploadToken(getenv("QINIU_BUCKET"), null, 3600, [
      'saveKey' => $saveKey,
    ]);
  }

  public static function getAuth() {
    return new Auth(getenv("QINIU_ACCESS_KEY"), getenv("QINIU_SECRET_KEY"));
  }

  public static function uploadLocal($path) {
  }

  /**
   * 获取文件信息 - 用于判断文件是否存在
   */
  public static function stat($file, $throw = false) {
    $auth = self::getAuth();
    $config = new Config();
    $bucketManager = new BucketManager($auth, $config);
    list($fileInfo, $err) = $bucketManager->stat(getenv("QINIU_BUCKET"), ltrim($file, "/"));
    if ($err) {
      if ($throw) {
        throw new Exception("file not exists");
      }
      return false;
    }
    return $fileInfo;
  }

  /**
   * 获取cdn地址
   */
  public static function getUrl($path) {
    return $path && strpos($path, "http") !== 0 ? (rtrim(getenv("QINIU_CDN_URL"), "/") . "/" . ltrim($path, "/")) : $path;
  }
}
