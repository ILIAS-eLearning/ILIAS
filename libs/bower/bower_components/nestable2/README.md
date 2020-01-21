Nestable2 - new pickup of Nestable!
========

### Drag & drop hierarchical list with mouse and touch compatibility (jQuery / Zepto plugin)

[![Demo](https://img.shields.io/badge/demo-live-brightgreen.svg?style=flat-square)](https://ramonsmit.github.com/Nestable2/)
[![Build Status](https://travis-ci.org/RamonSmit/Nestable2.svg)](https://travis-ci.org/RamonSmit/Nestable2)
[![License](https://img.shields.io/npm/l/nestable2.svg?style=flat-square)](https://github.com/RamonSmit/Nestable2/blob/master/LICENSE)
[![Release](https://img.shields.io/npm/v/nestable2.svg?style=flat-square)](https://www.npmjs.com/package/nestable2)

Nestable is an experimental example and IS under active development. If it suits your requirements feel free to expand upon it!

## Install

You can install this package either with `npm` or with `bower`.

### npm

```shell
npm install --save nestable2
```

Then add a `<script>` to your `index.html`:

```html
<script src="/node_modules/nestable2/jquery.nestable.js"></script>
```

Or `require('nestable2')` from your code.

### bower

```shell
bower install --save nestable2
```

### CDN

You can also find us on [CDNJS](https://cdnjs.com/libraries/nestable2):

```
//cdnjs.cloudflare.com/ajax/libs/nestable2/1.6.0/jquery.nestable.min.css
//cdnjs.cloudflare.com/ajax/libs/nestable2/1.6.0/jquery.nestable.min.js
```

## Usage

Write your nested HTML lists like so:
```html
<div class="dd">
    <ol class="dd-list">
        <li class="dd-item" data-id="1">
            <div class="dd-handle">Item 1</div>
        </li>
        <li class="dd-item" data-id="2">
            <div class="dd-handle">Item 2</div>
        </li>
        <li class="dd-item" data-id="3">
            <div class="dd-handle">Item 3</div>
            <ol class="dd-list">
                <li class="dd-item" data-id="4">
                    <div class="dd-handle">Item 4</div>
                </li>
                <li class="dd-item" data-id="5" data-foo="bar">
                    <div class="dd-nodrag">Item 5</div>
                </li>
            </ol>
        </li>
    </ol>
</div>
```
Then activate with jQuery like so:
```js
$('.dd').nestable({ /* config options */ });
```

### Events
`change`: For using an .on handler in jquery

The `callback` provided as an option is fired when elements are reordered or nested.
```js
$('.dd').nestable({
    callback: function(l,e){
        // l is the main container
        // e is the element that was moved
    }
});
```

`onDragStart` callback provided as an option is fired when user starts to drag an element. Returning `false` from this callback will disable the dragging.
```js
$('.dd').nestable({
    onDragStart: function(l,e){
        // l is the main container
        // e is the element that was moved
    }
});
```

This callback can be used to manipulate element which is being dragged as well as whole list.
For example you can conditionally add `.dd-nochildren` to forbid dropping current element to
some other elements for instance based on `data-type` of current element and other elements:
 
 ```js
 $('.dd').nestable({
     onDragStart: function (l, e) {
         // get type of dragged element
         var type = $(e).data('type');
         
         // based on type of dragged element add or remove no children class
         switch (type) {
             case 'type1':
                 // element of type1 can be child of type2 and type3
                 l.find("[data-type=type2]").removeClass('dd-nochildren');
                 l.find("[data-type=type3]").removeClass('dd-nochildren');
                 break;
             case 'type2':
                 // element of type2 cannot be child of type2 or type3
                 l.find("[data-type=type2]").addClass('dd-nochildren');
                 l.find("[data-type=type3]").addClass('dd-nochildren');
                 break;
             case 'type3':
                 // element of type3 cannot be child of type2 but can be child of type3
                 l.find("[data-type=type2]").addClass('dd-nochildren');
                 l.find("[data-type=type3]").removeClass('dd-nochildren');
                 break;
             default:
                 console.error("Invalid type");
         }
     }
 });
 ```
`beforeDragStop` callback provided as an option is fired when user drop an element and before 'callback' method fired. Returning false from this callback will disable the dropping and restore element at start position.

```js
$('.dd').nestable({
    beforeDragStop: function(l,e, p){
        // l is the main container
        // e is the element that was moved
        // p is the place where element was moved.
    }
});
```

### Methods

`serialize`:
You can get a serialised object with all `data-*` attributes for each item.
```js
$('.dd').nestable('serialize');
```
The serialised JSON for the example above would be:
```json
[{"id":1},{"id":2},{"id":3,"children":[{"id":4},{"id":5,"foo":"bar"}]}]
```

`toArray`:
```js
$('.dd').nestable('toArray');
```
Builds an array where each element looks like:
```js
{
    'depth': depth,
    'id': id,
    'left': left,
    'parent_id': parentId || null,
    'right': right
}
```

You can get a hierarchical nested set model like below.
```js
$('.dd').nestable('asNestedSet');
```
The output will be like below:
```js
[{"id":1,"parent_id":"","depth":0,"lft":1,"rgt":2},{"id":2,"parent_id":"","depth":0,"lft":3,"rgt":4},{"id":3,"parent_id":"","depth":0,"lft":5,"rgt":10},{"id":4,"parent_id":3,"depth":1,"lft":6,"rgt":7},{"id":5,"parent_id":3,"depth":1,"lft":8,"rgt":9}]
```

`add`:
You can add any item by passing an object. New item will be appended to the root tree.
```js
$('.dd').nestable('add', {"id":1,"children":[{"id":4}]});
```
Optionally you can set 'parent_id' property on your object and control in which place in tree your item will be added.
```js
$('.dd').nestable('add', {"id":1,"parent_id":2,"children":[{"id":4}]});
```

`replace`:
You can replace existing item in tree by passing an object with 'id' property.
```js
$('.dd').nestable('replace', {"id":1,"foo":"bar"});
```
You need to remember that if you're replacing item with children's you need to pass this children's in object as well.
```js
$('.dd').nestable('replace', {"id":1,"children":[{"id":4}]});
```

`remove`:
You can remove existing item by passing 'id' of this element. To animate item removing check `effect` config option. This will delete the item with all his children.
```js
$('.dd').nestable('remove', 1);
```
This will invoke callback function after deleting the item with data-id '1'.
```js
$('.dd').nestable('remove', 1, function(){
    console.log('Item deleted');
});
```

`removeAll`:
Removes all items from the list. To animate items removing check `effect` config option. You can also use callback function to do something after removing all items.
```js
$('.dd').nestable('removeAll', function(){
    console.log('All items deleted');
});
```

`destroy`:
You can deactivate the plugin by running
```js
$('.dd').nestable('destroy');
```
### Autoscroll while dragging
Autoscrolls the container element while dragging if you drag the element over the offsets defined in `scrollTriggers` config option.

```js
$('.dd').nestable({ scroll: true });
```

To use this feature you need to have `jQuery >= 1.9` and `scrollParent()` method.
You can be find this method in `jQuery UI` or if you don't want to have `jQuery UI` as a dependency you can use [this repository](https://github.com/slindberg/jquery-scrollparent).


You can also control the scroll sensitivity and speed, check `scrollSensitivity` and `scrollSpeed` options.

### On the fly nestable generation

You can passed serialized JSON as an option if you like to dynamically generate a Nestable list:
```html
<div class="dd" id="nestable-json"></div>

<script>
var json = '[{"id":1},{"id":2},{"id":3,"children":[{"id":4},{"id":5,"foo":"bar"}]}]';
var options = {'json': json}
$('#nestable-json').nestable(options);
</script>
```
NOTE: serialized JSON has been expanded so that an optional "content" property can be passed which allows for arbitrary custom content (including HTML) to be placed in the Nestable item

Or do it yourself the old-fashioned way:
```html
<div class="dd" id="nestable3">
    <ol class='dd-list dd3-list'>
        <div id="dd-empty-placeholder"></div>
    </ol>
</div>

<script>
$(document).ready(function(){
    var obj = '[{"id":1},{"id":2},{"id":3,"children":[{"id":4},{"id":5}]}]';
    var output = '';
    function buildItem(item) {

        var html = "<li class='dd-item' data-id='" + item.id + "'>";
        html += "<div class='dd-handle'>" + item.id + "</div>";

        if (item.children) {

            html += "<ol class='dd-list'>";
            $.each(item.children, function (index, sub) {
                html += buildItem(sub);
            });
            html += "</ol>";

        }

        html += "</li>";

        return html;
    }

    $.each(JSON.parse(obj), function (index, item) {

        output += buildItem(item);

    });

    $('#dd-empty-placeholder').html(output);
    $('#nestable3').nestable();
});
</script>
```

### Configuration

You can change the follow options:

* `maxDepth` number of levels an item can be nested (default `5`)
* `group` group ID to allow dragging between lists (default `0`)
* `callback` callback function when an element has been changed (default `null`)
* `scroll` enable or disable the scrolling behaviour (default: `false`)
* `scrollSensitivity` mouse movement needed to trigger the scroll (default: `1`)
* `scrollSpeed` speed of the scroll (default: `5`)
* `scrollTriggers` distance from the border where scrolling become active (default: `{ top: 40, left: 40, right: -40, bottom: -40 }`)
* `effect` removing items animation effect (default: `{ animation: 'none', time: 'slow'}`). To fadeout elements set 'animation' value to 'fade', during initialization the plugin.

These advanced config options are also available:

* `contentCallback` The callback for customizing content (default `function(item) {return item.content || '' ? item.content : item.id;}`)
* `listNodeName` The HTML element to create for lists (default `'ol'`)
* `itemNodeName` The HTML element to create for list items (default `'li'`)
* `rootClass` The class of the root element `.nestable()` was used on (default `'dd'`)
* `listClass` The class of all list elements (default `'dd-list'`)
* `itemClass` The class of all list item elements (default `'dd-item'`)
* `dragClass` The class applied to the list element that is being dragged (default `'dd-dragel'`)
* `noDragClass` The class applied to an element to prevent dragging (default `'dd-nodrag'`)
* `handleClass` The class of the content element inside each list item (default `'dd-handle'`)
* `collapsedClass` The class applied to lists that have been collapsed (default `'dd-collapsed'`)
* `noChildrenClass` The class applied to items that cannot have children (default `'dd-nochildren'`)
* `placeClass` The class of the placeholder element (default `'dd-placeholder'`)
* `emptyClass` The class used for empty list placeholder elements (default `'dd-empty'`)
* `expandBtnHTML` The HTML text used to generate a list item expand button (default `'<button data-action="expand">Expand></button>'`)
* `collapseBtnHTML` The HTML text used to generate a list item collapse button (default `'<button data-action="collapse">Collapse</button>'`)
* `includeContent` Enable or disable the content in output (default `false`)
* `listRenderer` The callback for customizing final list output (default `function(children, options) { ... }` - see defaults in code)
* `itemRenderer` The callback for customizing final item output (default `function(item_attrs, content, children, options) { ... }` - see defaults in code)
* `json` JSON string used to dynamically generate a Nestable list. This is the same format as the `serialize()` output

**Inspect the [Nestable2 Demo](https://ramonsmit.github.io/Nestable2/) for guidance.**

## Change Log

### 21th October 2017
* [klgd] Fixed conflict when project using also jQuery 2.*
* [RomanBurunkov] Moved effect and time parameter in `remove` method to config option. This changes break backward compatibility with version 1.5
* [RomanBurunkov] Added callback in methods `remove` and `removeAll` as a parameter
* [RomanKhomyshynets] Fixed `add` function with non-leaf parent_id, fixed [#84](https://github.com/RamonSmit/Nestable2/issues/84)

### 9th August 2017
* [pjona] Added support for string (GUID) as a data id

### 21th July 2017
* [spathon] Append the .dd-empty div if the list don't have any items on init, fixed [#52](https://github.com/RamonSmit/Nestable2/issues/52)
* [pjona] Fixed problem on Chrome with touch screen and mouse, fixed [#28](https://github.com/RamonSmit/Nestable2/issues/28) and[#73](https://github.com/RamonSmit/Nestable2/issues/73)

### 15th July 2017
* [RomanBurunkov] Added fadeOut support to `remove` method
* [pjona] Fixed `replace` method (added collapse/expand buttons when item has children), see [#69](https://github.com/RamonSmit/Nestable2/issues/69)
* [uniring] Added autoscroll while dragging, see [#71](https://github.com/RamonSmit/Nestable2/issues/71)

### 2nd July 2017
* [pjona] Added CDN support
* [pjona] Removed unneeded directories in `dist/`

### 25th June 2017
* [pjona] Fixed `add` method when using parent_id, see [#66](https://github.com/RamonSmit/Nestable2/issues/66)

### 22th June 2017
* [pjona] Added Travis CI builds after each commit and pull request
* [pjona] Added `test` task in gulp with eslint validation
* [pjona] Added minified version of JS and CSS
* [pjona] Changed project name to `nestable2`
* [pjona] Fixed `remove` method when removing last item from the list

### 16th June 2017

* [imliam] Added support to return `false` from the `onDragStart` event to disable the drag event

### 28th May 2017

* [pjona] Function `add` support `parent_id` property
* [pjona] Added `replace` function
* [pjona] Added `remove` function

### 22th May 2017

* [pjona] Added npm installation
* [pjona] Added `add` function

### 10th April 2017

* [timalennon] Added functions: `toHierarchy` and `toArray`

### 17th November 2015

* [oimken] Added `destroy` function

### 2nd November 2015

* [ivanbarlog] Added `onDragStart` event fired when user starts to drag an element

### 21th April 2015

* [ozdemirburak] Added `asNestedSet` function
* [ozdemirburak] Added bower installation

### 6th October 2014

* [zemistr] Created listRenderer and itemRenderer. Refactored build from JSON.
* [zemistr] Added support for adding classes via input data. (```[{"id": 1, "content": "First item", "classes": ["dd-nochildren", "dd-nodrag", ...] }, ... ]```)

### 3rd October 2014

* [zemistr] Added support for additional data parameters.
* [zemistr] Added callback for customizing content.
* [zemistr] Added parameter "includeContent" for including / excluding content from the output data.
* [zemistr] Added fix for input data. (JSON string / Javascript object)

### 7th April 2014

* New pickup of repo for developement.

### 14th March 2013

* [tchapi] Merge Craig Sansam' branch [https://github.com/craigsansam/Nestable/](https://github.com/craigsansam/Nestable/) - Add the noChildrenClass option

### 13th March 2013

* [tchapi] Replace previous `change` behaviour with a callback

### 12th February 2013

* Merge fix from [jails] : Fix change event triggered twice.

### 3rd December 2012

* [dbushell] add no-drag class for handle contents
* [dbushell] use `el.closest` instead of `el.parents`
* [dbushell] fix scroll offset on document.elementFromPoint()

### 15th October 2012

* Merge for Zepto.js support
* Merge fix for remove/detach items

### 27th June 2012

* Added `maxDepth` option (default to 5)
* Added empty placeholder
* Updated CSS class structure with options for `listClass` and `itemClass`.
* Fixed to allow drag and drop between multiple Nestable instances (off by default).
* Added `group` option to enabled the above.

* * *

Original Author: David Bushell [http://dbushell.com](http://dbushell.com/) [@dbushell](http://twitter.com/dbushell/)

New Author     : Ramon Smit    [http://ramonsmit.nl](http://ramonsmit.nl) [@ramonsmit94](https://twitter.com/Ram0nSm1t/)

Contributors :

* Cyril [http://tchap.me](http://tchap.me), Craig Sansam
* Zemistr [http://zemistr.eu](http://zemistr.eu), Martin Zeman
* And alot more. 

Copyright © 2012 David Bushell / © Ramon Smit 2014/2017 | BSD & MIT license
