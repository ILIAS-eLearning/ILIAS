# ILIAS images

All images used in the default system style of ILIAS are stored in the directory /images
and its sub-directories. They are part of the visual appearance of ILIAS.


## Icons


### Principles

* We ensure a uniform icon language. That means: A PR is needed for each new icon. CSS Maintainer acts as gatekeeper for such PRs. In each case, the icon designer (Caroline Wanner), is consulted to determine whether the icon complies with the Milos icon design rules.
* We do our best to design the icons according to the guidelines. That means: Others may also design icons, but our icon designer keeps an eye on them and provides advice.

### Design guidelines

#### General
* In principle, the icons are designed with significantly more line than area. The area often serves as an accent.
* The design of the icons is based on a visual vocabulary and a certain grammar. There are rules and design guidelines, as well as recurring elements (More precise details will be added gradually).
* Equal is treated equal as well as possible.
* The icon set meets the requirements for different functionalities (f.e. size, transparencies, colorings) and areas of use (f.e. content page, mainbar, tiles).

#### Composition
* The icons were originally created in the format 320 x 320 px .
* The lines are created in a line width of 15 px when designing, then converted to areas and joined together to form a large shape. There are a few exceptions, in cases where the 15 px just didn't fit.
* In each file there is an invisible frame in this format. This prevents line width differences due to different resizing or automatic scaling.
* The icons are created in one color (grey value not lighter than #636363).
* There are no white areas.
* The background is transparent.


### Customizing

Via ILIAS Administration the icon colors can be adapted via the GUI or icons can be replaced individually. Go to «Administration > Layout and Navigation > Layout and Styles > System Styles > Edit a System Style > Icons».

If you want to develop your own icons (e.g. for plugins), make sure that they fit into the ILIAS icon system. Please follow the guidelines mentioned above.

The original files of the icons in Adobe Illustrator format are available in the following directory: https://github.com/ILIAS-eLearning/ILIAS-images







