<?php defined('SYSPATH') OR die('No direct access allowed.');

function check_path($path)
{
	if(is_dir($path))
	{
		return "pass";
	}
	else
	{
		return "fail";
	}
}

$page =
		"
		<!DOCTYPE html>
		<head>
		<title>Install Check | CSScaffold</title>
		<style>
			.pass { color:green; }
			.fail { color:red; }
			html { background:#e7e7e7; }
			.content { width:70%; margin:30px auto; font:15px/18px Arial; padding:20px; background:#fff; color:#595959; border:1px solid #aaa; margin-bottom: 20px; }
			dl { border-top:1px solid #eee; padding-top:20px }
		</style>
		</head>
		<body>
			<div class='content'>
			<h1>Installation</h1>
			<p>Make sure you remove install.php when you're finished.</p>
				<dl>
					<dt>Document Root</dt>
					<dd class='".check_path(DOCROOT)."'>".DOCROOT."</dd>
		
					<dt>System Path</dt>
					<dd class='".check_path(SYSPATH)."'>".SYSPATH."</dd>
		
					<dt>CSS Path</dt>
					<dd class='".check_path(CSSPATH)."'>".CSSPATH."</dd>
		
				</dl>
			</div>
		</body>
		</html>
		";
		
echo $page;