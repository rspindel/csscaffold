<?php
/******************************************************************************
 Prevent direct access
 ******************************************************************************/
if (!defined('CSS_CACHEER')) { header('Location:/'); }

// error_reporting(0);

// Lock the cache, so it can never be recached, no matter what. Useful for
// when your site goes live - you don't want people spamming ?recache on you.
// It's also handy to use while you're uploading your css file, so you
// don't get a half-uploaded css file cached.
$cache_lock = FALSE;

// IMPORTANT: Set a password for overriding the cache lock. This
// is useful if you *really* need to recache, but you don't
// want to open up recaching to the public.
$secret_word = "password";


// ENABLING TEST MODE
// If you have cache lock turned on, you can still modify, cache 
// and test your css without letting the public see the changes.
// To get access to the test file, you need have cache lock on, use the secret
// word and use test_mode in your URL
// eg. screen.css?test_mode&secret_word=password
// When you're happy with the changes, simply recache your css.


// Generate the header at the top of the CSS - useful for debugging plugins, testing times etc
$show_header = TRUE;

// Create a size report, useful for determining how much compression you're getting
$create_report = FALSE;

// All paths are relative to the css directory above your system folder - usually your css folder
$path = array(
	"system" 		=> "system",
	"cache"			=> "system/cache/",
	"browsers"		=> "specific",
	"append_dir" 	=> "plugins",
	"xml" 			=> "assets/xml",
	"fonts" 		=> "assets/fonts",
	"backgrounds" 	=> "assets/backgrounds",
	"image_titles" 	=> "assets/titles"
);

// CSSTidy Options
$tidy_options = array(
	'preserve_css' 				=> false,
	'sort_selectors' 			=> false,
	'sort_properties' 			=> true,
	'merge_selectors' 			=> 2,
	'optimise_shorthands'		=> 1,
	'compress_colors' 			=> true,
	'compress_font-weight' 		=> false,
	'lowercase_s' 				=> true,
	'case_properties' 			=> 1,
	'remove_bslash' 			=> false,
	'remove_last_;' 			=> true,
	'discard_invalid_properties' => false,
	'css_level' 				=> 'CSS2.1',
	'timestamp' 				=> false,
	'template'					=> 'highest_compression'
);

// Control the order the plugins are loaded. 
// Comment out plugins you don't want to load.
$plugin_order = array(
	'ServerImportPlugin',
	'Browsers'	,
	'Append',
	'BasedOnPlugin',
	'NestedSelectorsPlugin',
	'ConstantsPlugin',
	'Math',
	'Grid',
	'ImageReplacement',
	'AddTo',
	'Base64Plugin',
	'CSSTidyPlugin',
	'CSS3Helper',
	'CondenserPlugin',
	//'PrettyPlugin'
);

?>