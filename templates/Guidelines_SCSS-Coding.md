# General

*Please note, that you need to provide a Pull Request for all CSS related changes done in this folder. The CSS Coordinators
will then review the PR and give you feedback and/or merge your canges.*

This section of the codebase defines the design and layout of ILIAS. All CSS code is build here. HTML templates can be found elsewhere (see section "HTML").

The following guidelines will help you to
* find your way through the folder structure,
* understand the different layers that keep our style code organized,
* and learn what standards and best practices are expected.

Code contributions to the ILIAS repository containing style code will be reviewed according to the rules and recommendations defined here.

## SASS in SCSS syntax generates CSS

Style code MUST be written in the SCSS syntax of the Sass pre-processer. The Sass pre-processor compiles the entry point delos.scss and all connected files to the one delos.css file which can then be rendered by the browser.

Sass/CSS is responsible for the design of the website. All colors, font sizes, spacings, offsets, gaps, as well as the proportions of the basic layout MUST be defined by Sass/CSS.

Sass extends the CSS language, adding features that allow variables, mixins, functions and many other techniques that allow you to make CSS that is more maintainable, themable and expendable (see: [sass-lang.com](http://sass-lang.com/)).

You SHOULD consult the [official Sass Documentation](https://sass-lang.com/documentation/) to make use of the advantages of Sass.

When contributing style code to the ILIAS repository, you MUST first compile the Sass code to CSS using a recent version of the Dart Sass. You MUST NOT make changes to the compiled delos.css manually.

Delos.scss MUST only contain imports and no other Sass logic at all.

## HTML

HTML templates are only responsible for a well structured HTML document, which displays the bare content of the website and nothing else.

They can be found in `src/UI/templates/default` for modern UI components or in `Modules/` and `Services/` for legacy components.

You MUST NOT use style attributes like style, align, border, cellpadding, cellspacing font, nowrap, valign, width, height and similar in HTML templates.

You MUST NOT use `&nbsp;` to create space.

## Bootstrap

Bootstrap 3 has been used since ILIAS 5.0 to solve many common web design challenges like normalizing, column layouts and input elements.

At the moment, Bootstrap classes and mixins SHOULD be used where possible.

In the future, many systems in Bootstrap 3 will be replaced by our native solutions specifically customized to the needs of ILIAS. In 2022, Bootstrap 3 has been updated to the [official Bootstrap 3 Sass port](https://github.com/twbs/bootstrap-sass).

# ITCSS structure

Style code MUST be segmented into the following layers based on the [Inverted Triangle CSS (ITCSS)](https://www.xfive.co/blog/itcss-scalable-maintainable-css-architecture/) structure:

1. settings
2. dependencies
3. tools
4. normalize
5. layout
6. elements
7. components
8. hacks and tweaks

The reach of the code MUST get less global and more specific the deeper we get in this layer structure. Settings MAY effect all layers below. Components MUST only effect itselves.

## Settings layer

The Settings layer MUST only contain global variables that will be used throughout the whole system e.g. colors, fonts, font-sizes and the most important spacings.

These variables MUST be the ones needed to quickly recolor and reshape ILIAS to create a new minimal, but clearly branded skin.

Variables on the settings layer that are directly tied to a single component MUST be essential to define the basic look of such a skin e.g. the height of the footer.

Variables that aren't essential to creating the basic look MUST be placed on lower layers and SHOULD be defined based on values from the settings layer.

The variables MUST be structured into separate files and logical sections.

Anything added to the settings layer MUSTN'T create CSS code on its own.

Variables on the Settings layer SHOULD be made public by adding `!default` to the definition and partial files SHOULD be added to the index with @forward, so they are public and can be overriden when the main delos.scss is loaded by a skin.

You MAY include the entire Settings layer on lower levels. You SHOULD NOT need to include individual files from the settings layer on lower levels.

There is a legacy folder that SHOULD be empty and the contents SHOULD be refactored.

Examples:
* Main color palette
* The most common font sizes
* The header height (essential for minimal skin)
* Primary and default button colors (essential for minimal skin)

Non-Examples:
* The color of a disabled button is not essential for the basic look of a minimal skin and should be carefull set following accessibility practices on a lower layer.

## Dependencies

The Dependencies layer MUST contain files pulled in from other projects and/or modifications of such files. Dependencies and their modifications MUST only be added after careful consideration whether the benefits outweigh the risks.

The "unmodified" folder MUST only contain unmodified dependencies (or unmodified parts of them). They MAY be updated with new official releases of the dependency after careful testing.

You SHOULD keep modifications, additions or overrides to a minimum and make them through files in the "modification" folder mirroring the original structure of the unmodified dependency. In this case you MAY load a mix of unmodified and modified files on the index file or make such a mixed configuration available for lower layers. The "modification" folder MUSTN'T hold unmodified files.

Legagcy dependencies MAY be located in other places (e.g. "node_modules"). Modifications to those dependencies with regards to style code SHOULD be done inside the "modifications" folder of the ITCSS dependency layer.

Instead of loading complete packages to our CSS as a framework you SHOULD only load selected parts of a dependency where needed using it as a library.

When including dependency files in multiple components this MUST NOT lead to repeating CSS code (which can happen when including files with @use that use @import for loading its own partials). To avoid this you MAY separate variables from CSS classes in the "modification" folder and include only the variables on lower levels. In this case, extending to CSS classes of a dependency doesn't work on lower layers, as the component is unaware of the CSS classes.

This layer only SHOULD create a dependency's utility CSS code (and MUST do so selectively only for code that is actually needed).

Lower layers MAY call the variables, functions and mixins of a dependency for their purposes and SHOULD include them with a namespace.

A dependency MAY be called on with @forward inside the _index.scss to expose its public variables to a skin.

Examples:
* Bootstrap

## Tools

The Tools layer MUST define mixins, silent extensible classes, functions, media queries and animations
that are used in lower layers of the SCSS.

They MUST provide uniform definitions for common concepts and problems, and thus foster visual homogenity in the system. They substantiate variables from the Settings into more concrete concepts.

Tools MAY be used in various other sections in the SCSS.

Past layouts and HTML templates heavily rely on CSS utility classes like "ilCenter" or "smallred" for some formatting. In the future, these kinds of CSS utility classes are deprecated and SHOULD NOT be used except for yet to be determined exceptions. These utility use cases SHOULD be covered by Tool functions and mixins that are being called on lower levels or extensions to silent Tool classes.

Tools MUST not generate CSS code before being used on lower levels.

Examples:
* Provide mixins and functions for common styling problems.
* A function to turn pixel values to rem
* A mixin that generates a palette of color shades

Non-Examples:
* A class "smallred" to style text in warnings. Instead the semantic class "warning" should be styled on Component level and MAY use colors from a Settings or Tools alert color palette.

## Normalize

Only styles needed to normalize browser behaviour to a good default base state in all browsers SHOULD be defined on the Normalize layer.

We MAY rely on dependencies e.g. for normalizing complex elements like input fields. Therefor, large portions of normalizing styles MAY be located on the Dependency layer.

Elements that are heavily styled and overwritten on lower layers anyway SHOULD NOT be normalized. Resetting paragraph margins only to overwrite them again on the Elements layer is not necessary.

This layer MAY create CSS code.

Examples:
* Set general line-heights for texts.
* Remove paddings and margins.
* Remove browser specific stylings.

## Layout

The Layout layer MUST contain silent extensible classes, variables, functions and mixins that help define the positioning and spacing of components relative to each other. Content from this layer MAY be used in the components.

If the positioning or spacing for a component can be completely based on Settings or general Layout variables, it MUST be defined in the component itself instead of creating component-specific variables on the Layout layer. For example, a variable like $panel-margin is neither part of a general layout system, nor needed on the Component layer if the general $il-margin- variables are sufficient.

You MUST NOT create utility CSS classes (e.g. "ilFloatRight") like they are used in many legacy HTML templates unless for yet to be determined exceptions. You MAY create utility functions, mixins or silent classes to be used by semantic classes on lower layers instead.

Consequently, Layout SHOULD not create CSS code unless called upon on lower layers.

A specific page layout is a component and MUST not be on this layer. Only the general concepts, utilities and patterns to build this MUST be on this layer.

Examples:
* General spacing variables
* The spacing of Bulky Buttons and Bulky Links in Menues needs to be coordinated.
* Close Button in Tool Slate and the Collapse Button in Slates should be on the same vertical line.
* A future ILIAS grid or flexbox system that is independent from Bootstrap

Non-Examples:
* The specific CSS grid of the standardpage belongs on the Component layer as it is one of potentially many alternatives (like a kiosk mode layout). Instead the standardpage component SHOULD use general concepts and utilities from the Layout layer to create the specific page appearance.

## Elements

The Elements layer MUST contain the basic styling of all unclassed HTML-elements. It provides a visual baseline for more specific components.

This layer SHOULD use variables from the Settings extensively. It MUST cover the commonalities among components that allows them to use unclassed HTML-elements. Consequently, components SHOULD only add classes and specific styling on lower layers if strictly required.

This layer MUST output CSS code for elements only and MUST NOT output css code for classes.

This layer SHOULD NOT define variables, mixins and fuctions to be used on lower layers.

Examples:
* Define the bullets that are used in lists.
* Define a general look for links.
* Define how headline-elements use variables for fonts and sizes.

## Components

The Component layer MUST contain CSS classes and local variables for each specific, individual component.

A component SHOULD be a UI component from the UI framework. Currently, we also have legacy Module and Service components as well as some some legacy code that has been grouped to form a component like unit, but both of these SHOULD dissolve into modern UI components in the future.

Colors, sizes and fonts SHOULD reference global variables, functions, mixins and extensible classes from other layers.

A component MUST be independent from other components. It MAY mix and combine stuff from the upper layers to create classes that MUST be used in the html template of this specific component only.

This layer SHOULD only define its own CSS properties or overwrite styles from the Elements layer if strictly required.

Examples:
* Build a class for the panel to set background color, border and font-style based on mixins from Tools and global variables.

Non-Examples:
* Define font-families or sizes for some components. This is supposed to be done in the Settings layer.

## Hacks and Tweaks

This layer SHOULD be empty. But as every developer knows: sometimes we cannot solve a problem right away and we need a hack or tweak that temporarily fixes the issue. This is the layer where this code goes.

Temporary code that affect very specific locations in the DOM or override some specific CSS inherited from somewhere MUST go here. Every bit of code that is contained here SHOULD be considered a smell that is worth fixing.

`!important` MUST NOT be used outside of this layer and SHOULD not be used here either.

# Colors

* Outside of the Settings layer, all colors MUST be defined by using variables and you MUST NOT assign hex, rgb, hsl or other independent color values in lower layers. This means an attribute like `color: #564` is not allowed otside of Settings.
* You MUST use functions (like `color: darken(@other-variable, 10%)`) to create new colors only on the Settings and Tool layers. You SHOULD create new color shades only if there is no other suitable option available yet.
* In Settings, all new color values MUST be defined in the colors file.  Later those values can be reassigned to other variables, but the values  MUST NOT be changed anymore. E.g. `@il-modal-bg` can be defined outside the colors section,  but should be assigned directly to a variable from the colors section.  E.g. `il-modal-bg: @il-primary-container-bg`, and not `il-modal-bg: darken(@il-primary-container-bg, 15%)` or similar.
* Shortforms MUST be used in less and CSS. E.g. `#efe`, instead of `#eeffee`.
* Only use lowercase for color codes in less and CSS. E.g. `#efe`, instead of `#EFE`.
* You SHOULD use the already existing extended color variants to generate colors for components displaying areas or labels that need to be differentiated by colors such as charts.

# Naming Conventions

Many existing names do not follow this naming conventions, but future projects MUST use the following guidelines.

## File names

Inside their layer files MUST be prefixed with an `_` underscore to mark them as a partial (this stops the compiler from compiling the file on its own).

Generally, files MUST be named like this: `_layer_name-of-element-or-system.scss`

An exception is the Dependency layer: Files inside the "unmodified" folder MUST be kept in their original state. Files inside "modification" SHOULD copy file name and folder structure from the original package. They MAY include prefixes like "additions" or "modified" to indicate if they are meant to be included in addition to the original dependency or fully replace a file from the original.

## BEMIT basics

Class, variable and function names MUST make use of [BEMIT conventions](https://csswizardry.com/2015/08/bemit-taking-the-bem-naming-convention-a-step-further/).

1 - Prefix: The names MUST start with a prefix inidcating the layer that they are from:

* `il-` = Settings
* `t-` = Tools (extensible classes)
* `l-` = Layout
* `c-` = Components

2 - Block: Next, the name of the root of the component or system MUST follow e.g. `c-button, c-panel, l-spacing`

3 - Element: If the attributes are applied to a sub-element of the root block, the name of the sub-element MUST be attached with `__` two underscores `c-panel__header, c-button__caret`

4 - Modifier: Specific conditions or contexts that modify an existing element or block MUST be attached with `--` two dashes directly after the block or element that causes the modified style. Modifiers SHOULD NOT be visual descriptions like `--large`, they SHOULD indicate a semantic concept or status. Examples: `c-panel--alert__header, c-button--primary`

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

For now, variables MUST have a unique name and be treated as global as some approaches to create custom skins require this. The only exception are local variables (in a mixin or function) or variables hidden from being forwarded, which MAY have a generic non-unique name.

Long variable names SHOULD be avoided. However, here is an extreme example for all possible segments of a variable's name:

```markdown
c-button--primary--hover__glyph--expand__color
1 2       3               4    3            5
```

1 ITCSS Prefix
2 BEMIT Block
3 BEMIT Modifier
4 BEMIT Element
5 The name MUST contain the specific attribute that the variable is for. This MUST indicate a specific CSS attribute or semantic group or concept.

# CSS Attributes

* You SHOULD aim to write as little Sass as possible. You SHOULD use existing logic from Settings, Tools, Layout and Dependencies whenever possible.
* If you need to add custom styling, you SHOULD use the following ordering for
your attributes (see [Concentric
CSS](https://rhodesmill.org/brandon/2011/concentric-css/)): Concentric
CSS/Less Overview.

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

Files that are meant to only be compiled inside other files and never on their own (which is almost all of them) MUST be marked as partials by adding "_" as a prefix. This stops the compiler from compiling the file in "watching" mode.

You MUST include partials with @use or @forward. You MUST NOT use the deprecated @import.
@forward SHOULD only be used for settings and dependencies made public to the skin. Consequently, when including delos.scss in a skin, variables MAY be overriden by importing the entire delos skin like this `@use "../some-path/delos" with ( $il-main-color: green );`. These exposed variables MUST have a unique name. You MAY hide variables when forwarding a component with `@forward "some-component" hide $local-variable;`.

When including a file with @use you SHOULD utilize a namespace. You MAY use the one generated by default or define a custom namespace.

When including a file with @use from a dependency you MUST use a namespace. This namespace SHOULD be the same everywhere e.g. the namespace for Bootstrap 3 is btstrp3.

Division with a slash (e.g. "10px / 2") outside of calc() is deprecated and MUST NOT be used. You MUST use math.div() instead or multiplication (e.g. "$il-padding-small / 2" could be substituted with "$il-padding-small * 0.5")

Functions SHOULD throw an error if an incorrect input can be detected.

# Media

* Media queries SHOULD directly be added into the Sass structure. `Delos_sm`
files are deprecated and MUST NOT be created anymore.
* We currently follow a desktop first approach on the Sass level. This means,
that we handle all mobile cases as special cases and the desktop as the default.
* You should use: `max-width: @il-grid-float-breakpoint-max` instead of `min-width: @grid-float-breakpoint (or min-width: @screen-sm-min)`. With `max-width`,  the mobile version is declared as the special case version (desktop first).

## CSS Guideline

CSS is obtained by using a sass compiler on delos.scss, e.g. like so:

```
sass templates/default/delos.scss templates/default/delos.css
```

Note that the output heavily depends on the used sass version. You MUST use a current version of Dart Sass.

If you observe that there are changes appearing in your css output other than the ones to be expected, please first make sure, that you are using the latest Sass version. 

If you have any questions about style code and how to contribute in the most optimal way, please contact the maintenance coordinators or ask in the CSS squad channel on the official ILIAS Discord server.