<?php

ini_set('display_errors', false);
error_reporting(E_ALL & ~E_STRICT ^ E_DEPRECATED);

require_once(dirname(__FILE__) . '/../vendor/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../libraries/Bootstrap.php');

include '../config.php';

$config['system']  = realpath('../') . '/';
$config['cache']   = realpath('./') . '/cache/';

$test = &new GroupTest('All tests');

// Environment Tests
//require_once('Scaffold_Env.php');
//$test->addTestCase(new EnvironmentTests());

// Utility method tests
//require_once('Scaffold_Utils.php');
//$test->addTestCase(new UtilityTests());

// CSS Utility tests
require_once('./CSS/CSS.php');
$test->addTestCase(new Test_CSS());

// Cache tests
//require_once('Scaffold_Cache.php');
//$test->addTestCase(new CacheTests());

// Logging tests
//require_once('Scaffold_Log.php');
//$test->addTestCase(new LogTests());

// Scaffold Core tests
//require_once('Scaffold_Core.php');
//$test->addTestCase(new CoreTests());

// Stress-tests for each of the included modules
//require_once('Scaffold_Modules.php');
//$test->addTestCase(new ModulesTests());

// Makes sure the correct headers are sent
//require_once('Scaffold_HTTP.php');
//$test->addTestCase(new HTTPTests());

$test->run(new TextReporter());