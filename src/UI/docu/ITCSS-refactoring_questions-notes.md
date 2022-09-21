# Questions

* rtl into normalize?
* script in tags or normalize?
* tables.less, font.less, print.less on multiple layers?
* is body and html layout or elements?
* Modules/Course/delos.less contains general tools
* should UI Framework stylecode be in components layer folder?
* Bootstrap - mix 3 and 5 as linked dependency instead of pulling it all in now?
* should modules and service files still be called delos.less?

# Guidelines
To use a general tool or layout class
* use the variable in the semantic class: .my-box { margin: @margin-small; }
* or extend to a tool/layout class: .my-box { @extend(.margin-small)}