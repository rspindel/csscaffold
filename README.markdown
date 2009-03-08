#CSScaffold

A dynamic CSS framework built on top of Shaun Inman's CSS Cacheer. It's aimed at experienced CSS developers - it gives you the tools to create great CSS easily. It abstracts some repetitive and annoying flaws of the language to make it easier to create and maintain, all while giving you the benefits of caching.

The majority of this work uses Shaun Inman's CSS Cacheer, which gave us the ability to use:

- Constants
- Base a selector on another selector
- Embed images in your CSS using Base64 to save http requests
- Cached and Gzipped for speedy download
- Nested Selectors

So all credit goes to Shaun for the initial concept and code. I've extended and modified Cacheer to also allow you to:

- Assign classes to selectors within your CSS
- Easy grid layout system
- Generated and included utility classes
- Easy image replacement
- Tidy and Compress your CSS on the fly
- Form Framework for building forms quickly
- Global reset
- Development styles for debugging and testing
- Module-based css, broken up into common areas.