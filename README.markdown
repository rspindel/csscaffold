#CSScaffold

A dynamic CSS framework inspired by Shaun Inman's CSS Cacheer. It's aimed at experienced CSS developers - it gives you the tools to create great CSS easily. It abstracts some repetitive and annoying flaws of the language to make it easier to create and maintain, all while giving you the benefits of caching.

- Constants
- SASS-style mixins
- Compressed, Cached and Gzipped on-the-fly
- Nested Selectors
- Perform PHP operationså
- Image replace text by just linking to the image file
- Plus easily add your own functionality using the plugin system

##What you need

- PHP5+
- modrewrite enabled in Apache (optional)

##Quick Installation

1. Download the latest release of Scaffold. 
2. Place all the files **inside your css directory on your webserver**. 
3. Rename example.htaccess to just .htaccess
4. Change any configuration options in scaffold/config.php

Any css files within this css directory will now be parsed by Scaffold automatically. 

##Available Plugins

Some of the plugins available are:

- [Layout](http://github.com/anthonyshort/Layout/tree/master) - Create 960.gs style grids with Mixins and classes.
- [OOCSS](http://github.com/anthonyshort/Extends/tree/master) - Extend one selector using another selector
- [Browsers](http://github.com/anthonyshort/Browsers/tree/master) - Target specific browsers
- [Icy Compressor](http://github.com/anthonyshort/Icy/tree/master) -  An alternative to Minify

##Having trouble?

Make sure you read the documentation on the wiki. If you find a bug, put it in the issues section on Github. If you're still having trouble, feel free to contact me at csscaffold@me.com. 

##License

Copyright (c) 2009, Anthony Short <csscaffold@me.com>
http://github.com/anthonyshort/csscaffold
All rights reserved.

This software is released under the terms of the New BSD License.
http://www.opensource.org/licenses/bsd-license.php