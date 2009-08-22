<?php

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
		<title>Install Check | CSScaffold</title>
		<style>
			.pass { color:green; }
			.fail { color:red; }
		</style>
		<dl>
			<dt>Cache Path</dt>
			<dd class='".check_path(CACHEPATH)."'>".CACHEPATH."</dd>
		</dl>
		<dl>
			<dt>System Path</dt>
			<dd class='".check_path(SYSPATH)."'>".SYSPATH."</dd>
		</dl>
		<dl>
			<dt>System URL</dt>
			<dd class='".check_path(join_path(DOCROOT, SYSURL))."'>".SYSURL."</dd>
		</dl>
		<dl>
			<dt>CSS Path</dt>
			<dd class='".check_path(CSSPATH)."'>".CSSPATH."</dd>
		</dl>
		<dl>
			<dt>URL path to the CSS directory</dt>
			<dd class='".check_path(join_path(DOCROOT, CSSURL))."'>".CSSURL."</dd>
		</dl>
		";
		
echo $page;