<?php

require_once(dirname(__FILE__) . '/simpletest/autorun.php');

require_once('Scaffold_Main.php');
require_once('Scaffold_Core.php');
require_once('Scaffold_Utils.php');
require_once('Scaffold_Modules.php');
require_once('Scaffold_Env.php');
require_once('Scaffold_Cache.php');

$test = &new GroupTest('All tests');
//$test->addTestCase(new EnvironmentTests());
//$test->addTestCase(new UtilityTests());
//$test->addTestCase(new CacheTests());
//$test->addTestCase(new CoreTests());
$test->addTestCase(new MainTests());
//$test->addTestCase(new ModulesTests());
$test->run(new HtmlReporter());