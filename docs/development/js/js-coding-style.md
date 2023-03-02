# General Javascript Coding Guidelines

## ECMAScript 6

Javascript code must be written in [ES6](http://www.ecma-international.org/ecma-262/6.0/index.html). ES6 runs in all relevant browsers supported by ILIAS. Note: IE11 does not support ES6 and ILIAS does not support IE11 since release 6.0.

## Use Strict

All code files should ensure strict code syntax mode. Some code will automatically run in strict mode, e.g. [ES6 modules or classes](http://www.ecma-international.org/ecma-262/6.0/#sec-strict-mode-code).

All other cases should declare strict mode in the first line of the code file.

```
'use strict';
```

## Declare Globals

If global identifiers like `$`or `il` are being used, they should be declared at the top of the file (after `use strict`).

```
/* global $, il */
``` 

## Use Airbnb Style

Javascript code should be written using [Airbnb Style](https://github.com/airbnb/javascript) as coding style.

### Install

Airbnb works on top of Eslint. The following commands install both packages locally.

```
> npm init -y
> npm i -D eslint eslint-config-airbnb-base eslint-plugin-import
```

Create a file `.eslintrc.json`:
```
{
    "parserOptions": {
        "ecmaVersion": 6
    },
    "extends": "airbnb-base"
}
```

### Check Code Style

Check code style of a single file:

```
> ./node_modules/.bin/eslint test.js
```

### Fixing Code

```
>  ./node_modules/.bin/eslint --fix test.js
```

Note that not all problems are fixable automatically (e.g. using `==` instead of `===`).

### PHPStorm

There is no native support in PHPStorm yet. Please import our [PhpStorm Code Style](../code-style-configs/php-storm.xml) config file. The JS part is based on the airbnb [community config file](https://gist.github.com/mentos1386/aa18c110dc272514d592ec27e98128be).

Open Preferences > Editor > Code Style > Javascript and click the little wheel glyph right from the "Scheme" dropdown. Select "Import" and import the file as "Airbnb" style. Now select "Airbnb" in the "Scheme" dropdown.

To configure Eslint open Preferences > Language & Frameworks > JavaScript > Code Quality Tools > ESLint > "Automatic ESLint configuration".

### Migration

Since not all issues can be fixed automatically by using the standard script, migration to the coding style needs to be done manually by developers.
