<?php

namespace CodeIgniter\libraries;

class CConst {
  # 无效的session
  const INVALID_SESSION = 4000;
  const BAN_CODE = 4010;

  # 未注册
  const USER_NOT_EXISTS = 7001;

  const UUID_LABEL_USER = 0;

  const DB_USERS_AUTO_INCREMENT_KEY = "db:users:auto_increment:key";

  const UUID_KEYS = [
    self::UUID_LABEL_USER => self::DB_USERS_AUTO_INCREMENT_KEY,
  ];
}
