<?php
namespace CodeIgniter\models\_models;

use \Illuminate\Database\Eloquent\Model as Eloquent;
use CodeIgniter\libraries\Utils;
use CodeIgniter\libraries\Redis;
use StaticInstanceTrait;
use Exception;

class ModelCache extends Eloquent {
  use StaticInstanceTrait;

  const CACHE_TIME = 864000;

  public static function keyPrefix() {
    return "db:" . Utils::camel2id(Utils::basename(get_called_class()), '_');
  }

  public static function getCacheKey($condition, $name = false) {
    if (is_array($condition)) {
      $keys = array_keys($condition);
      sort($keys);
      return self::keyPrefix() . ":" . join(":", $keys) . ":" . join("_", array_values($condition));
    }
    $name || $name = get_called_class()::instance()->primaryKey;
    return self::keyPrefix() . ":" . $name . ":" . $condition;
  }

  public static function findOne($condition, $throw = false, $cache = true) {
    try {
      if (!$cache) {
        return parent::findOrFail($condition);
      }
      $cacheKey = self::getCacheKey($condition);
      $model = Redis::instance()->hgetall($cacheKey);
      if (!$model) {
        $model = parent::findOrFail($condition);
        if (!$model) {
          throw new Exception("not found");
        }
        # FIX
        $json = $model->getAttributes();
        if (isset($json['password'])) unset($json['password']);
        Redis::instance()->hmset($cacheKey, $json);
        Redis::instance()->expire($cacheKey, self::CACHE_TIME);
        return $model;
      }
      $instance = new static;
      $instance->setRawAttributes($model);
      $instance->exists = true;
      return $instance;
    }
    catch (Exception $e) {
      if ($throw) {
        throw new Exception($e->getMessage());
      }
    }
    return null;
  }

  public static function getRedis() {
    return Redis::instance();
  }

  public static function hexists($cacheKey, $key) {
    return self::getRedis()->hexists($cacheKey, $key);
  }

  public static function hset($cacheKey, $key, $value) {
    return self::getRedis()->hset($cacheKey, $key, $value);
  }

  public static function hdel($cacheKey, $key) {
    return self::getRedis()->hdel($cacheKey, $key);
  }

  /**
   * 获取指定的部分属性
   */
  public function getSelfParams($params) {
    $return = [];
    if (!is_array($params)) {
      $params = func_get_args();
    }

    foreach ($params as $key) {
      try {
        $value = $this->{$key};
      }
      catch (\Exception $e) {
        $value = '';
      }
      $return[$key] = $value ?? '';
    }

    return $return;
  }

  /**
   * 刷新cache
   */
  public function flushCache() {
    $primaryKey = $this->primaryKey;
    $cacheKey = self::keyPrefix() . ":" . $primaryKey . ":" . $this->$primaryKey;
    $names = $this->getAttributes();
    self::getRedis()->del($cacheKey);
    self::getRedis()->hmset($cacheKey, $names);
    self::getRedis()->expire($cacheKey, self::CACHE_TIME);
  }

  /**
   * 删除全部
   */
  public function delCacheKey() {
    $primaryKey = $this->primaryKey;
    $cacheKey = self::keyPrefix() . ":" . $primaryKey . ":" . $this->$primaryKey;
    self::getRedis()->del($cacheKey);
  }
}
