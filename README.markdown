#CSScaffold

A dynamic CSS framework inspired by Shaun Inman's CSS Cacheer. It's aimed at experienced CSS developers - it gives you the tools to create great CSS easily. It abstracts some repetitive and annoying flaws of the language to make it easier to create and maintain, all while giving you the benefits of caching.

- Constants
- SASS-style mixins
- Compressed, Cached and Gzipped for speedy download
- Nested Selectors
- Easy-to-use layout system
- Utility classes and mixins to get you started
- Image replace text by just linking to the image file
- CSSTidy your CSS on the fly
- And more... 
- Plus easily add your own functionality using the plugin system

##Quick-start

The main folder is the Scaffold folder. The included CSS folder includes some templates you can use and generally shows how to use Scaffold. Feel free to use these stylesheets in your projects. 

Scaffold works inside your CSS directory, so take the /scaffold folder and drop it in your css directory. Also take the .htaccess if you want it.

The @stylesheets@ folder and @master.css@ are examples for you to work from. Remember, Scaffold only works from within your css directory.

This is how you link to your css so that it is parsed by CSScaffold. You can also setup a .htaccess file to take care of this for you and include them as you normally would. See the example.htaccess in /css/

This:

	<link rel="stylesheet" href="css/master.css" type="text/css" media="screen" />

Becomes this:

	<link rel="stylesheet" href="/scaffold/index.php?request=css/master.css&recache" type="text/css" media="screen" />

Note: Use 'recache' in the url to force it to recache the css everytime. This is useful during development. 

##License

Copyright (c) 2009, Anthony Short <anthonyshort@me.com>
http://github.com/anthonyshort/csscaffold
All rights reserved.

This software is released under the terms of the New BSD License.
http://www.opensource.org/licenses/bsd-license.php