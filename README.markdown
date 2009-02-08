#CSScaffold

**Requires PHP**

A dynamic CSS framework built on top of Shaun Inman's CSS Cacheer. Favouring convention over configuration, it aims to speed up development time by reducing the number of times you need to repeat yourself.

The majority of this work uses Shaun Inman's CSS Cacheer, which gave us the ability to use:

- Constants
- Base a selector on another selector
- Embed images in your CSS using Base64 to save http requests
- Cached and Gzipped for speedy download
- Nested Selectors

So all credit goes to Shaun for the initial concept and code. I've extended and modified Cacheer to also allow you to:

- Assign classes to selectors within your CSS (In development)
- Easy grid layout system (no more floats or positioning - we use columns instead)
- Generated and included utility classes
- Easy image replacement (using image-replace:url('url');)
- Tidy and Compress your CSS on the fly
- Form Framework for building forms quickly
- Global reset
- Development styles for debugging and testing
- Module-based css, broken up into common areas.

##Installation

To install Scaffold, all you need to do is place everything into your servers www directory or root directory. You also need to make sure these files are set to CHMOD 777 (Read and Write):

- /assets/*
- /system/cache

If you want a speedy setup, just make css/ and everything in it read/write so you can have a play with it quicker. I wouldn't do this on a live site though...

###Remember:

- You can delete the docs folder and README.markdown
- I'm still writing the docs for this, so you may have trouble figuring it out until then
- Paths are always relative to screen.css, even in deeper folders, if they are imported.

###Todo:

- Update forms framework. Dropping IE6 support (it's about time) so we can use input[type='blah'] and more now.
- Finish off the documentation and tests
- Finish the printed styles

##Usage

After you've got Scaffold up and running on your server, you probably want to know what you can do with it. Scaffold comes prepackaged with templates and frameworks to make every project much easier to get started with. Some of these are empty files, ready for you to get started working with, and some have been preloaded with the basic styles you need. The main focus on Scaffold is workflow - we want to make this as consistent and as fast as possible.

### Layouts

To make a layout, you need some settings:

	@grid 
	{
		column-width:23;
		gutter-width:18;
		column-count:24;
		baseline:18;
	}
	
Then you can use these settings throughout your CSS as variables.

	#id 
	{
		columns:12;
	}
	
	#id2
	{
		width: grid(12col);
	}
	
	#id3 
	{
		margin-bottom: grid(baseline);
	}
	
	#id4
	{
		padding: grid(gutter);
	}

The 3 last variables can be used anywhere, not just in these properties. The columns property creates everything for you - the float, the width, and the compatibility fixes. It also factors in padding and border. So you don't have to calculate widths. 

It should also be noted that the layout plugin also creates a grid.css, a grid.png and a layouts.xml file for you. These are similar to BlueprintCSS, but just dynamic. 

### Constants

Set up your constants

	@constants
	{
		color1: #ddd;
		blackborder: #000 1px solid;
	}
	
Then call them anywhere
	
	#id
	{
		color: const(color1);
		border: const(blackborder);
	}
	
### Bases

Create property sets to give to other classes

	@base(myBase)
	{
		color:#fff;
		background:#000;
		margin:10px;
	}
	
Then call it:

	#id 
	{
		based-on: base(myBase);
	}
	
### Automatic Browser Feeding

Scaffold creates many different cache files for different browsers. In the *specific* folder, you'll see 3 files:

- ie7.css
- gecko.css
- webkit.css

If Scaffold detects these browsers, it attaches the appropriate file to your standard css, and gives it to the browser.

### Mimic Selectors

You can make selectors mimic other selectors:

	#id
	{
		add-to: selector('#id2');
	}
	
	#id2 
	{
		color:#fff;
	}
	
	#id3, #id4, #id2
	{
		padding:5px;
	}
	
Becomes

	#id
	{
		add-to: selector('#id2');
	}
	
	#id2, **#id**
	{
		color:#fff;
	}
	
	#id3, #id4, #id2, **#id**
	{
		padding:5px;
	}

It only mimics the specific selector you choose, for example, it wouldn't add to this selector:

	#id5 > #id2
	{
		margin:10px;
	}
	
That would require this:

	#id
	{
		add-to: selector('#id2'), selector('#id5 > #id2') ;
	}
	
### CSS3 Helper (WIP)

A helper plugin to make adding some CSS3 support easier.

#### @font-face

This section is still a work in progress. Any file you add to assets/fonts will have the @font-face rule automatically generated for you. This will eventually, hopefully, include PHP generate font backups.

#### border-radius

Firefox and Safari have slightly different syntax for border radius, Scaffold will figure this out for you, so you can write it as normal CSS3:

	#id
	{
		border-radius:5px;
	}
	
	#id2
	{
		border-radius-topleft:5px;
	}
	
etc. 

### CSSTidy

Scaffold has CSSTidy built in which optimizes and compresses your CSS. It puts duplicate selectors together, removes white-space, removes the last ;, and much more. It functionality can be set in config.php

### Image Replacement

You can easily image replace titles by using this syntax:

	#id
	{
		image-replace: 'image-name';
	}
	
This image replaces for everything but Safari 3.1 and FF3.1 which will fall back on the font as you can use @font-face.

If you don't want to fall back on @font-face, then you can use the dynamically generated class to force it to replace. 

	#id
	{
		add-to: selector('.ir-image-name');
	}
	
### Math

You can perform simple math equations:

	#id
	{
		padding: math('10 * 2')px;
	}
	
Or mix and match

	#id
	{
		padding: math('(grid(baseline) * 2) + grid(gutter) - const(pageMargin)');
	}
	
### Nested Selectors

You can nest selectors to make complex cascading simpler to write:

	#id
	{
		color:#fff;
		
		#id2
		{
			background:#000;
		}
	}
	
Outputs:

	#id { color:#fff; }
	#id #id2 { background:#000; }
	
### Server Import

Rather then relying on CSS's native import, you can use @server-import to bring it in at any stage, anywhere in your CSS and parse it through Scaffold

	@server import url('assets/snippets/reset.css');