# Browsers Plugin

This plugin for (CSScaffold)[http://github.com/anthonyshort/csscaffold/tree] lets you target Internet Explorer from within your CSS. This has been limited to Internet Explorer to keep things simple. Allowing completely browser specific CSS would be very bad mojo.

## Installation

Place the whole Browsers folder into scaffold/plugins and you're ready to go. If you've downloaded the directly from GitHub, rename the folder that is downloaded to "Browsers" and place it in the correct folder.

The folder structure should look like this:

<pre>
Browsers/
	Browsers.php
</pre>

## Usage

<pre><code>@browser lte IE 7
{
	@import '/scaffold/snippets/ie-fixes.css';
}
</code></pre>

This will only import the css file for IE less than or equal to 7. It works in a similar way to regular HTML conditional comments - you can use lt (less than), lte (less than or equal to), gt (greater than), gte (greater than or equal to), ! (not equal to) and nothing means it will just target that browser. You also don't need to include a version number if you're not using an operator. 

Although the browser testing in Scaffold works, there might be times when you want a more surefire solution. You can force it to render a certain way using HTML conditional comments like so:

<pre><code><!--[if !IE]>-->
&lt;link rel="stylesheet" href="../stylesheets/master.css?recache" />
<!--<![endif]-->

<!--[if IE 8]>
<link rel="stylesheet" href="../stylesheets/master.css?recache&ie=8" />
<![endif]-->
	
<!--[if IE 7]>
<link rel="stylesheet" href="../stylesheets/master.css?recache&ie=7" />
<![endif]-->
</code></pre>