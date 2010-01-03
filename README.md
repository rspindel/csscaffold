#CSScaffold

A dynamic CSS framework inspired by Shaun Inman's CSS Cacheer. It's aimed at experienced CSS developers - it gives you the tools to create great CSS easily. It abstracts some repetitive and annoying flaws of the language to make it easier to create and maintain, all while giving you the benefits of caching.

- Constants
- SASS-style mixins
- Compressed, Cached and Gzipped on-the-fly
- Nested Selectors
- Perform PHP operations
- Image replace text by just linking to the image file
- Plus easily add your own functionality using the plugin system

##What you need

- PHP5+
- modrewrite enabled in Apache (optional)

##Installation & Documentation

See http://anthonyshort.com.au/scaffold/ for documentation.

##Having trouble?

Make sure you read the documentation on the wiki. If you find a bug, put it in the issues section on Github. If you're still having trouble, feel free to contact me at csscaffold@me.com. 

##License

Copyright (c) 2009, Anthony Short <csscaffold@me.com>
http://github.com/anthonyshort/csscaffold
All rights reserved.

This software is released under the terms of the New BSD License.
http://www.opensource.org/licenses/bsd-license.php

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />

	<link rel="stylesheet" href="stylesheets/docs.css" />

	<title>CSScaffold Documentation</title>

</head>
<body>

	<div id="document">
	
	<nav>
		<ol>
			<li><a href="#overview">Overview</a>
				<ul>
					<li><a href="#how-it-works">How it works</a></li>	
					<li><a href="#features">Features</a></li>
					<li><a href="#requirements">Requirements</a></li>
					<li><a href="#download">Download</a></li>
					<li><a href="#upgrade">Upgrade Notes</a></li>
					<li><a href="#release-notes">Release Notes</a></li>
					<li><a href="#support">Support</a></li>
					<li><a href="#credits">Credits</a></li>
					<li><a href="#license">License</a></li>
				</ul>	
			</li>
			<li><a href="#user-guide">User Guide</a>
				<ul>
					<li><a href="#installation">Installation</a></li>
					<li><a href="#basics">The Basics</a></li>
					<li><a href="#constants">Constants</a></li>
					<li><a href="#expression">Expression</a></li>
					<li><a href="#include">Include</a></li>
					<li><a href="#iteration">Iteration</a></li>
					<li><a href="#mixins">Mixins</a></li>
					<li><a href="#ready-to-go">Built-in Mixins</a></li>
					<li><a href="#nested-selectors">Nested Selectors</a></li>
					<li><a href="#output">Custom Output</a>
						<ul>
							<li><a href="#typography">Typography Test Suite</a></li>
							<li><a href="#validate">Validate</a></li>
							<li><a href="#grid">Grid</a></li>
						</ul>
					</li>
					<li><a href="#plugins">Plugins</a>
						<ul>
							<li><a href="#image-replace">ImageReplace</a></li>
							<li><a href="#xmlconstants">XML Constants</a></li>
						</ul>
					</li>
				</ul>
			</li>
			<li><a href="#advanced">Advanced Usage</a>
				<ul>
					<li><a href="#structure">Folder structure</a></li>
					<li><a href="#config">Configuration Options</a>
						<ul>
							<li><a href="#paths">Custom Paths</a></li>
						</ul>
					</li>
					<li><a href="#layout">Layout Module</a></li>
				</ul>
			</li>
		</ol>
		<!--
		<ol>
			<li><a href="#reference">Reference Guide</a>
				<ul>
					<li><a href="#built-in-mixins">Built-in mixins</a>
						<ul>
							<li><a href="#mixin-css3">CSS3</a></li>
							<li><a href="#mixin-layout">Layout</a></li>
							<li><a href="#mixin-typography">Typography</a>
								<ul>
									<li><a href="#font-stacks">Font stacks</a></li>
								</ul>
							</li>
							<li><a href="#utilities">Utilties</a></li>
							<li><a href="#widgets">Widgets</a>
								<ul>
									<li><a href="#tabs">Tabs</a></li>
									<li><a href="#lightbox">Lightbox</a></li>
									<li><a href="#buttons">Buttons</a></li>
								</ul>
							</li>
						</ul>
					</li>
					<li><a href="#premade-grids">Pre-made Grids</a>
						<ul>
							<li><a href="#960gs-grid">960.gs</a></li>
							<li><a href="#blueprint-grid" title="">Blueprint</a></li>
							<li><a href="#default-grid" title="">Default</a></li>
						</ul>
					</li>
					<li><a href="#templates">Templates</a></li>
					<li><a href="#methods">Plugin Methods</a>
						<ul>
							<li><a href="#class-css">CSS class</a></li>
							<li><a href="#class-csscaffold">CSScaffold class</a></li>
							<li><a href="#class-plugin">Plugin class</a></li>
						</ul>
					</li>
				</ul>
			</li>
						<li><a href="#plugin-development">Plugin Development</a>
				<ul>
					<li><a href="#plugin-basics">Plugin Basics</a></li>
					<li><a href="#creating-properties">Creating properties</a></li>
					<li><a href="#creating-at-rules">Creating at-rules</a></li>
					<li><a href="#finding-properties">Finding properties</a></li>
					<li><a href="#finding-selectors">Finding selectors</a></li>
					<li><a href="#finding-at-rules">Finding at-rules</a></li>
					<li><a href="#adding-libraries">Adding libraries</a></li>
					<li><a href="#adding-language-files">Adding language files</a></li>
					<li><a href="#error-handling">Error handling</a></li>
					<li><a href="#using-the-cache">Using the cache</a></li>
					<li><a href="#creating-custom-views">Creating custom views</a></li>
					<li><a href="#firephp">Using FirePHP to log messages</a></li>
				</ul>
			</li>
		</ol>
		-->
	</nav>
		
	<div id="content">

		<h1>CSScaffold v2.0.0</h1>
		
		<h2 id="overview">Overview</h2>
		
		<p>Scaffold is a new type of CSS framework, built with PHP, that allows speed up your development time by doing the hard work for you. It is different to other CSS frameworks, like Blueprint and 960.gs, but it's power lies in it's ability to extend the CSS language. You can even generate the CSS found in other CSS frameworks dynamically, like grid classes to quickly build layouts. </p>
		
		<p><strong>It will change the way you write and manage your CSS.</strong></p>
		
		<h3 id="how-it-works">How it works</h3>
		
		<p>Scaffold sits in your CSS directory, and uses .htaccess files to automatically pass any CSS file through Scaffold first for processing and caching - it all happens in the background. Just drop the files onto your server and you're ready to go with the CSS you've already written.</p>
		
		<h3 id="features">Features</h3>
		
		<ul>
			<li>Written exactly like CSS, so you don't have to learn a new syntax</li>
			<li>Use constants within your CSS</li>
			<li>Use mixins like in SASS and Compass</li>
			<li>Nest selectors to tidy up your code</li>
			<li>Optimize, compress and gzip</li>
			<li>Generate test suites for typography, layouts and form styles</li>
			<li>Plugin architecture that lets you extend the language the way you want</li>
			<li>and much more!</li>
		</ul>
		
		<h3 id="requirements">Requirements</h3>
		
		<p>The only requirements are a webserver running <strong>PHP 5+</strong>. You need to be able to use .htaccess files if you want automatic parsing of your CSS files (An apache-like server).</p>
		
		<h3 id="download">Download</h3>
		
		<p>You can download the latest version directly from Github in the downloads section. You can also <a href="http://github.com/anthonyshort/csscaffold/zipball/master">download the latest source</a> <em>(note: It might have a couple of untamed bugs)</em>.</p>
		
		<h3 id="upgrade">Upgrade Notes</h3>
		
		<p><strong>Upgrading Scaffold shouldn't break you're CSS</strong>, unless it's a jump from a point verion (1.5 to 1.6), where you might be at risk as some syntax may change.</p>
		
		<p>However, the syntax is locked in for good now, so you shouldn't have any issue simply replacing the Scaffold folder on your server with the latest version, but just keep this in mind.</p>
		
		<h3 id="release-notes">Release notes</h3>
		
		<p></p>
		
		<h3 id="support">Support</h3>
		
		<p>If you're having any issues installing or using Scaffold, have any suggestions, bugs or critique, you can send an email to <a href="mailto:csscaffold@me.com">csscaffold@me.com</a>.</p>
		
		<p>Please note that under Scaffold's license, I hold not accountablity for breaking your site, costing you money or otherwise harming you commercially or financially. This is an MIT-licensed project simply out there for those that care to use it at <strong>their own risk</strong>.</p>
		
		<h3 id="license">License</h3>
		
		<p>This software is released under the terms of the <a href="http://www.opensource.org/licenses/bsd-license.php" rel="license">New BSD License</a>.</p>
		
		<h2 id="user-guide">User guide</h2>
		
		<h3 id="installation">Installation</h3>
		
		<ol>
			<li><a href="#">Download</a> the latest release of Scaffold.</li>
			<li>Rename the downloaded file to <kbd>scaffold</kbd></li>
			<li>Place all the files <strong>inside your css directory on your webserver</strong>. e.g <code>/themes/css/scaffold/</code></li> 
			<li>Move <code>css.htaccess</code> into your css directory (one level up) and rename to <code>.htaccess</code></li>
			<li>Rename <code>example-config.php</code> to just <code>config.php</code></li>
			<li>Change any configuration options in <code>scaffold/config.php</code></li>
			<li>If all is well, when you view your CSS file in a browser, it will be parsed.</li>
		</ol>

		<h4>Install with Git</h4>

<pre><code>cd path/to/css/directory
git clone git://github.com/anthonyshort/csscaffold.git scaffold
git mv scaffold/css.htaccess .htaccess</code></pre>

		<h3 id="basics">The Basics</h3>
		
		<p>Now that you've installed Scaffold, you can see your parsed CSS file in one of 2 ways; If you're using the .htaccess file, you can call your CSS files as normal and if you're not, you can manually pass the CSS through Scaffold.</p>
		
		<h5>Method One</h5>
		
		<pre><code>http://localhost<ins>/css/screen.css</ins></code></pre>
		
		<h5>Method Two</h5>
		
		<pre><code>http://localhost/<ins>css/scaffold/index.php?request=/css/screen.css</ins></code></pre>
		
		<h5>Forcing a recache</h5>
		
		<p>If you've got <code>always_recache</code> turned off, but have only modified included files (Scaffold won't know the main CSS has changed) you can use <code>recache</code> as a URL parameter to force it to recache.</p>
		
		<pre><code>http://localhost/css/screen.css<ins>?recache</ins></code></pre>
		
		<div class="success">
		<p>The best method is to turn <code>always_recache</code> on while developing so you don't ever need to force a recache, and when you send the CSS live, change <code>IN_PRODUCTION</code> and <code>cache_lock</code> to <code>true</code>.
		</div>
		
		<h4 id="constants">Constants</h4>
		<p>You declare constants using a syntax similar to normal css:</p>
		
		<pre><code>@constants 
{
	normal_color:#eee;
	other_constant:10px;
}</code></pre>

		<p>We've defined two constants - 'normal_color' and 'other_constant'- to use those constants, we do this:
		
		<pre><code>body
{
	color:!normal_color;
}</code></pre>
		
		<p>It doesn't matter where you define your constants, they can't be set again like variables. Once they are set, they are saved for good. You can also define constants inside config.php if there are any you can't set with CSS alone.</p>
		
		<h4 id="expression">Expressions</h4>
		<p>You can parse parts of your CSS as PHP:</p>
		
		<pre><code>#id 
{
	padding:#[10 * !constant]px;
}</code></pre>

		<p>Anything you place inside the square brackets will be parsed as PHP.</p>

		<h4 id="include">Includes</h4>
		<p>One of the great things about Scaffold is that it combines all of your CSS into one file. Scaffold imports the files server-side before it's cached and sent to the browser. This means you'll only send 1 file, not 4 or 5.</p>
		
		<pre><code>@include '/css/sections/layout.css';</code></pre>
		
		<p>There are a couple of other things you can do also - You don't need the extension of CSS files and you can use the ~ to reference the css directory. For example, this will also work:</p>
		
		<pre><code>@include '~/sections/layout';</code></pre>

		<h4 id="iteration">Iteration</h4>
		<p>You can loop through to build selectors, just like a for loop in PHP. This is how the grid mixin uses it to generate the grid classes:</p>
		
		<pre><code>@for !i from 1 to 12
{
	.columns-!i { +span(!i); }
}</code></pre>

		<h4 id="mixins">Mixins</h4>
		<p>Mixins are like functions for CSS, and <strong>they are by far the most powerful element of Scaffold</strong>. They let you define property groups that can be reused or extended upon. They can have parameters and they can nested within each other. Mixins were ported over from SASS.</p>
		
		<p>This is the basic mixin syntax:</p>
		
		<pre><code>=mixin-name(!param, !param2 = 10)
{
	color:!param;
	border:!param2 solid !param;
}</code></pre>

		<p>Then you assign it to a selector:</p>
		
		<pre><code>#id 
{
	+mixin-name(#eee);
	padding:10px;
}</code></pre>

		<p>You can use mixins to intelligently extend other property groups:</p>
		
		<pre><code>=box(!color)
{
	padding:10px;
	border:1px solid !color;
	color:!color;
}

=error
{
	+box(red);
	background:red;
}

=alert
{
	+error;
	background:yellow;
}</code></pre>

		<p>Now when you change the 'box' style, you change it everywhere. The organisational benefits of using mixins are huge. You can keep all of your styles for a particular element in one spot, and simply extend properties where needed. This makes your CSS file much more manageable.</p>
		
		<p>Mixins don't need parameters either, as shown above.</p>
		
		<h4 id="ready-to-go">Built-in Mixins</h4>
		
		<p>Scaffold comes with many mixins already made for you to abstract the more tedious elements of CSS. For example, clearing a container is usually done like this:</p>
		
		<pre><code>.clearfix
{
	zoom:1;
	display:block;
}

.clearfix:after 
{
	content:'\\0020';
	display:block;
	height:0;
	clear:both;
	visibility:hidden;
	overflow:hidden;
	font-size:0;
}</code></pre>

		<p>You might add every selector to this as you go, making the selector very long, not to mention you need to remember it everytime. Scaffold makes it easy.</p>
		
		<pre><code>#id { +clearfix; }</code></pre>
		
		<p>That's it. Just add the clearfix mixin to any selector you want to add it to. Scaffold will do the rest of the work of adding the properties so you don't need to remember it in every project.</p>
		<p> It also adds more meaning to your CSS - people who read your stylesheets will know exactly what's going on. Using the first method, it's messy and there's no real meaning behind all of the those properties, people might not know what they're doing.</p>
		
		<p>Using mixins, you get the benefit of adding semantics to your CSS and making it much cleaner at the same time.</p>
		
				
		<p><strong>Some</strong> of the included mixins:</p>
		
		<ul>
			<li>+border-radius</li>
			<li>+box-shadow</li>
			<li>+clearfix</li>
			<li>+has-layout</li>
			<li>+container</li>
			<li>+opacity</li>
			<li>+move</li>
			<li>+reset</li>
		</ul>
		
		<h5><a href="">Full list of included mixins &rsaquo;</a></h5>
		
		<p>All of the included mixins are stored in <code>scaffold/framework/mixins</code>. Have a look through those files to see all of the mixins you have in your toolkit, or <a href="#included-mixins">read through the complete list</a> here in the documentation.</p>

		<h4 id="nested-selectors">Nested Selectors</h4>
		<p>By nesting selectors, you save yourself from writing the same code over and over and it ultimately makes your CSS much easier to read and navigate. It's pretty simple:</p>
		
		<pre><code>#id
{
	color:#000;
	padding:10px;
	
	a 
	{ 
		color:#ff0;
		
		&:hover { text-decoration:underline; } 
	}
	
	h1,h2,h3,h4,h5,h6
	{
		border-bottom:1px solid #eee;
		padding-bottom:10px;
		
		&:first-child { margin-top:0; }
	}
	
}</code></pre>

		<p>This will render this CSS:</p>
		
		<pre><code>#id { color:#000; padding:10px;}
#id a { color:#ff0; }
#id a:hover { text-decoration:underline; }
#id h1, #id h2, #id h3 { border-bottom:1px solid #eee; padding-bottom:10px; }
#id h1:first-child, #id h2:first-child ... { margin-top:0; }
</code></pre>

		<p>It's fairly obvious which is easier to read and update.</p>
		
		<div class="success">
		<h5>Using the parent element - <code>&amp;</code></h5>
		<p>One thing you'll notice above is that you can use the <code>&amp;</code> to reference the parent element. So in the case of <code>&:hover</code> above, it's really saying <code>a:hover</code>.
		</p>
		</div>
		
		<h4 id="output">Custom Output</h4>
		
		<p>Custom output allows modules and plugins to output something other than the CSS file. It could be a layout test page, test markup, validation etc.</p>
		
		<div class="notice">
			<p>Custom output will not work when in production mode.</p>
		</div>

		<h5 id="typography">Typography test suite - <a href="stylesheets/master.css/typography/">Example</a></h5>
		<p>The typography test suite creates a page with nearly every html element which is attached to the processed CSS. This allows you to easily see what you're styles look like, without writing a single line of html.</p>
		<pre><code>http://localhost/css/screen.css/typography/</code></pre>

		<h5 id="validate">Validate - <a href="stylesheets/master.css/validate/">Example</a></h5>
		<p>The validate module allows you to validate your CSS using the W3C validator before sending it to the browser. To use it, simply add <code>validate</code> to your URL. Instead of displaying your CSS, it will display any validate errors. Eg:</p>
		<pre><code>http://localhost/css/screen.css/validate/</code></pre>
		
		<h5 id="grid">Grid - <a href="stylesheets/master.css/grid/">Example</a></h5>
		<p>If you're using the Layout module, you can view your grid as output, so you can test it out and adjust it to your liking.</p>
		<pre><code>http://localhost/css/screen.css/grid/</code></pre>

		<h3 id="plugins">Plugins</h3>
		
		<p>Scaffold can use plugins to extend it's capabilities even further. Plugins are extremely easy to create and have all of the same functionality as the built-in modules. </p>
		
		<h5>Enabling a plugin</h5>
		
		<p>To enable a plugin, place the folder in the scaffold/plugins/ directory and add the plugin name to your config.php file in the <code>plugins</code> array. These two plugins are included for you.
		
		<h4 id="image-replace">Image Replace</h4>
		
		<p>The image replace plugin creates a new property called <code>image-replace</code>. You simply give it a url, like a normal image in CSS, and it will image replace that element for you. Here's an example:</p>
		
		<pre><code>#id 
{
	image-replace:url(/path/to/image.png);
}</code></pre>

		<p>Which might render this:</p>
		
		<pre><code>#id
{
	text-indent:-9999px;
	height:20px;
	width:240px;
	background:url(/path/to/image.png) no-repeat 0 0;
	display:block;
	overflow:hidden;
}</code></pre>
	
		<p>It finds the image and does all of the hard work for you.</p>
		
		<h4 id="xmlconstants">XML Constants</h4>
		
		<p>This plugin allows you to set constants using an XML file. You simply edit the config in scaffold/plugins/xml_constants/config.php with the path to an xml file that looks like this:
		
		<pre><code>&lt;?xml version="1.0" ?>
&lt;constants>

	&lt;constant>
		&lt;name>Foo&lt;/name>
		&lt;value>Bar&lt;/value>
	&lt;/constant>

&lt;/constants></code></pre>

		<p>You can then use the constant <code>!Foo</code> in your CSS. This becomes useful when you use a CMS, like ExpressionEngine or Wordpress, to set constants for categories or tags, then you can use them in your CSS to style your pages.</p>
		
		<h2 id="advanced">Advanced Usage</h2>
				
		<h3 id="structure">Folder structure and files</h3>
		<p>Getting to know the Scaffold folder structure will make it easier to understand and will make the learning curve a little bit smaller.</p>
		<p>Inside the <code>scaffold</code> directory you have:</p>

		<dl>
			
			<dt>cache</dt>
			<dd>This stores the parsed CSS files and any other files created by Scaffold</dd>
			
			<dt>example-config.php</dt>
			<dd><strong>Rename this to config.php. </strong>The global configuration for Scaffold. You can activate plugins in here and turn a lot of functionality on and off. Be sure to have a good look through here before anything else to customize Scaffold.</dd>
			
			<dt>css.htaccess</dt>
			<dd>
				<p>This .htaccess file should be placed in your CSS directory and renamed to just .htaccess. The lines pointing to the scaffold folder will need to be adjusted manually if you don't keep Scaffold in your CSS directory.</p>
			</dd>
			
			<dt>framework</dt>
			<dd>
				<p>This stores all of the templates, mixins, behaviours and grids used by Scaffold. The system may open up the functionality, but the framework folder is what gives you a jump-start when building your CSS. Take a look through this folder and take note of the Mixins you can use in your projects.</p>
				<p>Any CSS file placed in the mixins directory is automatically included (you can turn this off), but not all mixins have to reside in here.</p>
			</dd>
			
			<dt>index.php</dt>
			<dd>The front controller for Scaffold. CSS is passed to this file via the request POST variable. You can set paths to Scaffold folders in here if you need to.</dd>
			
			<dt>plugins</dt>
			<dd>You can create plugins for Scaffold and drop them in here. There are a few plugins in there already to give you an idea of what you can do.</dd>
			
			<dt>system</dt>
			<dd>Scaffold core functionality resides in here. You shouldn't need to change anything in here. </dd>
		
		</dl>
		
		<h3 id="config">Configuration Options</h3>
		
		<p>Scaffold's configuration is stored in <code>scaffold/config.php</code> and it's paths and constants are stored in <code>scaffold/index.php</code>.</p>
			
		<dl>
			<dt>INSTALL</dt>
			<dd>Setting this to true will cause Scaffold to run the path checking script rather than outputting the file. This will check all of your paths and make sure Scaffold can find them all.</dd>
			
			<dt>IN_PRODUCTION</dt>
			<dd>Setting this to true will cause no errors to be thrown and the CSS will never be recached. This overrides the cache_lock.</dd>
			
			<dt>cache_lock <code>boolean</code></dt>
			<dd>Scaffold recaches the CSS file if the CSS has been changed, however, you can lock the cache so it is never recached. You usually do this when you send your site to production.</dd>
			
			<dt>always_recache <code>boolean</code></dt>
			<dd>If you want Scaffold to always recache the files, set this to true. Scaffold doesn't know if included files have been changed, so if you're working on an included file, and refresh the main CSS file, it won't recache. To counter this, turn this option on. It's useful to have this on during development. However, you can use the <a href="#url-params"><code>recache</code> url parameter</a> instead.</dd>
			
			<dt>show_header <code>boolean</code></dt>
			<dd>Scaffold outputs some information at the top of the CSS file, you can turn this on or off.</dd>
			
			<dt>auto_include_mixins <code>boolean</code></dt>
			<dd>Scaffold automatically includes all the CSS files inside <code>scaffold/framework/mixins</code> so that you don't have to include them one-by-one yourself. Turning this off can speed up Scaffold.</dd>
			
			<dt>override_import <code>boolean</code></dt>
			<dd>The syntax for including files is <code>@include</code>, however, if you'd rather just use the standard <code>@import</code>, turn this on.</dd>
			
			<dt>absolute_urls <code>boolean</code></dt>
			<dd>If your calling Scaffold using the second method, that is, manually passing the CSS files through the index.php, the browser will assume all image paths are relative to the index.php file. You can either move the index.php file and change the paths, or set this to true. All image paths will be renamed to absolute URL's. This is not 100% effective.</dd>
			
			<dt>use_css_constants <code>boolean</code></dt>
			<dd>Scaffold uses the SASS syntax for constants <code>!constantname</code>, however, if you'd like to use a syntax which is closer to that of the proposed CSS constants, use can turn this on. Constants are then used <code>const(constantname)</code></dd>
			
			<dt>minify_css <code>boolean</code></dt>
			<dd>You can use the minify library to compress your CSS. Minify strips all unnecessary whitespace, empty and redundant selectors etc. By setting this to false, instead of minifying your CSS, it will prettify it, making it easier to read instead of worrying about compressing down the size.</dd>
			
			<dt>constants <code>array</code></dt>
			<dd>An array of values for creating CSS constants. This is useful for constants that you can't set in the CSS, for example, PHP version or SERVER info.</dd>
			
			<dt>debug</dt>
			<dd>Turn on FirePHP log and errors messages. These message will output to Firebug.</dd>
			
			<dt>language</dt>
			<dd>Set the language. Scaffold comes with English only at the moment.</dd>
			
			<dt>plugins</dt>
			<dd>Plugins are activated by adding them to this array and setting their value to true.</dd>
			
			
		</dl>
		
		<h4 id="paths">Custom Paths</h4>
		
		<p>You can change the paths for Scaffold so you can customize the folder structure to work with your server setup. The paths you can change in index.php are:</p>
		
		<div class="notice">
			<p>All of these paths are relative to index.php. If you move index.php, you'll need to change these paths accordingly.</p> 
		</div>
		
		<dl>
			<dt>document_root</dt>
			<dd>Set the document root of your server using the full server path. You should only need to set this if your server doesn't support <code>$_SERVER['DOCUMENT_ROOT']</code></dd>
			
			<dt>css</dt>
			<dd>The path to the CSS directory. This can be relative to the index.php file, or from the document root.</dd>
			
			<dt>scaffold</dt>
			<dd>The path to the scaffold directory. This is either relative to the index.php file, or from the document root.</dd>
			
			<dt>system</dt>
			<dd>The path to the system directory inside the scaffold folder. Can be relative to the index.php or from the document root.</dd>
			
			<dt>cache</dt>
			<dd>The path to the cache directory inside the scaffold folder. Can be relative to the index.php or from the document root.</dd>
			
			<dt>plugins</dt>
			<dd>The path to the plugins directory inside the scaffold folder. Can be relative to the index.php or from the document root.</dd>

		</dl>
		
		<h3 id="layout">Layout Module</h3>
		
		<p>The layout module allows you to create layouts with pure CSS very quickly. It generates grid structures similar to Blueprint and 960.gs. The difference is that you can make the grid just about anything you want.</p>
		<p>If you&#8217;ve used either of these CSS frameworks before, you&#8217;ll already know how you can create layouts by using classes, like so:</p>
		
		<pre><code>&lt;div class="columns-12 last">&lt;/div></code></pre>
		
		<p>Creating layouts this way is very easy, but it&#8217;s not entirely semantic - we want to try and remove as much of this style from the markup as possible. The layout module, along with mixins, solves this problem.</p>
		
		<h4>Using the layout module</h4>
		
		<p>To use the layout module, all you have to do is setup your grid inside your CSS:</p>
		
		<pre><code>@grid
{
    column-width:60;
    column-count:12;
    left-gutter-width:10;
    right-gutter-width:10;
    baseline:18;
}</code></pre>
		
		<p>This particular @grid creates the 960.gs style of grid. Each column (which is usually a div), has a left and right margin of 10px and a single column is 60px wide.</p>
		
		<h5>Test out your grid</h5>
		
		<p>To see your new grid, locate your CSS file in the browser, and add /grid/ to the end of the url like so:</p>
		
		<pre><code>http://scaffold/stylesheets/master.css/grid/</code></pre>
		
		<p>You should see a page will red columns cascading down the page. Each of these columns has been created dynamically. Change the @grid settings and refresh this page to see the changes.</p>
		
		<h5>Background Grid Image</h5>
		
		<p>By adding the grid settings, you also have access to a class which is automatically added to your CSS called <code>.showgrid</code>. Adding this class to an element which display the grid behind it for reference. </p>
		
		<h4>Grid Classes</h4>
		
		<p>Like I mentioned earlier, if you&#8217;ve used 960.gs or Blueprint, you&#8217;ll know how these frameworks use pre-built grid classes to build layouts. Scaffold creates these for you as well. You have:</p>
		
		<dl>		
			<dt>.columns-#</dt>
			<dd>Makes the div sddan a ddarticular number of columns across</dd>
			
			<dt>.push-# and .pull-#</dt>
			<dd>Moves the column across the grid x number of columns</dd>
			
			<dt>.append-# and .prepend-#</dt>
			<dd>Adds x number of columns to the total width of the column</dd>
			
			<dt>.baseline-#</dt>
			<dd>Makes the column x baseline heights high.</dd>
			
			<dt>.baseline-up-# and .baseline-down-#</dt>
			<dd>Moves the column udd and down the baseline</dd>
			
			<dt>.container</dt>
			<dd>Creates a container for grid columns which sits in the middle of the page and spans the total width of the grid.</dd>
			
			<dt>.first and .last</dt>
			<dd>Removes left or right margin respectively.</dd>
		</dl>
		
		<h4>Grid Mixins</h4>
		
		<p>Scaffold creates mixins which mirror all of the classes above. In fact, the mixins actually create those classes. You can see this in action in <code>scaffold/framework/mixins/layout/grid.css.</code></p>
		<p>These allow you to do exactly the same thing as the classes above, but you don&#8217;t have to litter your markup. You do this instead:</p>
		
		<pre><code>#id
{
    +columns(4);
}</code></pre>
		
		<p>This div will span exactly 4 columns across and have all the properties needed to function as a column.</p>
		
		<p>For full reference as to what all the mixins do, become familiar with <code>scaffold/framework/mixins/layout/grid.css</code>. </p>
		
		
		<!--
		<h2 id="reference">Reference Guide</h2>
		
		<h3 id="builtin-mixins">Built-in Mixins</h3>
		
		<p>Scaffold comes with a lot of useful mixins already made for you. If
		
		<h4 id="css3"><span class="caps">CSS3</span></h4>


	<h5>Border-box</h5>


<pre><code>=border-box 
{
    -moz-box-sizing:border-box;
    -webkit-box-sizing:border-box;
    -ms-box-sizing:border-box;i
    box-sizing:border-box;
    behavior:url("!scaffold_url/modules/Mixins/support/behaviours/boxsizing.htc");
}</code></pre>

	<h5>Rotate</h5>


<pre><code>=rotate(!angle)
{
    -webkit-transform: rotate(#[!angle]deg);
    -moz-transform: rotate(#[!angle]deg);
    filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=#[90 / !angle]);
}</code></pre>

	<h5>Box-shadow</h5>


<pre><code>=box-shadow(!blur, !color, !x, !y)
{
    -moz-box-shadow:!x !y !blur !color;
    -webkit-box-shadow:!x !y !blur !color;
    box-shadow:!x !y !blur !color;
}</code></pre>

	<h5>Opacity</h5>


<pre><code>=opacity(!opacity = 1)
{
    opacity: !opacity;
    -moz-opacity: !opacity;
    -khtml-opacity: !opacity;
    -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=#[!opacity*100])";
    filter: "alpha(opacity=#[!opacity*100])";
}</code></pre>

	<h5>Border-radius</h5>


<pre><code>=border-radius(!radius)
{
    -webkit-border-radius:!radius;
    -moz-border-radius:!radius;
    border-radius:!radius;
}
</code></pre>

	<p>Also in

	<h5>Background-size</h5>


<pre><code>=background-size(!x, !y)
{
    -o-background-size:!x !y;
    -webkit-background-size:!x !y;
    -khtml-background-size:!x !y;
    -moz-background-size:!x !y;
}</code></pre>

	<h5>Text-overflow</h5>


<pre><code>=text-overflow(!type = "ellipsis")
{
    text-overflow:!type;
    -o-text-overflow:!type;
}</code></pre>

	<h5>Multi-column Layout</h5>


<pre><code>=column-width(!width)
{
    -moz-column-width:!width;
    -webkit-column-width:!width;
}

=column-count(!count)
{
    -moz-column-count:!count;
    -webkit-column-count:!count;
}

=column-rule(!color, !style, !thickness)
{
    -moz-column-rule:!color !style !thickness;
    -webkit-column-rule:!color !style !thickness;
}

=column-gap(!gap)
{
    -moz-column-gap:!gap;
    -webkit-column-gap:!gap;
}</code></pre>

	<h4>Font stacks</h4>


<pre><code>=times
{
    font-family: Times, Times New Roman, Georgia, serif;
}

=helvetica
{
    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
}

=myriad
{
    font-family: "MyriadPro", Myriad, "Helvetica Neue", Helvetica, Arial, sans-serif;
}

=baskerville
{
    font-family: Baskerville, Times New Roman, Times, serif;
}

=cambria
{
    font-family: Cambria, Georgia, Times, Times New Roman, serif;
}

=centurygothic
{
    font-family: Century Gothic, Apple Gothic, sans-serif;
}

=monospace
{
    font-family: "Espresso Mono", Consolas, Lucida Console, Monaco, monospace;
}

=franklingothic
{
    font-family: Franklin Gothic Medium, Arial Narrow Bold, Arial, sans-serif;
}

=futura
{
    font-family: Futura, Century Gothic, AppleGothic, sans-serif;
}

=garamond
{
    font-family: Garamond, Hoefler Text, Times New Roman, Times, serif;
}

=geneva
{
    font-family: Geneva, Verdana, Lucida Sans, Lucida Grande, Lucida Sans Unicode, sans-serif;
}

=georgia
{
    font-family: Georgia, Palatino, Palatino Linotype, Times, Times New Roman, serif;
}

=gillsans
{
    font-family: "Gill Sans", Calibri, Trebuchet, sans-serif;
}

=impact
{
    font-family: Impact, Haettenschweiler, Arial Narrow Bold, sans-serif;
}

=palatino
{
    font-family: Palatino, Palatino Linotype, Hoefler Text, Times, Times New Roman, serif ;
}

=tahoma
{
    font-family: Tahoma, Geneva, Verdana;
}

=trebuchet
{
    font-family: 'Trebuchet MS', Tahoma, Arial, sans-serif;
}

=warnock
{
    font-family: "Warnock Pro", "Goudy Old Style","Palatino","Book Antiqua", Georgia, serif;
}
</code></pre>

	<h4>Layout Helpers</h4>


	<h5>Float</h5>


<pre><code>=float(!side)
{
    float:!side;
    display:inline;
}</code></pre>

	<h5>Quick Clearfix</h5>


<pre><code>=quick-clearfix
{
    +has-layout; /* For IE6, the overflow is enough for IE7+ */
    overflow:hidden;
}</code></pre>

	<h5>Clearfix</h5>


<pre><code>=clearfix
{
    +has-layout;
    display:block;

    &#38;:after 
    {
        content:'\\0020';
        display:block;
        height:0;
        clear:both;
        visibility:hidden;
        overflow:hidden;
        font-size:0;
    }
}</code></pre>

	<h5>Inline-block</h5>


<pre><code>=inline-block
{
    zoom:1;
    display:inline; 
    display:-moz-inline-box; 
    display:inline-block; 
    vertical-align:top;
}</code></pre>

	<h5>Has-layout</h5>


<pre><code>=has-layout
{
    zoom:1;
}</code></pre>

	<h4>Typography Helpers</h4>


	<h5>Crisp</h5>


<pre><code>=crisp
{
    text-shadow:rgba(0,0,0,0.01) 0 0 0;
}</code></pre>

	<h5>Small-type</h5>


<pre><code>=small-type
{ 
    line-height: [floor(!baseline*4/5)]px; 
    font-size: 11px; 
}</code></pre>

	<h5>Regular</h5>


<pre><code>=regular
{
    font-weight:normal;
    font-style:normal;
}</code></pre>

	<h5>All-caps</h5>


<pre><code>=all-caps
{ 
    font-variant: small-caps; 
    letter-spacing: 1px; 
    text-transform: lowercase; 
    font-size:1.2em;
    line-height:1%;
    font-weight:bold;
    padding:0 2px;
}</code></pre>

	<h5>Link-info</h5>


<pre><code>=link-info
{
    &#38;[href^="http"]:after
    {
        margin:0 5px 0 0; font-family:"Zapf Dingbats"; content: "\279C";
    }

    &#38;[href$="pdf"]:after    { content: " (pdf)"; }  
    &#38;[href$=".doc"]:after    { content: " (doc)"; } 
    &#38;[href$=".zip"]:after    { content: " (zip)"; } 
}</code></pre>

	<h5>Drop-cap</h5>


<pre><code>=drop-cap
{  
    &#38;:first-letter
    {
        +baskerville;

        display:block;  
        margin:5px 0 0 5px;  
        float:left;    
        font-size:60px;   
    }
}</code></pre>

	<h5>Nice-amp</h5>


<pre><code>=nice-amp 
{ 
  +warnock; 
  font-style: italic;
  font-weight: normal;
}</code></pre>

	<h5>Image-replaced</h5>


<pre><code>=image-replaced
{
    display:block;
    text-indent:-9999px;
    background-repeat: no-repeat;
    background-position: 0 0;
    overflow:hidden;
}</code></pre>

	<h4>Other utilities</h4>


	<h5>Sharpen Image</h5>


<pre><code>=sharpen
{
    image-rendering:-moz-crisp-edges;
    -ms-interpolation-mode:nearest-neighbor;  /* IE 7+ */
}</code></pre>

	<h5>High Quality Images</h5>


<pre><code>=high-quality
{
    image-rendering:optimizeQuality;
    -ms-interpolation-mode:bicubic;  /* IE 7+ */
}</code></pre>

	<h5>Low Quality Images</h5>


<pre><code>=low-quality
{
    image-rendering:optimizeSpeed;
}</code></pre>

	<h5>Horizontal-list</h5>


<pre><code>=horizontal-list
{
    +reset;
      +clearfix;

    li
    {
        +no-bullet;
        +float(left);
        white-space:nowrap;
    }
}</code></pre>

	<h5>No Bullet</h5>


<pre><code>=no-bullet
{
    list-style-type:none;
    margin-left:0;
}</code></pre>

	<h5>No Bullets</h5>


<pre><code>=no-bullets
{
    li
    {
        +no-bullet;
    }
}</code></pre>

	<h5>Inherit Parents Styles</h5>


<pre><code>=inherit
{
    font:inherit;
    color:inherit;
    background:inherit;
    border:inherit;
    text-decoration:inherit;
    text-shadow:inherit;
    text-transform:inherit;
}</code></pre>

	<h5>Resets</h5>


<pre><code>=reset
{
    margin:0;
    padding:0;
}

=reset-box-model
{
    margin:0;
    padding:0;
    border:0;
}

=reset-all
{
    margin: 0;
    padding: 0;
    border: 0;
    background: none;
    font-weight: inherit;
    font-style: inherit;
    font-size: 100%;
    font-family: inherit;
    vertical-align: baseline;
}</code></pre>

	<h5>Hide but remain accessible</h5>


<pre><code>=hide
{
    position:absolute;
    top:-9999px;
    left:-9999px;
}</code></pre>

		-->
		
	</div>
	</div>
</body>
</html>