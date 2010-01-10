<?php

/**
 * If set to true, Scaffold will use Minify's CSS 
 * compression library to compress your CSS.
 *
 * If set to false, Scaffold will make your CSS
 * human-readable with nice indentation.
 */
$config['Formatter']['compress'] = true;

/**
 * Compression Options
 *
 * If you've set compress to true, the options below will also apply.
 */

/**
 * Keep the comments inside the CSS
 */
$config['Formatter']['preserve_comments'] = true;

/**
 * You can limit line-lengths of your CSS.
 */
$config['Formatter']['limit_line_lengths'] = false;

/**
 * If you use hacks (you're an idiot) you can preserve them here
 */
$config['Formatter']['preserve_hacks'] = false;

/**
 * Convert font-weights to numbers (which are shorter)
 */
$config['Formatter']['font_weights_to_numbers'] = true;

/**
 * Remove empty meaurements with redundant units eg 0px can just be 0
 */
$config['Formatter']['remove_empty_measurements'] = true;

/**
 * Convert rgb() values to hex, which are shorter
 */
$config['Formatter']['rgb_to_hex'] = true;