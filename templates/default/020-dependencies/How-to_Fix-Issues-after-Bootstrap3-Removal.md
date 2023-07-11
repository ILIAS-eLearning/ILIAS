In June 2023 Bootstrap 3 has been removed as a dependency. Instead we now use...

* our own UI concepts written specifically for ILIAS
* merged, shortened and customized code snippets from Bootstrap 3 directly on the appropriate ITCSS layer
* very specific parts of Bootstrap 5 or other frameworks for a very specific component, again customized and cut down where possible.

As a result of this big shift you may find areas in ILIAS 9 that look broken or unstyled compared to ILIAS 8. Please report such issues here https://mantis.ilias.de/ using the tag "no-bs".

This guide explains the process of fixing such an issue.

# Preparation

It's recommended to have two instance of ILIAS at hand while you work:

1. trunk (as the abse for your work)
2. release_8

The following resources can be very helpful:

* On Github, the Bootstrap 3 SASS implementation: https://github.com/twbs/bootstrap-sass/tree/master/assets/stylesheets/bootstrap
* A modern implementation of the area you want to fix e.g. from Bootstrap 5 (https://github.com/twbs/bootstrap/tree/main/scss) or another framework to serve as inspiration.

# Process of Fixing an Issue

First, you might want to
* inspect the broken element and check in the Bootstrap 3 if this class has been styled there (e.g. btn-group).
* identify which Bootstrap component used to contain the now missing code (e.g. button-group.less), check where it would go in the ITCSS structure of ILIAS 9.

If code for this class exists in Bootstrap 3, chances are hight that you can immediately fix the issue by copy and pasting the code of the component from the SASS version of Bootstrap 3 (e.g. from _button-group.scss) into an appropriate place in our ITCSS structure (e.g. 070-component/legacy/_btn-group.scss). However, we don't want to reintroduce unnecessary code that we just got rid of, so please follow these guidelines:

* Write a solution specific to ILIAS with as few lines of code as possible.
* Heavily cut down the code you are copying from Bootstrap 3 back into our codebase. Check if any html template uses a class. If it doesn't, it can very likely be removed.
* Instead of using the Bootstrap 3 version, check if Bootstrap 5 or another framework has a useful or smarter solution for this problem and (if the license allows it) use selected snippets from this code instead.
* Replace framework variables/mixins by ILIAS variables/mixins wherever possible.
* if a variable is very specific, consider defining it on the lowermost possible level e.g. $component-bg-color does not need to be in the settings layer and can be in the same file as the component
* For missing mixins consider either
  * using an exisiting ILIAS mixin from tools or layout if it accomplishes the same or can be quickly adapted/extended.
  * turning them into general tools or layout files by copying/mergin them into the correct file or location in our ITCSS structure.
* Put files only in a folder named "legacy" if it's not to be used in the future.

Give credit for sections included from other frameworks like this:

``` SCSS
// section based on bootstrap 3 - see /templates/default/Guidelines_SCSS-Coding.md

.bootstrap-class {}

// end of section based on bootstrap 3
```

Link the license from this inline comment or (as shown in the example) in the dependency section of the file /templates/default/Guidelines_SCSS-Coding.md

# Contact

If you have any questions, please ask in the CSS Squad channel of the ILIAS Discord server: https://discord.gg/7gGgBMSHUQ