# JS Modules in ILIAS

## Current State

Currently Javascript code is provided in a larger number of files which are included separately in the header of each HTML page.

```
<script type="text/javascript" src="./libs/bower/bower_components/jquery/dist/jquery.js"></script>
...
<script type="text/javascript" src="./libs/bower/bower_components/moment/min/moment-with-locales.min.js"></script>
...
<script type="text/javascript" src="./src/UI/templates/js/Dropdown/dropdown.js"></script>
``` 

Single scripts are added to this list via PHP code of every component that relies on the javascript by calling `addJavascript()` on the Global Template.

```
$tpl->addJavascript("./Services/Form/js/ServiceFormMulti.js");
```

The single components are using (in the best case) a revealing-module pattern to expose their public API in a global `il` object.

```
il = il || {};
il.UI = il.UI || {};
il.UI.button = il.UI.button || {};
(function($, il) {
    il.UI.button = (function($) {

        var privateFunction = function (id) {
            ...
        };

        // public function
        var initMonth = function (id) {
            ...
        };

        // return public interface
        return {
            initMonth: initMonth,
            ...
        };
    })($);
})($, il);

```

### Issues with the current approach

- The sequence of script-tags is error-prone and relies on addJavascript calls in lots of different PHP components.
- Ajax calls may result in missing dependencies to unavailable js files.
- The approach does not allow to bundle and minify at least the core Javascript code.

## Proposal

### Use ES6 modules

Modules SHOULD be implemented as ES6 modules. They should export their public API part using ES6 export statements.

```
export default {

  public1: function() {
    ...
    internal();
  }
}

function internal() {
  console.log("internal called");
}
```

### Split and bundle files in components

Single components MAY split up their code into multiple files using the ES6 module concept internally. To improve performance they SHOULD create bundles on the component level, see [js-bundling.md](js-bundling.md).


## Outlook: Bundle core JS via ES6 modules

Using ES6 modules could greatly reduce the number of scripts being included in ILIAS page HTML.

```
<html>
<head>
</head>
<body>

...

<script type="module">
    import il from './js/il.js';
    window.il = il;
</script>
<script>
window.onload = function () {
    ...
};
</script>
</body>
</html>
```

All core and service components MUST add their ES6 imports to one central il.js file.

```
// this first line imports a non-ES6-module version of jquery
import "../libs/bower/bower_components/jquery/dist/jquery.min.js"
...
// ES6-module import style
import ui from "./src/UI/js/ui.js"
import object from "./Services/Object/js/object.js"
import tagging from "./Servicces/tagging/tagging.js"
...

export default {
  ui: ui,
  objects: objects,
  tagging: tagging,
  ...
}
```

Pros

- Availability of all core and service JS code with each request.
- Elimination of problems caused by the wrong sequence scripts are loaded.
- Optional bundling and minification based on ES6 modules.
- Removing `$tpl->addJavascript()` calls from PHP.
- Necessary refactoring would be limited and can be done incrementally.
- Better readability of the code.
- Easy way to prevent global scope pollution.

Centralizing the imports in one place instead of adding them to single components keeps the path information (location of the js files) in one place. This makes refactoring easier (e.g. when JS files are moved). This approach shares some similarities with the way less files are currently organised in ILIAS.

It may not be appropriate to include Javascript specialised on one component without any reuse for other contexts in this central `il.js` file, e.g. forum.js or learning_module.js. But in general the number of scripts separately included in a page could be reduced to two.