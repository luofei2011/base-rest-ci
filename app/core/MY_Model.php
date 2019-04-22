<?php
/*
 * CI_Model 扩展类 - 添加常用方法
 * @author luofei
 */

class MY_Model extends CI_Model {
  public function __construct() {
    parent::__construct();
  }

  public function getSelfParams($params) {
    $return = [];
    if (!is_array($params)) {
      $params = func_get_args();
    }

    foreach ($params as $key) {
      $return[$key] = $this->{$key} ?? '';
    }

    return $return;
  }
}
