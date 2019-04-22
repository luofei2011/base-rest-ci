<?php

/**
 * 静态单例
 */

trait StaticInstanceTrait {
  protected static $_instances = [];
  protected $instanceKey;

  public static function instance($key = null, $refresh = false) {
    if (!isset($key)) {
      $key = get_called_class();
    }

    if (!$refresh && isset(static::$_instances[$key]) && static::$_instances[$key] instanceof static) {
      return static::$_instances[$key];
    }

    $client = new static();
    $client->instanceKey = $key;
    return static::$_instances[$key] = $client;
  }
}
