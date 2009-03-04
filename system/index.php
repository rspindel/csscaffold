<?php

	// Prevent direct access to CSS Cacheer files
	define('CSS_CACHEER', true);
	
	// Get everything
	include 'config.php';
	include 'functions.php';
	include 'plugin.php';
		
	// Start the timer...
	$time_start = microtime(true);
	
	// Get the user agent to use throughout
	$ua = parse_user_agent($_SERVER['HTTP_USER_AGENT']);
		
	// We'll work from inside the /css directory
	chdir('../');

/******************************************************************************
 Received request from mod_rewrite
 ******************************************************************************/
 
	// absolute path to requested file, eg. /css/nested/sample.css
	$requested_file	= isset($_GET['cssc_request']) ? $_GET['cssc_request'] : '';
	
	// absolute path to directory containing requested file, eg. /css/nested
	$requested_dir	= preg_replace('#/[^/]*$#', '', $requested_file);
	
	// absolute path to css directory, eg. /css
	$path['css_dir'] = str_replace("/".$path['system'], '', preg_replace('#/[^/]*$#', '', (isset($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_URL']));


/******************************************************************************
 Limit processing to existing css files within this and nested directories
 ******************************************************************************/
 	
 	// If it isn't a css file
	if (substr($requested_file, -4) != '.css')
	{
		echo "/* Error: Request file isn't a css file */";
		exit;
	}
	
	// Or the requested file isn't within the css directory
	elseif(substr($requested_file, 0, strlen($path['css_dir'])) != $path['css_dir'])
	{
		echo "Error: The file wasn't requested from the css directory";
		exit;
	}
	
	// Or the file doesn't exist
	elseif(!file_exists(substr($requested_file, strlen($path['css_dir']) + 1)))
	{
		echo "Error: That file doesn't exist";
		exit;
	}


/******************************************************************************
 Check to see if we should unlock the cache
 ******************************************************************************/
	
	if 
	(
		$cache_lock === TRUE &&
 		isset($_GET['secret_word']) && 
 		$_GET['secret_word'] == $secret_word
 	)
 	{
 		$cache_lock = FALSE;
 	}
 	
	
/******************************************************************************
 Check to see if we should enable test mode
 ******************************************************************************/
 	
 	if 
	(
		isset($_GET['test_mode']) && 
 		isset($_GET['secret_word']) && 
 		$_GET['secret_word'] == $secret_word
 	)
 	{
 		$test_mode = TRUE;
 		$cache_lock = FALSE;
 	}
 	else
 	{
 		$test_mode = FALSE;
 	}
 
	
/******************************************************************************
 If recache=all url param - delete all the cache files.
 ******************************************************************************/
 
	if
	(
		isset($_GET['recache']) &&  $_GET['recache'] == "all" &&
		isset($_GET['secret_word']) && $_GET['secret_word'] = $secret_word
	)
	{
		$f = get_files_in_directory($path['cache'], "path");
		
		foreach($f as $file)
		{
			if(substr($file, -3) == 'css')
			{
				unlink($file);
			}
		}
	}


/******************************************************************************
 Load plugins
 ******************************************************************************/

	$flags = array();
	$plugins = array();

	// Use our included function. (directory, what to return, what file types to get)
	$plugin_files = get_files_in_directory($path['system']."/plugins", "path");
	
	foreach($plugin_files as $plugin)
	{
		include($plugin);
	}
	
	// Now enable the plugins
	foreach($plugin_order as $plugin_class)
	{
		if (class_exists($plugin_class))
		{
			$plugins[$plugin_class] = new $plugin_class($flags);
			$flags = array_merge($flags, $plugins[$plugin_class]->flags);
		}
	}
	

/******************************************************************************
 Create hash of query string to allow variables to be cached
 ******************************************************************************/
 
	$recache = isset($_GET['recache']);
	$args = $flags;
	ksort($args);
	$checksum = md5(serialize($args));
	

/******************************************************************************
 Determine relative and cache paths
 ******************************************************************************/
	
	// Directory to cache processed files
	$cache_dir = $path['cache'];
	
	// path to requested file, relative to css directory, eg. nested/sample.css
	$relative_file = substr($requested_file, strlen($path['css_dir']) + 1);
	
	// path to directory containing requested file, relative to css directory, eg. nested
	$relative_dir = (strpos($relative_file, '/') === false) ? '' : preg_replace("/\/[^\/]*$/", '', $relative_file);
	
	// path to cache of requested file, relative to css directory, eg. css-cacheer/cache/nested/sample.css
	if( $test_mode === TRUE ) 
 	{
 		$cached_file = $cache_dir."test_mode.css";
 	}
	else
	{
		$cached_file = $cache_dir.preg_replace('#(.+)(\.css)$#i', "$1-{$checksum}$2", $relative_file);
	}


/******************************************************************************
 Delete file cache
 ******************************************************************************/
 
	if ($recache && file_exists($cached_file) && $cache_lock === FALSE || $test_mode === TRUE )
	{
		@unlink($cached_file);
	}

/******************************************************************************
 Get modified time for requested file and if available, its cache
 ******************************************************************************/
 
	$requested_mod_time	= filemtime($relative_dir.$relative_file);
	$cached_mod_time	= (int) @filemtime($cached_file); // cache may not exist, silence error with @


/******************************************************************************
 Recreate the cache if stale or nonexistent
 ******************************************************************************/
 
	if (($cached_mod_time < $requested_mod_time) && $cache_lock === FALSE)
	{	
		/******************************************************************************
	 	 Grab the modified CSS file and process plugins
		 ******************************************************************************/
	 
		$css = file_get_contents($relative_dir.$relative_file);
				
		// Pre-process for importers
		foreach($plugins as $plugin)
		{
			$css = $plugin->pre_process($css);
		}
		
		
		// Process for heavy lifting
		foreach($plugins as $plugin)
		{
			$css = $plugin->process($css);
		}
		
		// Post-process for formatters
		foreach($plugins as $plugin_class => $plugin)
		{
			$css = $plugin->post_process($css);
			$filesize[$plugin_class] = strlen($css);
		}
		
			
		/******************************************************************************
		 Make sure the target directory exists
		 ******************************************************************************/
	 
		if ($cached_file != $cache_dir && !is_dir($cache_dir))
		{
			$path = $cache_dir;
			$dirs = explode('/', $relative_dir);
			foreach ($dirs as $dir)
			{
				$path .= '/'.$dir;
				mkdir($path, 0777);
			}
		}
	
		/******************************************************************************
		 Cache parsed CSS
		 ******************************************************************************/
	 
		$css_handle = fopen($cached_file, 'w');
		fwrite($css_handle, $css);
		fclose($css_handle);
		chmod($cached_file, 0777);
		touch($cached_file, $requested_mod_time);
	
	
		/****************************************************************************
		 Create the size report
		 ****************************************************************************/
	 	if($create_report == TRUE)
	 	{
		 	$s = "";
		 	
			// Output the benchmark text file
			foreach($filesize as $plugin_class => $css_size)
			{
				// Make the report line
				$s .= "Filesize after ".$plugin_class." => ".$css_size."\n";
			}
			
			// Create the ratio in the string
			$size_ratio = 100 - (end($filesize) / reset($filesize) * 100) ."%";
			
			$s .= "\n\n Compression Ratio = ". $size_ratio;
			$s .= "\n Final CSS Size (as file before Gzip) = ". fileSize($cached_file) ." bytes (". fileSize($cached_file) / 1024 . " kB)";
			
			// Open the file relative to /css/
			$benchmark_file = fopen($cache_dir . "/css_report.txt", "w") or die("Can't open the report.txt file");
			// Write the string to the file
			fwrite($benchmark_file, $s);
			//chmod($benchmark_file, 777);
			fclose($benchmark_file);
		}
	}

/******************************************************************************
 Or send 304 header if appropriate
 ******************************************************************************/
 
	else if 
	(
		isset($_SERVER['HTTP_IF_MODIFIED_SINCE'], $_SERVER['SERVER_PROTOCOL']) && 
		$requested_mod_time <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])
	)
	{
		header("{$_SERVER['SERVER_PROTOCOL']} 304 Not Modified");
		exit();
	}


/******************************************************************************
 Send cached file to browser
 ******************************************************************************/

	$css = file_get_contents($cached_file);

	$time_end = microtime(true);
	$time = $time_end - $time_start;
	
	$filesize = round( filesize($cached_file) / 1024 , 2);
	
	if ($show_header)
	{
		$header  = "/* Processed and cached by Shaun Inman's CSS Cacheer. ";
		$header .= "Cached filesize is " . $filesize . " kilobytes. ";
		$header	.= "Processed in ".$time." seconds and rendered as " . $ua['browser'] . $ua['version'];
		$header .= ' (with '.str_replace('Plugin', '', preg_replace('#,([^,]+)$#', " &$1", join(', ', array_keys($plugins)))).' enabled)';
		$header .= ' on '.gmdate('r').' <http://shauninman.com/search/?q=cacheer> */'."\r\n";
		$css = $header.$css;
	}
	 
	header('Content-Type: text/css');
	header('Last-Modified: '.gmdate('D, d M Y H:i:s', $requested_mod_time).' GMT');
	echo $css;

	
	