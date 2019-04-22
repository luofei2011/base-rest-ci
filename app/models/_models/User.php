<?php
namespace CodeIgniter\models\_models;

class User extends ModelCache {
  protected $primaryKey = "user_id";

  public $timestamps = false;
  public $incrementing = false;

  protected $casts = [
    'user_id' => 'string',
  ];

  public function simpleFormat() {
    return $this->getSelfParams(['user_id', 'nickname', 'avatar']);
  }
}
