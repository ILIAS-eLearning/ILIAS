# Preparation

You might want have two instance of ILIAS at hand while you work:

1. trunk
2. this work in progress branch

In a browser tab you might want to have the following resources available

* Bootstrap 5 Github branch to look up more modern implementations

# State of this branch

* the Bootstrap 3 files are still present in the working branch (spread across modifications/bootstrap-3-scss and unmodified/bootstrap-3-scss), but almost none of them are being loaded.
* some Bootstrap 3 files have already been merged into ILIAS layout, tools and components (always deleting Bootstrap 3 code that wasn't being used).
* There is a detailed lists of to dos at the end of this document.


# Workflow

This is a recommended workflow

* find an area in the working branch instance that looks different/broken
* check in trunk instance which bootstrap component is responsible for the "correct" rendering
* copy (and split) the corresponding (currently unloaded) Bootstrap 3 code from they dependency layer into a layout, tool, legacy component, UI component (existing or new) file(s)
* alternatively use Bootstrap 5 code, snippets from another framework or your own solutions for a more modern implementation
* fence the copyrighted code between two license comments like this
  * The following section contains code from Bootstrap 3. For the text of the MIT license see https://github.com/twbs/bootstrap/blob/v3.4.1/LICENSE
  * (code)
  * end of Bootstrap 3 code
* delete any parts of the code that isn't currently being used (search if obscure sounding classes are used by any HTML Templates or set by php/js)
* deal with variables and mixins in the following way:
  * replace Bootstrap variables/mixins by ILIAS variables/mixins wherever possible ($font-size-base exists as $il-font-size-base; $brand-primary is $il-main-color; you can see which Bootstrap variables we redefines in .../020-dependencies/modifications/bootstrap-3-scss/stylesheets/bootstrap/_modified-variables.scss, but also check our general color, spacing, border etc. variables)
  * if a variable is very specific, consider defining it on the lowermost level e.g. $component-bg-color does not need to be in the settings layer and can be in the same file as the component
  * for missing mixins consider either
    * using an exisiting ILIAS mixin from tools or layout if it accomplishes the same or can be quickly adapted/extended
    * turning them into general tools or layout files by copying/mergin the code into our ITCSS structure
* Now the SASS compiler should be able to compile the code.

Do not utilize `@use "[...]/020-dependencies/modifications/bootstrap-3-scss/bootstrap-3-scss-modified-variables-mixins" as *;` or similar to quickly make the Bootstrap variables work. This connection to Bootstrap has to be cut as well as a goal of this project.

# To Dos

These Bootstrap parts definitely need to be merged/fixed/adapted:

* [ ] forms
* [ ] btn-group
* [ ] panel? (UI component seems to already have all relevant code to work, check if same is true for legacy panel)
* [ ] responsive variables / mixins have to be turned into a general layout file (maybe only currently in use variables from Bootstrap 3? Bootstrap 5 seems too complex)

To Dos left over / caused by already merged parts:

* [ ] some shadow mixins have been sloppily deactivated and need to be restored (mixin in tools?)
* [ ] nav bar / toolbar padding is deactivated/broken, needs fix