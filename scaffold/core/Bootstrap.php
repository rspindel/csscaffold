<?php defined('SYSPATH') OR die('No direct access allowed.');

require SYSPATH . '/core/Common.php';
require SYSPATH . '/core/Benchmark.php';
require SYSPATH . '/core/Plugins.php';
require SYSPATH . '/core/CSScaffold.php';
require SYSPATH . '/core/CSS.php';

require SYSPATH . '/libraries/FirePHPCore/fb.php';
require SYSPATH . '/libraries/FirePHPCore/FirePHP.class.php';

# Send the request through to the main controller
CSScaffold::setup($_GET);

# Parse the css
CSScaffold::parse_css();

# Send it to the browser
CSScaffold::output_css();