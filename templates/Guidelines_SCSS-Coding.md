# General

*Please note, that you need to provide a Pull Request for all CSS related changes done in this folder. The CSS Coordinators
will then review the PR and give you feedback and/or merge your changes.*

This section of the codebase defines the design and layout of ILIAS. All CSS code is build here. HTML templates can be found elsewhere (see section "HTML").

The following guidelines will help you to
* find your way through the folder structure,
* understand the different layers that keep our style code organized,
* and learn what standards and best practices are expected.

Code contributions to the ILIAS repository containing style code will be reviewed according to the rules and recommendations defined here.

## SASS in SCSS syntax generates CSS

Sass/CSS is responsible for the design of the website. Instead of directly writing CSS code and many repeating patterns by hand, we are using the Sass pre-processor to generate the CSS code for ILIAS.

Sass extends the CSS language, adding features that allow advanced variables, mixins, functions and many other techniques that allow you to make CSS that is more maintainable, themable and expendable (see: [sass-lang.com](http://sass-lang.com/)).

The Sass pre-processor compiles the entry point delos.scss and all connected files to the one delos.css file which can then be rendered by the browser.

You should consult the [official Sass Documentation](https://sass-lang.com/documentation/) to make use of the advantages of Sass.

### Guidelines
* Style code MUST be written in the SCSS syntax of the Sass pre-processer.
* All colors, font sizes, spacings, offsets, gaps, as well as the proportions of the basic layout MUST be defined by Sass/CSS.
* When contributing style code to the ILIAS repository, you MUST first compile the Sass code to CSS using the latest released version of Dart Sass: https://github.com/sass/dart-sass/releases
* We currently follow a **desktop first approach** on the Sass level. This means, that we handle all mobile cases as special cases and the desktop as the default.
* You MUST NOT make changes to the compiled delos.css manually.
* Delos.scss MUST only contain imports and no other Sass logic at all.

## HTML

HTML templates are only responsible for a well structured HTML document, which displays the bare content of the website and nothing else.

They can be found in `src/UI/templates/default` for modern UI components or in `Modules/` and `Services/` for legacy components.

### Guidelines
* New class names MUST follow the naming convention outlined in this document.
* You MUST NOT use style attributes like style, align, border, cellpadding, cellspacing font, nowrap, valign, width, height and similar in HTML templates.
* You MUST NOT use `&nbsp;`, `<br>`, `<br/>` or similar means to create space.



# ITCSS structure

## General Sorting

Our style code is split into many files across the following layers based on the [Inverted Triangle CSS (ITCSS)](https://www.xfive.co/blog/itcss-scalable-maintainable-css-architecture/) structure:

1. settings
2. dependencies
3. tools
4. normalize
5. layout
6. elements
7. components
8. hacks and tweaks

The reach of the code gets less global and more specific the deeper we get in this layer structure.

### Guidelines

The style code...

* MUST be structured into separate files and logical sections.
* MAY affect all layers below and other files on the same layer.
* MUST NOT affect layers above.
* for components MUST only affect this component and no other components on the same layer.

## Settings layer

The Settings layer is used to define general variables which shape the style and design of the entire system.

### Guidelines

#### General scope
* **MUST only contain global variables** that will be used throughout the whole system e.g. colors, fonts, font-sizes and the most important spacings.
* variables MUST be **needed to quickly recolor and reshape ILIAS to create a new minimal, but clearly branded skin**.
* Variables from this layer SHOULD be used to define the design on lower levels.
* Variables directly tied to a single component MUST be essential to define the basic look of such a skin e.g. the height of the footer.
* Variables not essential for creating the basic look MUST be placed on lower layers.
#### Import and Compiling
* Anything added to the **settings layer MUSTN'T create CSS code** on its own.
* Variables on the Settings layer SHOULD be made easy to override by adding `!default` to the definition.
* Partial files SHOULD be added to the index with @forward, so they are public and can be overriden when the main delos.scss is loaded by a skin.
* You MAY include the entire Settings layer on lower levels.
* You SHOULD NOT need to include individual files from the settings layer on lower levels.

#### Legacy
* The legacy folder SHOULD be empty and the contents SHOULD be refactored.

### Examples
* Main color palette
* The most common font sizes
* The header height (essential for minimal skin)
* Primary and default button colors (essential for minimal skin)

### Non-Examples
* The color of a disabled button is not essential for the basic look of a minimal skin and should be carefully set following accessibility practices on a lower layer.

## Dependencies

Files pulled in from other projects and/or modifications of such files are placed on the Dependency layer.

Adding dependencies requires careful consideration whether the benefits outweigh the risks.

### Guidelines
#### General 
* You MUST NOT add new style code dependencies outside of this folder.
* Instead of loading complete packages to our CSS as a framework you **SHOULD only load selected parts of a dependency** where needed using it as a library.
* The "unmodified" folder MUST only contain unmodified dependencies (or unmodified parts of them). They MAY be updated with new official releases of the dependency after careful testing.
* You SHOULD keep modifications, additions or overrides to a minimum and make them through files in the "modification" folder mirroring the original structure of the unmodified dependency. In this case you MAY load a mix of unmodified and modified files on the index file or make such a mixed configuration available for lower layers. The "modification" folder MUSTN'T hold unmodified files.

#### Import and compiling
* This layer MAY create a dependency's CSS code (and MUST do so selectively only for code that is actually needed).
* When including dependency files in multiple components this MUST NOT lead to repeating CSS code (which can happen when including files with @use that use @import for loading its own partials). To avoid this you MAY separate variables from CSS classes in the "modification" folder and include only the variables on lower levels. In this case, extending to CSS classes of a dependency doesn't work on lower layers, as the component is unaware of the CSS classes.
* Lower layers MAY call the variables, functions and mixins of a dependency for their purposes and SHOULD include them with a namespace.
* A dependency SHOULD NOT be called on with @forward inside the _index.scss to expose its public variables to a skin.

#### Bootstrap 3

Bootstrap 3 has been used since ILIAS 5.0 to solve many common web design challenges like normalizing, column layouts and input elements.

In the future, many systems in Bootstrap 3 will be replaced by our native solutions specifically customized to the needs of ILIAS. In 2022, Bootstrap 3 has been updated to the [official Bootstrap 3 Sass port](https://github.com/twbs/bootstrap-sass).

* If Bootstrap offers applicable classes and mixins, they SHOULD be used where possible.
* If a variable, mixin or function is defined inside the Bootstrap dependency layer, but also has an equivalent on one of the ILIAS ITCSS layers, you MUST use or extend to the ILIAS version instead.
* To avoid repeating CSS code, you MUST import only the variables and mixins of Bootstrap 3 on lower layers like so `@use "../some-relative-path/020-dependencies/modifications/bootstrap-3-scss/bootstrap-3-scss-modified-variables-mixins" as btstrp3;
` and SHOULD use the namespace `btstrp3`.
* You MAY customize, refactor and modernize parts of Bootstrap 3 and turn them into our own native code on the appropriate layer. In this case, you MUST point all formerly dependent components to the new code and remove the import of the now redundant Bootstrap 3 partial.
* You SHOULD NOT pull in Bootstrap 3 code into our components without reducing, optimizing and modernizing it.

#### Legacy
* Legagcy dependencies MAY be located in other places (e.g. "node_modules"). Modifications to those dependencies with regards to style code SHOULD be done inside the "modifications" folder of the ITCSS dependency layer.

### Examples
* Bootstrap

## Tools

General mixins, silent extensible classes, functions, media queries and animations
that are used in multiple locations on lower layers find their place on the Tools layer.

They provide uniform definitions for common concepts, patterns and problems, and thus foster visual homogenity in the system. They substantiate variables from the Settings into more concrete concepts.

Past layouts and HTML templates heavily rely on CSS utility classes like "ilCenter" or "smallred" for some formatting. These kinds of CSS utility classes are now deprecated.

### Guidelines

* **Tools MUST not generate CSS code** before being used on lower levels.
* Tools MAY be used in various other sections in the SCSS.
* You **SHOULD NOT create utility CSS classes.**
* Utility like systems SHOULD be covered by Tool functions and mixins that are being called on lower levels or extensions to silent Tool classes.

### Examples:
* Provide mixins and functions for common styling problems.
* A function to turn pixel values to rem
* A mixin that generates a palette of color shades

### Non-Examples:
* A class "smallred" to style text in warnings. Instead the semantic class "warning" should be styled on Component level and MAY use colors from a Settings or Tools alert color palette.

## Normalize

Styles needed to unify browser behaviour to a good default base state in all browsers are defined on the Normalize layer.

Currently, we rely on dependencies e.g. for normalizing complex elements like input fields. Therefor, large portions of normalizing styles may be located on the Dependency layer.

### Guidelines

* This layer MAY create CSS code to create a consistent baseline across all browsers.
* Elements that are heavily styled and overwritten on lower layers anyway SHOULD NOT be normalized. Resetting paragraph margins here and then overriding them again completely on the Elements layer is not necessary.

### Examples

* Set general line-heights for texts.
* Remove paddings and margins.
* Remove browser specific stylings.

## Layout

Silent extensible classes, variables, functions and mixins that help define the positioning and spacing of components relative to each other have to be added to the layout layer. Styling rules from this layer are used to control the relationship between components and their elements.

### Guidelines

* **General patterns and concepts for spacing and layout** MUST be placed on the Layout layer as silent classes, variables, functions and mixins.
* You **SHOULD NOT create utility CSS classes** (e.g. "ilFloatRight") like they are used in many legacy HTML templates.
* You MAY create utility functions, mixins or silent classes to be used by semantic classes on lower layers instead.
* Layout **SHOULD NOT create CSS code** unless called upon on lower layers.
* If the positioning or spacing for a component can be completely based on Settings or general Layout variables, it MUST be defined in the component itself instead of creating component-specific variables on the Layout layer. For example, a variable like $panel-margin is neither part of a general layout system, nor needed on the Component layer if the general $il-margin- variables are sufficient.
* A specific page layout is a component and not a general concept or utility and therefor MUST not be on this layer.

### Examples
* General spacing variables
* The spacing of Bulky Buttons and Bulky Links in Menues needs to be coordinated.
* Close Button in Tool Slate and the Collapse Button in Slates should be on the same vertical line.
* A future ILIAS grid or flexbox system that is independent from Bootstrap 3.

### Non-Examples
* The specific CSS grid of the standardpage belongs on the Component layer as it is one of potentially many alternatives (like a kiosk mode layout). Instead the standardpage component SHOULD use general concepts and utilities from the Layout layer to create the specific page appearance.

## Elements

The basic styling of all unclassed HTML-elements has to be placed in the Elements layer. It covers the commonalities among components and provides consistent baseline styling of typgraphy, forms, images, embeds and other HTML elements.

### Guidelines
* This layer **MUST output CSS code for unclassed HTML elements only** and MUST NOT output css code for classes.
* This layer SHOULD use variables from the Settings extensively.
* Components SHOULD only add classes and specific styling to HTML elements on lower layers if a variation from the baseline look is required.
* This layer SHOULD NOT define variables, mixins and fuctions to be used on lower layers.

### Examples
* Define the bullets that are used in lists.
* Define a general look for links.
* Define how headline-elements use variables for fonts and sizes.

## Components

CSS classes and local variables for each specific, individual component are contained on the Component layer.

A component should be a UI component from the UI framework. Currently, we also have legacy Module and Service components as well as some some legacy code that has been grouped to form a component like unit, but both of these should dissolve into modern UI components in the future.

### Guidelines

* Colors, sizes and fonts SHOULD reference global variables, functions, mixins and extensible classes from other layers.
* A component MUST be independent from other components. It MAY mix and combine elements from the upper layers to create classes that MUST be used in the html template of this specific component only.
* This layer SHOULD only define its own CSS properties or overwrite styles from the Elements layer if strictly required.

### Examples
* Build a class for the panel to set background color, border and font-style based on mixins from Tools and global variables from Settings.

### Non-Examples:
* Define font-families or sizes in px for a component. This is supposed to be done on higher layers.

## Hacks and Tweaks

As every developer knows: sometimes we cannot solve a problem right away and we need a hack or tweak that temporarily fixes the issue. This is the layer where this code goes.

Every bit of code that is contained here should be considered a smell that is worth fixing.

### Guidelines
* **This layer SHOULD be empty.**
* Temporary patches that affect very specific locations in the DOM or override some specific CSS inherited from somewhere MUST go here.
* `!important` MUST NOT be used outside of this layer and SHOULD not be used here either.

# Colors

* Outside of the Settings layer, all colors MUST be defined by using variables and you MUST NOT assign hex, rgb, hsl or other independent color values in lower layers. This means an attribute like `color: #564` is not allowed outside of Settings.
* You MAY use functions (like `color: darken(@other-variable, 10%)`) to create new colors only on the Settings and Tool layers and SHOULD NOT use such functions on lower layers.
* You SHOULD create new color shades only if there is no other suitable option available yet.
* Colors can be reassigned to other variables, but the values  MUST NOT be changed anymore. E.g. `@il-modal-bg` can be defined outside the Settings and Tools,  but should be assigned directly to a variable from Settings ad Tools.  E.g. `il-modal-bg: @il-primary-container-bg`, and not `il-modal-bg: darken(@il-primary-container-bg, 15%)` or similar.
* Shortforms MUST be used in Sass and CSS. E.g. `#efe`, instead of `#eeffee`.
* Only use lowercase for color codes in Sass and CSS. E.g. `#efe`, instead of `#EFE`.
* You SHOULD use the already existing extended color variants to generate colors for components displaying areas or labels that need to be differentiated by colors such as charts.

# Font sizes
* Font sizes **MUST be defined using the unit rem**, so accessibility options of the browser can easily scale all text in ILIAS.
* You SHOULD use the font size variables from the settings layer.

# Sizes, spacings and units

* When defining margins, paddings, widths and heights you SHOULD use variables from the Settings, Tools or Layout layer whenever possible.
* For margins and paddings you SHOULD use the unit px, so users relying on the accessibility options of their browser can scale text separately from the design.
* For widths and heights you SHOULD use (in order from highly to least recommended) flexbox and grid systems from Layouts or Dependencies, the units %, vw or vh, px.

# Media Queries

* Media queries SHOULD directly be added into the Sass structure.
* `Delos_sm` files are deprecated and MUST NOT be created anymore.
* You should use: `max-width: @il-grid-float-breakpoint-max` instead of `min-width: @grid-float-breakpoint (or min-width: @screen-sm-min)`. With `max-width`, the mobile version is declared as the special case version (desktop first).

# Naming Conventions

Many existing names do not follow these naming conventions, but future projects MUST use the following guidelines.

## File names

* Inside their layer files MUST be prefixed with an `_` underscore to mark them as a partial (this stops the compiler from compiling the file on its own).
* Generally, files MUST be named like this: `_layer_name-of-element-or-system.scss`

An exception is the Dependency layer:
* Files inside the "unmodified" folder MUST be kept in their original state.
* Files inside "modification" SHOULD copy file name and folder structure from the original package.
* Files inside "modification" MAY include prefixes like "additions" or "modified" to indicate if they are meant to be included in addition to the original dependency or fully replace a file from the original.

## BEMIT basics

Class, variable and function names must make use of [BEMIT conventions](https://csswizardry.com/2015/08/bemit-taking-the-bem-naming-convention-a-step-further/).

In the future, names MUST be constructed from the following parts:

### 1 - The ITCSS layer
* SHOULD be indicated by a prefix

    * `il-` = Settings
    * `t-` = Tools (extensible classes)
    * `l-` = Layout
    * `c-` = Components

### 2 - Block
* Next, the name of the root of the component, tool or system SHOULD follow e.g. `c-button, c-panel, l-spacing`

### 3 - Element
* If the attributes are applied to sub-elements of a root block, the name of the sub-element MUST be attached with `__` two underscores `c-button__caret`.
* You SHOULD NOT indicate a chain of many sub-elements, if a shortened version is sufficiently clear (just `c-panel__header` instead of `c-panel__wrapper__panel-body__header`)

### 4 - Modifier

* Specific conditions or contexts that modify an existing element or block MUST be attached with `--` two dashes directly after the block or element that causes the modified style.
* Modifiers SHOULD NOT be visual descriptions like `--large`, they SHOULD indicate a semantic concept or status. Examples: `c-panel--alert__header, c-button--primary`

## Classes

This is an example for a class name following BEMIT guidelines:

```markdown
c-panel--dashboard__header--alert
1 2    3            4       3
```

1 ITCSS Prefix
2 BEMIT Block
3 BEMIT Modifier
4 BEMIT Element

# Variables, Mixins and Functions

* For now, variables MUST have a unique name and be treated as global as some approaches to create custom skins require this.
* The only exception are strictly local variables inside functions and mixins, which MAY have a non-unique name (e.g. omitting the layer and block prefix).
* Long variable names SHOULD be avoided. However, here is an extreme example for all possible segments of a variable's name:

```markdown
c-button--primary--hover__glyph--expand__color
1 2       3               4    3            5
```

1 ITCSS Prefix
2 BEMIT Block
3 BEMIT Modifier
4 BEMIT Element
5 Single attribute or name of attribute group (e.g. border)

* The name MUST contain the specific attribute that the variable is for. This MUST indicate a specific CSS attribute or semantic group or concept.

# CSS Attributes

* You SHOULD aim to write as little Sass as possible.
* You SHOULD use existing logic from Settings, Tools, Layout and Dependencies whenever possible.
* If you need to add custom styling, you SHOULD use the following ordering for your attributes (see [Concentric
CSS](https://rhodesmill.org/brandon/2011/concentric-css/)):

```CSS
#Concentric-CSS-Overview {
        display: ;    /* Directions about where and how the box is placed */
        position: ;
        float: ;
        clear: ;

        visibility: ; /* Next: can the box be seen? */
        opacity: ;
        z-index: ;

        margin: ;     /* Layers of the box model, from outside to inside */
        outline: ;
        border: ;
        background: ; /* (padding and content BOTH get the background color) */
        padding: ;

        width: ;      /* Content dimensions and scrollbars */
        height: ;
        overflow: ;

        color: ;      /* Textual content */
        text: ;
        font: ;
}
```
# SASS best practices

## Importing files
Keep in mind that paths in CSS are relative to the compiled CSS file at templates/default/. Paths in Sass @use, @forward or @import (deprecated) are relative to the Sass file.
* You MUST include partials with @use and/or @forward.
* You MUST NOT use the deprecated @import.
* When including a file with @use you SHOULD utilize a namespace. You MAY use the one generated by default or define a custom namespace.
* When including a file with @use from a dependency you MUST use a namespace. This namespace SHOULD be the same everywhere e.g. the namespace for Bootstrap 3 is btstrp3.
* @forward SHOULD only be used for settings and dependencies made public to the skin customization.
    * Consequently, when including delos.scss in a skin, variables may be overriden by importing the entire delos skin like this `@use "../some-path/delos" with ( $il-main-color: green );`.
    * These exposed variables MUST have a unique name.
    * You MAY hide variables when forwarding a component with `@forward "some-component" hide $local-variable;`.
* Files that are meant to only be compiled inside other files and never on their own (which is almost all of them) MUST be marked as partials by adding "_" as a prefix. This stops the compiler from compiling the file in "watching" mode.

## Error handling
* Functions SHOULD throw an error if an incorrect input can be detected.

## Deprecated slash division

Division with a slash (e.g. "10px / 2") outside of calc() is deprecated and MUST NOT be used. You MUST use math.div() instead or multiplication (e.g. "$il-padding-small / 2" could be substituted with "$il-padding-small * 0.5")

## CSS Guideline

CSS is obtained by using the latest Dart Sass compiler on delos.scss, e.g. like so:

```
sass templates/default/delos.scss templates/default/delos.css
```

Note that the output heavily depends on the used sass version. You MUST use the latest release version of Dart Sass: https://github.com/sass/dart-sass/releases

If you observe that there are changes appearing in your css output other than the ones to be expected, please first make sure that you are using the latest Sass version. 

If you have any questions about style code and how to contribute in the most optimal way, please contact the maintenance coordinators or ask in the CSS Squad channel on the official ILIAS Discord server.