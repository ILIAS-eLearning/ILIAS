Dynamic max height plugin for jQuery
========================================
This is a jQuery plugin to dynamically check a layer height and compare it to a custom height value.

[![Preview](http://joanclaret.github.io/jquery-dynamic-max-height/img/github-cover.png)](http://joanclaret.github.io/jquery-dynamic-max-height/)

- Configure `max-height` via data attribute
- Button appears only in if `item-height` > `max-height`
- Configure "show more / show less" messages via data attributes
- Animate via CSS transitions (best performance)

Online demo
-----------
[DEMO](http://joanclaret.github.io/jquery-dynamic-max-height)

How to use?
-----------

### Javascript
Include the ```jquery.dynamicmaxheight.min.js``` before your ```</body>``` tag and initialise it:

```html
 <script src="path/to/file/jquery.dynamicmaxheight.min"></script>
 <script>
    $('.dynamic-max-height').dynamicMaxHeight();
 </script>
```


### HTML
The plugin depends on the following HTML structure:

```html
<div class="js-dynamic-height" data-maxheight="70">
    <div class="dynamic-height-wrap">
      <p> My life fades. The vision dims. All that remains are memories. I remember a time of chaos... ruined dreams... this wasted land. But most of all, I remember The Road Warrior. The man we called "Max." To understand who he was, you have to go back to another time... when the world was powered by the black fuel... and the desert sprouted great cities of pipe and steel. Gone now... swept away. For reasons long forgotten, two mighty warrior tribes went to war, and touched off a blaze which engulfed them all. Without fuel they were nothing. They'd built a house of straw. The thundering machines sputtered and stopped. Their leaders talked and talked and talked. But nothing could stem the avalanche. Their world crumbled. </p>
    </div>
    <button class="js-dynamic-show-hide button" title="Show more" data-replace-text="Show less">Show more</button>
</div>
```

### CSS
Minimal CSS Rules for the plugin:

```css
.dynamic-height-wrap {
  overflow: hidden;
  position: relative;
  transition: max-height 0.25s ease-in-out;
  width: 100%;
}

/* Bottom gradient (optional, but recommended)*/
.dynamic-height-active .dynamic-height-wrap:before {
  background: linear-gradient(to bottom,  rgba(240,249,255,0) 0%,rgba(255,255,255,1) 100%);
  bottom: 0;
  content:'';
  height: 30px;
  left: 0;
  position: absolute;
  right: 0;
  z-index: 1;
}

.dynamic-height-active .dynamic-show-more {
  display: inline-block;
}

.dynamic-show-more {
  display: none;
}
```

### Options

| Value|Description|
| ------- |:---------------------|
| **data-maxheight** | Change "data-maxheight" in each item to set a different max height value |
| **data-replace-text** | Change "data-maxheight" in each button to set a custom "show less" message |


VanillaJS version
------
Looking for a VanillaJS verion? Check out [pinceladasdaweb](https://github.com/pinceladasdaweb/DynamicMaxHeight)'s DynamicMaxHeight in pure Javascript.

License
-------

    The MIT License (MIT)

    Copyright (c) 2015 Joan Claret

    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to deal
    in the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in
    all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
    THE SOFTWARE.

Thanks
-------
Many thanks to [David Panyella](https://github.com/davidpanyella) and [Òscar Casajuana](https://github.com/elboletaire) for help and inspiration.


Other useful  plugins
---------------------
* [Maximum Characters limit warning](https://github.com/JoanClaret/max-char-limit-warning): Get a warning when the max char limit has been exceeded with a jQuery plugin
* [jcSlider](http://joanclaret.github.io/jcSlider): A responsive slider jQuery plugin with CSS animations 
* [html5 canvas animation](http://joanclaret.github.io/html5-canvas-animation): 3D lines animation with three.js 
* [slide and swipe menu](http://joanclaret.github.io/slide-and-swipe-menu): A sliding swipe menu that works with touchSwipe library. 
