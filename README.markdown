#CSScaffold

**Requires PHP**

A dynamic CSS framework built on top of Shaun Inman's CSS Cacheer. Favouring convention over configuration, it aims to speed up development time by reducing the number of times you need to repeat yourself. It extends into the markup to make sure everything is consistent. By standardizing the markup, it makes it extremely easy to create templates and frameworks for common items.

- Constants
- Base a selector on another selector
- Assign classes to selectors within your CSS (In development)
- Easy grid layout system (no more floats or positioning - we use columns instead)
- Generated and included utility classes
- Easy image replacement (using image-replace:url('url');)
- Embed images in your CSS using Base64 to save http requests
- Tidy and Compress your CSS on the fly
- Cached and Gzipped for speedy download
- Nested Selectors
- Form Framework for building forms quickly
- Global reset
- Development styles for debugging and testing
- Module-based css, broken up into common areas.

##Installation

To install Scaffold, all you need to do is place everything into your servers www directory or root directory. You also need to make sure these files are set to CHMOD 777 (Read and Write):

- /assets/images/*
- /assets/snippets/
- /system/cache

If you want a speedy setup, just make css/ and everything in it read/write so you can have a play with it quicker. I wouldn't do this on a live site though...

##Remember:

- You can delete the docs folder and README.markdown
- I'm still writing the docs for this, so you may have trouble figuring it out until then
- Paths are always relative to screen.css, even in deeper folders, if they are imported.