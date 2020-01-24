# General Javascript Coding Guidelines

## ECMAScript 6

Javascript code must be written in [ES6](http://www.ecma-international.org/ecma-262/6.0/index.html). ES6 runs in all relevant browsers supported by ILIAS. Note: IE11 does not support ES6 and ILIAS does not support IE11 since release 6.0.

## Use Strict

All code files should ensure strict code syntax mode. Some code will automatically run in strict mode, e.g. [ES6 modules or classes](http://www.ecma-international.org/ecma-262/6.0/#sec-strict-mode-code).

All other cases should declare strict mode in the first line of the code file.

```
'use strict';
```


## StandardJS

Javascript code should be written using [StandardJS](https://standardjs.com/rules.html) as coding style.

### Install

```
> npm install standard --global
```

### Check Code Style

The following two commands are equivalent. They check all .js files within the current working directory.

```
> standard
> standard "**/*.js"
```

Checking a single file:

```
> standard test.js
```

### Fixing Code

```
> standard test.js --fix
```

Note that not all problems are fixable automatically (e.g. using `==` instead of `===`).

### Declare Globals

If global identifiers like `$`or `il` are being used, they should be declared at the top of the file.

```
/* global $, il */
``` 

This will prevent any "... is not defined" errors.

### PHPStorm

To activate the native support for StandardJS in PHPStorm open Preferences > Editor > Code Style > Javascript and click Set from... > Predefined Stlye > Javascript Standard Style.

### Migration

Since not all issues can be fixed automatically by using the standard script, migration to the coding style needs to be done manually by developers.