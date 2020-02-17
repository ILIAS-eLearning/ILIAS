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

This will prevent any "... is not defined" errors in the StandardJS syntax checker (see next chapter).

## Alternative 1: StandardJS

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

### PHPStorm

To activate the native support for StandardJS in PHPStorm open Preferences > Editor > Code Style > Javascript and click Set from... > Predefined Stlye > Javascript Standard Style.

### Migration

Since not all issues can be fixed automatically by using the standard script, migration to the coding style needs to be done manually by developers.

## Alternative 2: Airbnb

Javascript code should be written using [Airbnb Stlye](https://github.com/airbnb/javascript) as coding style.

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

There is no native support in PHPStorm yet, but community configuration files are available.

Download the .xml [config file](https://gist.github.com/mentos1386/aa18c110dc272514d592ec27e98128be).

Open Preferences > Editor > Code Style > Javascript and click the little wheel glyph right from the "Scheme" dropdown. Select "Import" and import the file as "Airbnb" style. Now select "Airbnb" in the "Scheme" dropdown.

To configure Eslint open Preferences > Language & Frameworks > JavaScript > Code Quality Tools > ESLint > "Automatic ESLint configuration". This should work with the 

### Migration

Since not all issues can be fixed automatically by using the standard script, migration to the coding style needs to be done manually by developers.

## Comparison

|                         | StandardJS    | Airbnb     |
|-------------------------|---------------|------------|
| Popolarity[1]           | High          | High       |
| PHPStorm Support        | Native        | Custom     |
| PSR-2 Similarities      | Medium        | Medium     |
| Fixing Tools            | Limited       | Limited    |
| Adoptable to Typescript | Yes [2]       | Yes [3]    |
| Matching our Legacy     | ?             | ?          |

**PSR-2 Similarities**

|                                        | PSR-2        | StandardJS    | Airbnb           |
|----------------------------------------|--------------|---------------|------------------|
| Method/Function Names                  | Camel        | Camel         | Camel            |
| Constants                              | Upper + _    | -             | Camel/Upper + _  |
| Properties/Variables                   | No rule      | Camel         | Camel            |
| Indentation                            | 4 spaces     | 2 spaces      | 2 spaces         |
| Line Length (soft)                     | 120          | -             | 100              |
| Opening Braces Methods/Functions       | Next line    | Same Line (i) | Same Line (i)    |
| Opening Braces Control Str. Keyw.      | Same line    | Same Line (i) | Same Line (i)    |
| After Control St. Keyw.                | Space        | Space         | Space (i)        |
| After Function Call                    | No Space     | No Space      | No Space (i)     |
| Control. Str. Parentheses inner Spaces | No           | No (i)        | No (i)           |

Everything declared as (i) means "implicit" by the examples given, even if no explicit rule has been found.

**Use of Semicolons**

Biggest difference in the javascript community between the two styles seems to to be the use of semicolons to end statements:

StandardJS: No, https://standardjs.com/rules.html#semicolons
Airbnb: Yes, https://github.com/airbnb/javascript#semicolons

[1] https://hackernoon.com/what-javascript-code-style-is-the-most-popular-5a3f5bec1f6f

[2] https://standardjs.com/#typescript

[3] https://www.npmjs.com/package/eslint-config-airbnb-typescript
