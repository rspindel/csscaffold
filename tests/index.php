<?php

ini_set('display_errors', false);
error_reporting(E_ALL & ~E_STRICT ^ E_DEPRECATED);

chdir(dirname(__FILE__));

define(BASEURL,'http://local.scaffoldframework.com/');

require_once(dirname(__FILE__) . '/../vendor/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../libraries/Bootstrap.php');
require_once(dirname(__FILE__) . '/../libraries/Scaffold/Test.php');

$test = &new GroupTest('All tests');

// Environment Tests
//require_once('./Environment/Environment.php');
//$test->addTestCase(new Test_Environment());

// Utility method tests
//require_once('./Utilities/Utilities.php');
//$test->addTestCase(new UtilityTests());

// CSS Utility tests
//require_once('./CSS/CSS.php');
//$test->addTestCase(new Test_CSS());

// Cache tests
//require_once('Scaffold_Cache.php');
//$test->addTestCase(new CacheTests());

// Logging tests
require_once('./Log/Log.php');
$test->addTestCase(new Test_Log());

// Scaffold Core tests
//require_once('Scaffold_Core.php');
//$test->addTestCase(new CoreTests());

// Stress-tests for each of the included modules
//require_once('Scaffold_Modules.php');
//$test->addTestCase(new ModulesTests());

// Makes sure the correct headers are sent
//require_once('./HTTP/HTTP.php');
//$test->addTestCase(new HTTPTests());

$test->run(new TextReporter());