# Roadmap
The System Styles, most importantly, the CSS contained in the System Styles are currently undergoing structural changes and new guidelines are currently being discussed and written. They grew too large over time to be handled by one single developer and would therefore highly benefit from contributions among different developers and even service providers. They are of modular structure with different parts following a similar scheme. Further, they are critical importance for many other components. Therefore, we consider them suited perfectly to be maintained by the Coordinator Model, see: https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/maintenance-coordinator.md

This Roadmap is therefore a first step to meet the requirements listed above for such a maintenance.

## Frameworkless SASS for the UI-Framework
A proposal for better structuring the System Styles has been provided and accepted by the JF in 2021, see: https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/src/UI/docu/sass-guidelines.md

Since October 2022 the SCSS as been restructered according to the ITCSS structure suggested by this proposal, but is not yet independent from Bootstrap.