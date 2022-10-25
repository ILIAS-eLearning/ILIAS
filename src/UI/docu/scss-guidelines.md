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

The Settings layer MUST only contains global variables that will be used throughout the whole system e.g. colors, fonts, font-sizes and the most important spacings.

These variables MUST be the ones needed to quickly recolor and reshape ILIAS to create a new minimal, but clearly branded skin.

Variables on the settings layer that are directly tied to a single component MUST be essential to define the basic look of such a skin e.g. the height of the footer.

Variables that aren't essential to creating the basic look MUST be placed on lower layers and SHOULD be defined based on values from the settings layer.

Anything added to the settings layer MUSTN'T create CSS code on its own.

You MAY include the entire Settings layer on lower levels. You SHOULD NOT need to include individual files from the settings layer on lower levels.

There is a legacy folder that SHOULD be empty and the content needs to find a permanent place elsewhere.

Examples:
* Main color palette
* The most common font sizes
* The header height (essential for minimal skin)
* Primary and default button colors (essential for minimal skin)

Non-Examples:
* The color of a disabled button is not essential for the basic look of a minimal skin and should be carefull set following accessibility best practices on lower layers

## Dependencies

The Dependencies layer MUST contain files pulled in from other projects and/or modifications of such files. Dependencies and their modifications MUST only be added after careful consideration whether the benefits outweigh the risks.

Only complete, unmodified dependencies MUST be added in the "unmodified" folder. They MAY be updated with new official releases of the dependency after careful testing.

You SHOULD keep modifications, additions or overrides to a minimum and make them through files in the "modification" folder mirroring the original structure of the unmodified dependency. In this case you MAY load a mix of unmodified and modified files. The "modification" folder MUSTN'T hold unmodified files.

Legagcy dependencies MAY be located in other places (e.g. "node_modules"). Modifications to those dependencies with regards to style code SHOULD be done inside the "modifications" folder of the ITCSS dependency layer.

Instead of loading complete packages to our CSS as a framework you SHOULD only load selected parts of a dependency where needed using it as a library.

When including dependency files in multiple components this MUST NOT lead to repeating CSS code (which can happen when including files with @use that use @import for loading its own partials). To avoid this you MAY separate variables from CSS classes in the "modification" folder and include only the variables on lower levels. In this case, extending to CSS classes of a dependency doesn't work on lower layer, as the component is unaware of the CSS classes.

This layer only SHOULD create a dependency's utility CSS code (and MUST do so selectively only for code that is actually needed).

Lower layers MAY call the variables, functions and mixins of a dependency for their purposes and SHOULD include them with a namespace.

Examples:
* Bootstrap

## Tools

The Tools layer MUST define mixins, silent extensible classes, functions, media queries and animations
that are used in lower layers of the SCSS.

Tools MAY be used in various other sections in the SCSS. They MUST provide uniform definitions for common concepts and problems, and thus foster visual homogenity in the system. They substantiate variables from the Settings into more concrete concepts.

Past layouts and HTML templates heavily rely on CSS utility classes like "ilCenter" or "smallred" for some formatting. In the future, these kinds of CSS utility classes are deprecated and SHOULD NOT be used except for yet ro be determined exceptions. These utility use cases SHOULD be covered by Tool functions and mixins that are being called on lower levels or extensions to silent Tool classes.

Tools MUST not generate CSS code before being used on lower levels.

Examples:
* Provide mixins and functions for common styling problems.
* A function to turn pixel values to rem
* A mixin that generates a palette of color shades

Non-Examples:
* A class "smallred" to style text in warnings. Instead the semantic class "warning" should be styled on Component level and MAY use colors from a Settings or Tools alert color palette.

## Normalize

The Normalize layer contains styles that SHOULD normalize browser behaviour to a good default base state in all browsers.

We MAY rely on dependencies e.g. for normalizing complex elements like input fields.

Elements that are heavily styled and overwritten on lower layers anyway SHOULD NOT be normalized. Resetting paragraph margins only to overwrite them again on the Elements layer is not necessary.

This layer MAY create CSS code.

Examples:
* Set general line-heights for texts.
* Remove paddings and margins.
* Remove browser specific stylings.

## Layout

The Layout layer MUST contain silent extensible classes, variables, functions and mixins that define the positioning and spacing of components relative to each other. Content from this layer MAY be used in the components.

Whenever positioning and spacing needs to be coordinated between various components the code MUST be contained here. If a positioning or spacing can be defined in a component or solely be based on global variables, it MUST go into the Component layer instead.

You MUST not create utility CSS classes (like "ilFloatRight") like they are used in many legacy HTML templates unless for yet to be determined exceptions. You MAY create utility functions, mixins or silent classes to be used by semantic classes on lower layers instead.

Consequently, Layout SHOULD not create CSS code unless called upon on lower layers.

A specific page layout is a component and MUST not be on this layer. Only the general concepts, utilities and patterns to build this layout MUST be on this layer.

Examples:
* The spacing of Bulky Buttons and Bulky Links in Menues needs to be coordinated.
* Close Button in Tool Slate and the Collapse Button in Slates should be on the same vertical line.
* A future ILIAS grid or flexbox system that is independent from Bootstrap

Non-Examples:
* The specific CSS grid of the standardpage belongs on the Component layer as it is one of potentially many alternatives (like a kiosk mode layout). Instead the standardpage component SHOULD use general concepts and utilities from the Layout layer to create the specific page appearance.

## Elements

The Elements layer MUST contain the basic styling of all unclassed HTML-elements. It provides a visual baseline for more specific components.

This layer SHOULD use variables from the Settings extensively. It MUST cover the commonalities among components that allows them to use unclassed HTML-elements. Consequently, components SHOULD only add classes and specific styling on lower layers if strictly required.

This layer SHOULD output CSS code for elements but MUST NOT output css code for classes.

This layer SHOULD NOT define variables, mixins and fuctions to be used on lower layers.

Examples:
* Define the bullets that are used in lists.
* Define a general look for links.
* Define how headline-elements use variables for fonts and sizes.

## Components

The Component layer MUST contain CSS classes and local variables for each specific, individual component.

A component SHOULD be a UI component from the UI framework. Currently, we also have legacy Module and Service components as well as some some legacy code that has been grouped to form a component like unit, but both of these SHOULD dissolve into modern UI components in the future.

Colors, sizes and fonts MUST reference global variables, functions, mixins and extensible classes from other layers.

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

# File names

Inside their layer files MUST be prefixed with an `_` underscore to mark them as a partial (this stops the compiler from compiling the file on its own).

Generally, files MUST be named like this: `_layer_descriptive-name.scss`

An exception is the Dependency layer: Files inside the "unmodified" folder MUST be kept in their original state. Files inside "modification" SHOULD copy file name and folder structure from the original package. They MAY include prefixes like "additions" or "modified" to indicate if they are meant to be included in addition to the original dependency or fully replace a file from the original.

# Naming Conventions

Many existing names do not follow this naming conventions, but future projects MUST use the following guidelines.

## BEMIT basics

Class, variable and function names MUST make use of BEMIT conventions.

1 - Prefix: The names MUST start with a prefix inidcating the layer that they are from:

* `il-` = Settings
* `t-` = Tools (extensible classes)
* `l-` = Layout
* `c-` = Components

2 - Block: Next, the name of the root of the component or system MUST follow e.g. `c-button, c-panel, l-spacing`

3 - Element: If the attributes are applied to a sub-element of the root block, the name of the sub element MUST be attached with `__` two underscores `c-panel__header, c-button__caret`

4 - Modifier: Specific conditions or contexts that modify an existing element or block MUST be attached with `--` two dashes directly after the block or element that causes the modified style. Modifiers SHOULD NOT be visual descriptions like "--large", they SHOULD indicate a semantic concept or status. Examples: `c-panel--alert__header, c-button--primary`

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


# SASS best practices

Files that are meant to only be compiled inside other files and never on their own (which is almost all of them) MUST be marked as partials by adding "_" as a prefix. This stops the compiler from compiling the file in "watching" mode.

You MUST include partials with @use. You MUST NOT use the deprecated @import.

Components MUST expose their variables with @forward all the way up to the main delos.scss so that when including delos in a skin, variables can be overriden by importing the entire delos skin like this `@use "../some-path/delos" with ( $il-main-color: green );`. This makes most variables public, so these exposed variables MUST have a unique name. You MAY hide variables when forwarding a component with `@forward "some-component" hide $local-variable;`

When including a file with @use you SHOULD utilize a namespace. You MAY use the default on or define a custom namespace.

When including a file with @use from a dependency you MUST use a namespace. This namespace SHOULD be the same everywhere e.g. the namespace for Bootstrap 3 is btstrp3.

Division with a slash (e.g. "10px / 2") outside of calc() is deprecated and MUST NOT be used. You MUST use math.div() instead.

## Functions

Functions SHOULD throw an error if an incorrect input can be detected.