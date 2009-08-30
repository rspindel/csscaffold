# OOCSS

This plugin for (CSScaffold)[http://github.com/anthonyshort/csscaffold/tree] lets you use, object-oriented methods of creating CSS files. It lets you extend CSS selectors with other selectors. 

This plugin is under development, and might be a bit buggy in very complex situations. 

## Installation

Place the whole OOCSS folder into scaffold/plugins and you're ready to go. If you've downloaded the directly from GitHub, rename the folder that is downloaded to "Browsers" and place it in the correct folder.

The folder structure should look like this:

<pre>
OOCSS/
	OOCSS.php
</pre>

## Usage

Example:

<pre><code>.box
{
	padding:10px;
}

.box-2
{
	extends:".box";
	background:#eee;
}
</code></pre>

The selector can be anything you want it to be and it will find and mimic that selector through the CSS file.