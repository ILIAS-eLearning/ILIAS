 Image Browser plugin for TinyMCE
---------------------------------

Installation instructions:
  * Copy the ibrowser directory to the plugins directory of TinyMCE (/jscripts/tiny_mce/plugins).
  * Add plugin to TinyMCE plugin option list example: plugins : "emotions".
  * Add the ibrowser button name to button list, example: theme_advanced_buttons3_add : "ibrowser".

Initialization example:
  tinyMCE.init({
    theme : "advanced",
    elements: "ta",
    mode : "exact",
    plugins : "ibrowser",
    theme_advanced_buttons3_add : "ibrowser"
  });

---------------------------------
this version is patched
seems to work in nearly all browsers now
sk@mandarin-medien.de
(02-2005)