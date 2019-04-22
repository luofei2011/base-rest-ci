<?php

use CodeIgniter\models\_models\User as UserModel;

class Users extends MY_Model {
  public function isAdmin($userid) {
    return false;
  }
}
