<?php if (!defined('CSS_CACHEER')) { header('Location:/'); }

class User_agent
{
	var $user_agent;
	var $browser;
	var $version;
	
	function User_agent()
	{	
		$this->user_agent = $_SERVER['HTTP_USER_AGENT'];
		$this->browser = 'Unknown Browser';
		$this->version = '';
	
		if (preg_match('/(firefox|opera|applewebkit)(?: \(|\/|[^\/]*\/| )v?([0-9.]*)/i', $this->user_agent, $m))
		{
			$this->browser = strtolower($m[1]);
			$this->version = $m[2];
		}
		else if (preg_match('/MSIE ?([0-9.]*)/i', $this->user_agent, $v) && !preg_match('/(bot|(?<!mytotal)search|seeker)/i', $this->user_agent))
		{
			$this->browser = 'ie';
			$this->version = $v[1];
		}
	}

}