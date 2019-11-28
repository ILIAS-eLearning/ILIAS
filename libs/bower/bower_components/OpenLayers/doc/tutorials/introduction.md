---
title: Introduction
layout: doc.hbs
---

# Introduction

## Objectives
With version 3, the OpenLayers web mapping library was fundamentally redesigned. The widely used version 2 dates from the early days of Javascript development, and was increasingly showing its age. So it has been rewritten from the ground up to use modern design patterns.

The initial release aims to support much of the functionality provided by version 2, with support for a wide range of commercial and free tile sources, and the most popular open-source vector data formats. As with version 2, data can be in any projection. The initial release also adds some additional functionality, such as the ability to easily rotate or animate maps.

It is also designed such that major new features, such as displaying 3D maps, or using WebGL to quickly display large vector data sets, can be added in later releases.

## Google Closure
OpenLayers was written in a way so it can be compiled with [__Closure Compiler__](https://developers.google.com/closure/compiler/). Its 'advanced' compilation mode offers a level of compression that exceeds anything else available.

## Public API
Using the advanced optimizations of the Closure Compiler means that properties and methods are renamed &ndash; `longMeaningfulName` might become `xB` &ndash; and so are effectively unusable in applications using the library. To be usable, they have to be explicitly `exported`. This means the exported names, those not renamed, effectively become the public API of the library. These __exportable__ properties and methods are marked in the source, and documented in the [API docs](../../apidoc). This is the officially supported API of the library. A build containing all these exportable names is known as a __full build__. A hosted version of this is available, which can be used by any application.

## Custom Builds
Unlike in, say, Node, where a module's exports are fixed in the source, with Closure Compiler, exports can be defined at compile time. This makes it easy to create builds that are customized to the needs of a particular site or application: a __custom build__ only exports those properties and methods needed by the site or application. As the full build is large, and will probably become larger as new features are added to the API, it's recommended that sites create a custom build for production software.

## Renderers and Browser Support
The library currently includes two renderers: Canvas and WebGL. Both of them support both raster data from tile/image servers, and vector data; WebGL however does not support labels. Clearly only those browsers that [support Canvas](http://caniuse.com/canvas) can use the Canvas renderer. Equally, the WebGL renderer can only be used on those devices and [browsers](http://caniuse.com/webgl) that support WebGL.

OpenLayers runs on all modern browsers that support [HTML5](https://html.spec.whatwg.org/multipage/) and [ECMAScript 5](http://www.ecma-international.org/ecma-262/5.1/). This includes Chrome, Firefox, Safari and Edge. For older browsers and platforms like Internet Explorer (down to version 9) and Android 4.x, [polyfills](http://polyfill.io) for `requestAnimationFrame` and `Element.prototype.classList` are required, and using the KML format requires a polyfill for `URL`.

The library is intended for use on both desktop/laptop and mobile devices.

## Objects and Naming Conventions
The top-level namespace is `ol` (basically, `var ol = {};`). Subdivisions of this are:

* further namespaces, such as `ol.layer`; these have a lower-case initial
* simple objects containing static properties and methods, such as `ol.easing`; these also have a lower-case initial
* types, which have an upper-case initial. These are mainly 'classes', which here means a constructor function with prototypal inheritance, such as `ol.Map` or `ol.layer.Vector` (the Vector class within the layer namespace). There are however other, simpler, types, such as `ol.Extent`, which is an array.

Class namespaces, such as `ol.layer` have a base class type with the same name, such as `ol.layer.Layer`. These are mainly abstract classes, from which the other subclasses inherit.

Source files are similarly organised, with a directory for each class namespace. Names are however all lower-case, for example, `ol/layer/vector.js`.

OpenLayers follows the convention that the names of private properties and methods, that is, those that are not part of the API, end in an underscore. In general, instance properties are private and accessed using accessors.
