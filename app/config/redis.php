<?php

defined('BASEPATH') OR exit('No direct script access allowed');

$config['redis_default']['host'] = '127.0.0.1';
$config['redis_default']['port'] = '6379';
$config['redis_default']['password'] = null;
$config['redis_default']['database'] = '1';

$config['redis_slave']['host'] = '';
$config['redis_slave']['port'] = '6379';
$config['redis_slave']['password'] = '';
$config['redis_slave']['database'] = '';
