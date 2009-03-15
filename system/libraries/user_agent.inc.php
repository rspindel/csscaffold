<?php if (!defined('CSS_CACHEER')) { header('Location:/'); }

/******************************************************************************
 Returns the user agent
 ******************************************************************************/
	
// really simple (read: imperfect) rendering engine detection
function parse_user_agent($user_agent)
{
	$ua['browser']	= 'Unknown Browser';
	$ua['version']	= '';

	if (preg_match('/(firefox|opera|applewebkit)(?: \(|\/|[^\/]*\/| )v?([0-9.]*)/i', $user_agent, $m))
	{
		$ua['browser']	= strtolower($m[1]);
		$ua['version']	= $m[2];
	}
	else if (preg_match('/MSIE ?([0-9.]*)/i', $user_agent, $v) && !preg_match('/(bot|(?<!mytotal)search|seeker)/i', $user_agent))
	{
		$ua['browser']	= 'ie';
		$ua['version']	= $v[1];
	}
	return $ua;
}