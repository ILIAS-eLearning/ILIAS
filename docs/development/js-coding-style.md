# JS Coding Style

This is the JavaScript coding style for the [ILIAS project](https://github.com/ILIAS-eLearning/ILIAS).

## ECMAScript 6 (ES6)

All JavaScript code must be written using [ES6](http://www.ecma-international.org/ecma-262/6.0/index.html) syntax, which
runs in all relevant browsers supported by ILIAS. All JavaScript logic must also be implemented
as [ES6-modules](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide/Modules), a revealing module pattern must
not be used (anymore) for exposing business logic. Furthermore, JavaScript code should not be written in HTML
script-tags, but in dedicated files whenever possible.

## Don't use jQuery

jQuery has played a major part for JavaScript projects in the past. However, we believe that since the revision of ES6
and it's wide support across all relevant browsers, using standard JavaScript might be a better choice now. Therefore:

- you must use vanilla JavaScript whenever possible
- you should avoid using third-party libraries depending on jQuery

## Airbnb Coding Style

ILIAS is using the [Airbnb Code Style](https://github.com/airbnb/javascript). We strongly emphasize the usage
of [`class`(es)](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Classes), which make encapsulation
much more convinient since the introduction of
[private class features](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Classes/Private_class_fields)
and the revealing module pattern obsolete.

### Additions

The Airbnb code-style is in some ways incomplete or not clear enough, which is why the code-style for ILIAS is extended
by the following rules:

#### 1. Copyright

All files must start with a file-level doc-comment that contains the official ILIAS copyright license header:

```javascript
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

// business logic starts here.
```

_Note, the comment may be extended after line 13 by real file-level comments._

#### 2. JSDoc Annotations

JavaScript does not support native type-hinting, therefore we use [JSDoc](https://jsdoc.app/) comments to provide
_some_ type-information about our properties, arguments and return-values. This makes JavaScript code way more readable
and also enables modern IDEs to suggest proper auto-complete options. Hence you must at least:

- describe function parameters by an [`@param`](https://jsdoc.app/tags-param.html) annotation with name and type.
- describe function return-values by an [`@returns`](https://jsdoc.app/tags-returns.html) annotation with type.
- describe possible exceptions by an [`@throws`](https://jsdoc.app/tags-throws.html) annotation with type.
- describe object properties by an [`@type`](https://jsdoc.app/tags-type.html) annotation with type.

Please try to be as specific as possible with the type documentation, so e.g. use `MouseEvent` over `Event`and
`HTMLDivElement` over `HTMLElement` and so on.

#### 3. ESLint Configuration Comments

As will be described in a following section, we are using ESLint to automatically check and/or apply our code-style.
This tool allows developers to disable or alter the linting process through so-called
[configuration-comments](https://eslint.org/docs/latest/use/configure/rules#using-configuration-comments-1). These
comments must not be used, since this would ignore code-style for particular sections altogether.

#### 4. Naming Conventions

The naming conventions of the Airbnb code-style only go so far, which is why in ILIAS these rules are extended by the
following additions:

- Object properties: Airbnb allows space to name properties in snake_case, which would allow snake_case `class` methods
  because classes are simply objects. Therefore, we require ALL object properties to be named in camelCase.
- Configurations: ILIAS is using various 3rd-party libraries, which often require configuration-files. To quickly
  identify these files, we require them to be named in this fashion: `<library>.config.js`
- Minification: To distinguish between normal JavaScript files and minified versions, we require them to be named like
  their input-file, but ending with `.min.js`.
- Testing: Since JavaScript unit tests will not export anything, we require them to be named like the file they are
  testing.

### Exceptions

Like there are some rules which are not clear enough or incomplete, there are also some rules which are not quite
applicable in the ILIAS project. Therefore, the following rules are exempt from our code-style:

#### 1. Exempt JavaScript files

There are some JavaScript files which are auto-generated, which cannot comply with the code-style rules. Such files are
created by e.g. module-bundling or minification. Therefore files matching the following criteria are exempt from all
code-style rules EXCEPT for addition 1, the copyright notice.

- files located in a `dist/` folder, which as described is the default output folder for bundled files as described in
  the [bundling guide](./js-bundling.md).
- files ending with `.min.js`, which are minified versions of the original file.

#### 2. Function Expressions

Airbnb requires ALL function expressions to be named, which is not always required. In ILIAS we therefore weakened this
rule to only require named function expressions, if they are assigned to a variable. This leaves room for anonymous
functions (which are not arrow-functions), which are often used as callbacks for event-handlers or IIFE's.

#### 3. Function Declarations

Airbnb requires ALL functions to be declared BEFORE they are used, which is unnecessary due to the fact JavaScript
functions are [hoisted](https://developer.mozilla.org/en-US/docs/Glossary/Hoisting), which makes this rule obsolete.
Therefore, we do not require function declarations to be placed before they are used.

## Applying Code Style

ILIAS is using [ESLint](https://eslint.org/) to automatically check and/or fix JavaScript code according to the
code-style. This tool can be used by IDEs or editors, but also manually via CLI:

```bash
# checking code-stye
npx eslint SomeModule.js
```

```bash
# fixing code-style
npx eslint --fix SomeModule.js
```

_Note that not all problems can be fixed by ESLint automatically like e.g. naming conventions._

Code-style will also be automatically applied with an according pre-commit git-hook, which can be installed
with a `composer install`.

## Using PHPStorm

The JavaScript part of the ILIAS [code-style](code-style-configs/php-storm.xml) for PHPStorm has been adopted from
Airbnb's [community code-style](https://gist.github.com/Cleancookie/37268871188384da51a79b9443bf1266) for WebStorm.
Therefore, the ILIAS code-style config can be used for JavaScript code as well.

ESLint can be configured for PHPStorm as well by either using automatic-mode, or manually using the configuration file
located at `./.eslintrc.json`. An instance of ESLint can be found in the node-modules folder
at `./node_modules/.bin/eslint`.
