# jQuery outside events #
[http://benalman.com/projects/jquery-outside-events-plugin/](http://benalman.com/projects/jquery-outside-events-plugin/)

Version: 1.1, Last updated: 3/16/2010

With jQuery outside events you can bind to an event that will be triggered only when a specific "originating" event occurs *outside* the element in question. For example, you can click outside, double-click outside, mouse-over outside, focus outside (and over ten more default "outside" events). Also, if an outside event hasn't been provided by default, you can easily define your own.

Please note that because a reference to the originating event's element is available as event.target you can change behavior based on which element was actually interacted with.

Visit the [project page](http://benalman.com/projects/jquery-outside-events-plugin/) for more information and usage examples!


## Documentation ##
[http://benalman.com/code/projects/jquery-outside-events/docs/](http://benalman.com/code/projects/jquery-outside-events/docs/)


## Examples ##
These working examples, complete with fully commented code, illustrate a few
ways in which this plugin can be used.

[http://benalman.com/code/projects/jquery-outside-events/examples/clickoutside/](http://benalman.com/code/projects/jquery-outside-events/examples/clickoutside/)  
[http://benalman.com/code/projects/jquery-outside-events/examples/dblclickoutside/](http://benalman.com/code/projects/jquery-outside-events/examples/dblclickoutside/)  
[http://benalman.com/code/projects/jquery-outside-events/examples/mouseoveroutside/](http://benalman.com/code/projects/jquery-outside-events/examples/mouseoveroutside/)  
[http://benalman.com/code/projects/jquery-outside-events/examples/focusoutside/](http://benalman.com/code/projects/jquery-outside-events/examples/focusoutside/)  


## Support and Testing ##
Information about what version or versions of jQuery this plugin has been
tested with, what browsers it has been tested in, and where the unit tests
reside (so you can test it yourself).

### jQuery Versions ###
1.4.2

### Browsers Tested ###
Internet Explorer 6-8, Firefox 2-3.6, Safari 3-4, Chrome, Opera 9.6-10.1.

### Unit Tests ###
[http://benalman.com/code/projects/jquery-outside-events/unit/](http://benalman.com/code/projects/jquery-outside-events/unit/)


## Release History ##

1.1 - (3/16/2010) Made "clickoutside" plugin more general, resulting in a whole new plugin with more than a dozen default "outside" events and a method that can be used to add new ones.  
1.0 - (2/27/2010) Initial release  


## License ##
Copyright (c) 2010 "Cowboy" Ben Alman  
Dual licensed under the MIT and GPL licenses.  
[http://benalman.com/about/license/](http://benalman.com/about/license/)
