<?php define('CSS_CACHEER', true);

	// Turn off those ugly errors :)
	//error_reporting(0);
	
 /******************************************************************************
 Load the required stuff
 ******************************************************************************/
 	
 	// Fetch the core functions. This contains __autoload() to load our classes
	require 'libraries/functions.inc.php'; 

/******************************************************************************
Received request from mod_rewrite and set some vars
******************************************************************************/
	  
	// The file that the user requested
	$requested_file	= isset($_GET['cssc_request']) ? $_GET['cssc_request'] : '';
	
	// Do they want to recache
	$recache = isset($_GET['recache']);
	
	
/******************************************************************************
 We've got everything we need, let do this thing...
 ******************************************************************************/
		
	// Start the CSScaffold plugin object
	$css = new CSScaffold($requested_file, $recache);
	
	// Send it to the browser
	$css->output_css();
	

