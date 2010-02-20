<?php

require_once('simpletest/autorun.php');
require_once('../libraries/Bootstrap.php');

include '../config.php';

$config['system']  = realpath('../') . '/';
$config['cache']   = realpath('./') . '/cache/';