# Layout

This plugin for [CSScaffold](http://github.com/anthonyshort/csscaffold/tree) gives you additional mixins, constants and classes for you to recreate a 960.gs style grid.css with only a few lines of code describing your grid. 

## Installation

Place the whole Layout folder into scaffold/plugins and you're ready to go. If you've downloaded the directly from GitHub, rename the folder that is downloaded to "Layout" and place it in the correct folder.

The folder structure should look like this:

<pre>
Layout/
	Layout.php
	support/
</pre>

## Usage

First, you set up your grid to activate the plugin.

<pre><code>@grid
{
	column-width:60;
	column-count:12;
	left-gutter-width:10;
	right-gutter-width:10;
	baseline:20;
	unit:px;
}
</code></pre>

This sets up and enables the layout plugin. It needs these settings. Left gutter and right gutter are optional. You can use both, just one, or none. The rest of the settings are required.

To create all of the grid classes (.columns-1, .columns-2 etc), add this anywhere inside your CSS (not inside a selector or property). It is replaced with all of the layout classes.

<pre><code>+grid-classes;</code></pre>

You can use the classes to create a layout, or use mixins. Whichever is easiest for you. The classes you can use are:

<ul>
<li>.columns-x</li>
<li>.push-x</li>
<li>.pull-x</li>
<li>.baseline-x</li>
<li>.baseline-up-x</li>
<li>.baseline-down-x</li>
<li>.append-x</li>
<li>.prepend-x</li>
</ul>

And the mixins you can use are:

<ul>
<li>grid-classes</li>
<li>container</li>
<li>container-alt (includes gutters into the overall width like 960.gs)</li>
<li>baseline-up(!x)</li>
<li>baseline-down(!x)</li>
<li>baseline(!x)</li>
<li>span(!x)</li>
<li>columns(!x)</li>
<li>push(!x)</li>
<li>pull(!x)</li>
<li>append(!x)</li>
<li>prepend(!x)</li>
<li>first</li>
<li>last</li>
</ul>

You can also use these constants:

<ul>
<li>column-width</li>
<li>column-count</li>
<li>gutter-width</li>
<li>left-gutter-width</li>
<li>right-gutter-width</li>
<li>baseline</li>
<li>grid-width</li>
</ul>

<h2>Grid Image</h2>

A class is automatically added to your css called <code>.showgrid</code>. This is dynamically generated grid image that you can give to your layout elements to display the grid as a background.

<h2>Examples</h2>

<pre><code>
@grid
{
	column-width:60;
	column-count:12;
	left-gutter-width:10px;
	right-gutter-width:10px;
	baseline:20px;
	unit:px;
}

.container 
{ 
	+container; 
}

+grid-classes;

#page
{
	#header 		{ +columns(12); }
	#content 		{ +columns(6); }
	#sub-content 	{ +columns(3); }
	#aside 			{ +columns(3); }
	#footer 		{ +columns(12); }
}
</code></code>