<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<title>CSScaffold Installation</title>

<style type="text/css">
body { width: 700px; margin: 0 auto; font-family: sans-serif; font-size: 90%; }

#tests table { border-collapse: collapse; width: 100%; }
	#tests table th,
	#tests table td { padding: 0.2em 0.4em; text-align: left; vertical-align: top; }
	#tests table th { width: 12em; font-weight: normal; font-size: 1.2em; }
	#tests table tr:nth-child(odd) { background: #eee; }
	#tests table td.pass { color: #191; }
	#tests table td.fail { color: #911; }
		#tests #results { color: #fff; }
		#tests #results p { padding: 0.8em 0.4em; }
		#tests #results p.pass { background: #191; }
		#tests #results p.fail { background: #911; }
</style>

</head>
<body>

<h1>Environment Tests</h1>

<p>The following tests have been run to determine if CSScaffold will work in your environment. If any of the tests have failed, consult the <a href="http://wiki.github.com/anthonyshort/csscaffold">documentation</a> for more information on how to correct the problem.</p>

<div id="tests">
<?php $failed = FALSE ?>
<table cellspacing="0">
<tr>
<th>PHP Version</th>
<?php if (version_compare(PHP_VERSION, '5.2', '>=')): ?>
<td class="pass"><?php echo PHP_VERSION ?></td>
<?php else: $failed = TRUE ?>
<td class="fail">Kohana requires PHP 5.2 or newer, this version is <?php echo PHP_VERSION ?>.</td>
<?php endif ?>
</tr>

<tr>
<th>System Directory</th>
<?php if (is_dir(BASEPATH)): ?>
<td class="pass"><?php echo BASEPATH ?></td>
<?php else: $failed = TRUE ?>
<td class="fail">The configured <code>system</code> directory does not exist or does not contain required files.</td>
<?php endif ?>
</tr>

<tr>
<th>Cache Directory</th>
<?php if (is_dir(CACHEPATH)): ?>
<td class="pass"><?php echo CACHEPATH ?></td>
<?php else: $failed = TRUE ?>
<td class="fail">The configured <code>cache</code> directory does not exist or does not contain required files.</td>
<?php endif ?>
</tr>

<tr>
<th>CSS Directory</th>
<?php if (is_dir(CSSPATH)): ?>
<td class="pass"><?php echo CSSPATH ?></td>
<?php else: $failed = TRUE ?>
<td class="fail">The configured <code>css</code> directory does not exist or does not contain required files.</td>
<?php endif ?>
</tr>

<tr>
<th>Assets directory</th>
<?php if (is_dir(ASSETPATH)): ?>
<td class="pass"><?php echo ASSETPATH ?></td>
<?php else: $failed = TRUE ?>
<td class="fail">The configured asset directory does not exist or does not contain required files.</td>
<?php endif ?>
</tr>


<tr>
<th>URL to CSS directory</th>
<?php if (is_dir($_SERVER['DOCUMENT_ROOT'] . URLPATH)): ?>
<td class="pass"><?php echo URLPATH ?></td>
<?php else: $failed = TRUE ?>
<td class="fail">The configured css url path does not exist or does not contain required files.</td>
<?php endif ?>
</tr>

</table>

<div id="results">
<?php if ($failed === TRUE): ?>
<p class="fail">CSScaffold may not work correctly with your environment.</p>
<?php else: ?>
<p class="pass">Your environment passed all requirements. Remove or rename the <code>install.php</code> file now.</p>
<?php endif ?>
</div>

</div>

</body>
</html>