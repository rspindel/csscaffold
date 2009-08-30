#CSScaffold

A dynamic CSS framework inspired by Shaun Inman's CSS Cacheer. It's aimed at experienced CSS developers - it gives you the tools to create great CSS easily. It abstracts some repetitive and annoying flaws of the language to make it easier to create and maintain, all while giving you the benefits of caching.

- Constants
- SASS-style mixins
- Compressed, Cached and Gzipped on-the-fly
- Nested Selectors
- Perform PHP operationså
- Image replace text by just linking to the image file
- Plus easily add your own functionality using the plugin system

##New in 1.4

- Exception handling so you can see exactly what went wrong and how to fix it. This should help out people who receive errors but have no idea what actually went wrong. This includes checks for missing constants, mixins, css files etc.
- Language files now control the output of text. This means you can translate Scaffold into other languages.
- Included all of the plugins, rather than keeping them separate
- New Plugin - Validate. Validates your CSS using the W3C CSS validator. Only turn this on when you need it :)
- Install file so you can check your paths. To use the install file, turn it on in index.php and navigate to the scaffold folder in a browser. 
- New way to use the global config (Kohana dot-notation style)
- Slight folder change in the repo. Moved the folders into a css directory to give a strong indication of where they should go
- Various bug fixes and enhancements thanks to Kohana.

##What you need

- PHP5+
- modrewrite enabled in Apache (optional)

##Quick Installation

1. Download the latest release of Scaffold. 
2. Place all the files **inside your css directory on your webserver**. 
3. Rename example.htaccess to just .htaccess
4. Change any configuration options in scaffold/config.php
5. In scaffold/index.php change the INSTALL parameter to TRUE
5. Navigate to the scaffold folder in a browser eg http://localhost/css/scaffold/ to run the installer which checks your paths
6. If all is well, rename or remove the install.php file and change the INSTALL parameter back to FALSE

Any css files within this css directory will now be parsed by Scaffold automatically. 

##Available Plugins

Some of the plugins available are:

- Layout - Create 960.gs style grids with Mixins and classes.
- OOCSS - Extend one selector using another selector
- Browsers - Target specific browsers
- Icy Compressor -  An alternative to Minify

See the wiki for more information.

##Having trouble?

Make sure you read the documentation on the wiki. If you find a bug, put it in the issues section on Github. If you're still having trouble, feel free to contact me at csscaffold@me.com. 

##License

Copyright (c) 2009, Anthony Short <csscaffold@me.com>
http://github.com/anthonyshort/csscaffold
All rights reserved.

This software is released under the terms of the New BSD License.
http://www.opensource.org/licenses/bsd-license.php