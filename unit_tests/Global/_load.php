<?php

require_once(dirname(__FILE__) . '/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../libraries/Bootstrap.php');

include '../config.php';

$config['system']  = realpath('../') . '/';
$config['cache']   = realpath('./') . '/cache/';