# System Styles

The templates folder of ILIAS contains the ILIAS System Styles. System Styles
are defined by the set of icons, fonts, html templates and CSS/SCSS files that
define the visual appearance of ILIAS. They differ from Content Styles, which
enable to manipulate the classes defining the appearance of user generated
content.

## Custom Styles

System Styles may be customized by creating custom System Styles. Custom styles have
to be placed in the `./Customizing/global/skin` directory to be active. One may have
multiple substyles which may be active for different branches of the repository.

A GitHub Repo for a custom System Style based on the default Delos will be available
soon.

### Tools

To generate a customized System Style, first install the necessary tools to your
server. These tools include nodejs and the node packet manager. After that you
can install the sass compiler that is used to turn SCSS into CSS using:

```
npm install -g sass
```

or

Download [Dart SASS from Github](https://github.com/sass/dart-sass/releases/) and add it to the machine's PATH.

If you want to create system styles throught frontend, make sure, that your webserver
has the permission to read and execute your newly installed sass compiler.

### How-To 1 (Through Frontend)

#### Step 1: Activate "Manage System Styles"

1. Open the ilias.ini.php file in your ILIAS Administration.
2. Make sure that in the Section [tools] the setting enable_system_styles_management is activated.
3. Make sure, that a lessc is set to your lessc installation:
```
enable_system_styles_management = "1"
lessc = "/usr/local/bin/lessc"
```

#### Step 2: Create new System Style

1. Navigate to "Administration -> Layout and Styles" of you ILIAS Installation.
2. Add a new System Style and activate it.
3. Optional: Add a new Sub Style for the created System Style through the
frontend
4. Optional: Change the SCSS variables for the new System Style through the
frontend
5. Optional: Change the Icon colors of the new System Style through the frontend
6. Optional: Manually add template files for the new System Style (see "Change
Layout" below)

### How-To 2 (Manually)

#### Step 1: Create skin directory

To create a new skin, first add a new subdirectory to directory
`Customizing/global/skin`, e.g. `Customizing/global/skin/myskin`.

In the future, we will provide a base System Style based on the
default Delos that you can download from Github and place here.

#### Step 2: Create template.xml File

One file that must exist in every skin is the file template.xml. E.g.
`Customizing/global/skin/myskin/template.xml`:

```
<?xml version = "1.0" encoding = "UTF-8"?>
<template xmlns = "http://www.w3.org" version = "1" name = "MySkin">
        <style name = "MyStyle" id = "mystyle" image_directory = "images"/>
</template>
```

Every skin can contain multiple styles. This example defines one style called
MyStyle. This skin/style combination will be listed as MySkin/MyStyle in the
ILIAS Style and Layout administration. The ILIAS administration is the place
where you can activate/deactivate styles, and where you can assign users from
one skin to another.

#### Step 2: Create main CSS File

The `id` attribute of the style tag defines the name of the corresponding style
sheet (CSS) file. This CSS file must also be added to the skin directory (here:
`Customizing/global/skin/myskin/mystyle.css`). You should start with a copy of
the default CSS file located at `templates/default/delos.css`. The best way to
see which styles are used on a given ILIAS screen is to open the HTML source of
the screen.

If your CSS file contains references to (background) images, these images must
be present at their defined locations. If you copied the default CSS file, the
image paths will not be correct anymore. You can either copy them to your skin
directory, change the CSS definitions or provide your own image files.

#### Step 2: Alternative

To have a working directory for your skin, you can also copy the complete folder
templates/default of your ilias installation to a new folder below
`Customizing/global/skin` within that directory, edit the file `template.xml` to
have an unique Style Name and id. This is needed to identify the new skin in
ILIAS' administration. Then copy the standard `delos.css` file to "your-id.css".
Take care: the main CSS-File must reflect the id in its name (see above).

#### Step 3: Sass (Optional)

Note: It is usually a good idea to use Sass and Sass variables to create Custom
Styles. A good default is given by generating a first style through the
frontend and then add the changes manually.

If you copied delos.scss compiling the standard delos.scss file would fail due
to path problems. In the file delos.scss at about line 20 the base for some
imports is set. Since the files were moved to the new location, this path must
be fixed to make the imports work. prepending `../../` to the path works. At
this point, compiling should run without errors, but some icons (like the search
button) can not be displayed. To fix this, edit `less/variables.less`.

To test the settings, compile the less-file:

```
sass delos.scss mystyle.css
```

or

```
sass --style=compressed delos.scss mystyle.css
```

for a minified CSS version.


#### Step 4: Add Icons (Optional)

If you want to replace the default icons coming with ILIAS, you can add new
representations of them to your skin. They must be stored in a subdirectory
named like the `image_directory` attribute of the style tag in the
`template.xml` file.

E.g. if you want to replace the default icon for categories
`templates/default/images/icon_cat.sfg`, and your template file defines
`image_directory = "images"` as in the example above, the new version must be
stored as `Customizing/global/skin/myskin/images/icon_cat.svg`.

#### Step 5: Change Layout (Optional)

The layout is specified in HTML template files. Some standard default template
files can be found in directory `templates/default`. Other template files are
stored within subdirectories of the Modules or Services directories. Most ILIAS
screens use more than one template file. Some template files are reused in many
ILIAS screens (e.g. the template file that defines the layout of the main menu).

To replace a template file for your skin, you have to create a new one in your
skin directory. Please note, that your skin should only contain template files
that are modified. You do not need to copy all default template files to your
new skin. 

Since ILIAS 5.3 we move aim to move most of the UI towards the UI Components. They
are located in src/UI. To overwrite those you need to add the respective tpl files 
in your skins folder. 

Examples:
* `Module/Service` related template files must be stored in a similar
subdirectory structure (omit the `templates` subdirectory). E.g. to replace the
template file `Services/XYZ/templates/tpl.xyz.html` create a new
version at `Customizing/global/skin/myskin/Services/XYZ/tpl.xyz.html`. A template of a UI Component located in 
`src/UI/templates/default/XYZ/tpl.xyz.html` can be customized by creating a 
`Customizing/global/skin/myskin/UI/XYZ/tpl.xyz.html` file.

The following list contains some standard template files, that are often changed in
skins:

- [Standard Layout](https://test6.ilias.de/goto_test6_stys_21_LayoutPageStandardStandard_default_delos.html?), 
template file: src/UI/templates/default/Layout/tpl.standardpage.html, the frame of the DOM for the complete ILIAS page. 
Also checkout the according scss variable under section Layout (UI Layout Page).
- [Meta Bar](https://test6.ilias.de/goto_test6_stys_21_MainControlsMetaBarMetaBar_default_delos.html?) 
template file: src/UI/templates/default/MainControls/tpl.metabar.html, the Bar on the top holding Notification, Search User Avatar, etc.
Also checkout the according metabar scss variables.
- [Main Bar](https://test6.ilias.de/goto_test6_stys_21_MainControlsMainBarMainBar_default_delos.html?) 
template directory: src/UI/templates/default/MainControls/tpl.mainbar.html, the Bar on the left holding triggers for opening the slates for
accessing Repository, Dasbhoard etc. Content.
Also checkout the according mainbar scss variables.
- [Slate](https://test6.ilias.de/goto_test6_stys_21_MainControlsSlateFactorySlate_default_delos.html?) 
template directory: src/UI/templates/default/MainControls/Slate/tpl.slate.html, the Slates triggered by opening items of the Main Bar.
Also checkout the according slate scss variables.
- [Breadcrumbs](https://test6.ilias.de/goto_test6_stys_21_BreadcrumbsBreadcrumbsBreadcrumbs_default_delos.html?)
template directory: src/UI/templates/default/Breadcrumbs/tpl.breadcrumbs.html, Breadcrumbs working as locator on the top of the page.
Also checkout the breadcrumb scss variables.

* Startup Screens (Login, Registration, ...): `Services/Init/templates/default/tpl .startup_screen.html`


#### Step 6: Change the ILIAS Icon

The main ILIAS icon is stored in the images Directory as `HeaderIcon.svg`. You
can replace this easyly by your own Icon in svg format. As long as your Icon is
close to a square, this may be all that is needed. Probably you want to change
the file favicon `.ico` in ILIAS' root directory too. For non-square Icons you
may refer to:

[Installation and Maintenance » Change the ILIAS
icon](http://www.ilias.de/docu/goto_docu_pg_68691_367.html)


#### Optional: Configuring lessc on OSX with MAMP

This exlpains how to adjust your MAMP installation to work with System Styles in Ilias on OSX.

First you have to Install Xcode Command Line Tools. Then execute:
```
xcode-select --install
Install Node.js 
cd /Applications/MAMP/
git clone https://github.com/nodejs/node.git
cd node
./configure
make
sudo make install
Install Sass
sudo npm install -g sass
```

Edit the File Applications/MAMP/Library/bin/envars.
Add the line export PATH="/usr/bin:/bin:/usr/sbin:/sbin:/usr/local/bin" to the envars file. 
Make sure that all the other lines are commented (an # is added in front of the line).

Edit the file Applications/MAMP/Library/bin/envars_std
Make sure that every line is commented. (an # is added in front of the line).

Change the rights on the files. Open a Terminal and execute:
```
chmod -R 777 /Application/MAMP/Library/bin
```

Note that this is only a good option for a local test environment in some protected enironment. Give more sensitive
Permission rights if there is possible access from the outsite.

Activate System Styles in Ilias
In a Browser go to localhost:8888/setup/setup.php and Login using the Master-Password. Under “Basic Settings” activate “Manage System Styles”.
Set the lessc Path to /usr/local/bin/lessc


### Migration

There might be changes you need to consider if updating to a new ILIAS version.

Note that this changelog was introduced for ILIAS 5.3. If migrating to a lower
version you might find helpful information by consulting:

[Installation and Maintenance » Prepare for a new
skin](https://www.ilias.de/docu/goto_docu_pg_68693_367.html)

#### ILIAS 6

Major parts of the UI of ILIAS 6 have changed. It is therefore recommended, to create a new skin
for ILIAS think an manually move changes that are still needed from oder versions to the new skin.

Also, most importantly the following components have been introduced:

- [Standard Layout](https://test6.ilias.de/goto_test6_stys_21_LayoutPageStandardStandard_default_delos.html?), 
template directory: src/UI/templates/default/Layout, the frame of the DOM for the complete ILIAS page. 
Also checkout the according scss variable under section Layout (UI Layout Page).
- [Meta Bar](https://test6.ilias.de/goto_test6_stys_21_MainControlsMetaBarMetaBar_default_delos.html?) 
template directory: src/UI/templates/default/MainControls, the Bar on the top holding Notification, Search User Avatar, etc.
Also checkout the according metabar scss variables.
- [Main Bar](https://test6.ilias.de/goto_test6_stys_21_MainControlsMainBarMainBar_default_delos.html?) 
template directory: src/UI/templates/default/MainControls, the Bar on the left holding triggers for opening the slates for
accessing Repository, Dasbhoard etc. Content.
Also checkout the according mainbar scss variables.
- [Slate](https://test6.ilias.de/goto_test6_stys_21_MainControlsSlateFactorySlate_default_delos.html?) 
template directory: src/UI/templates/default/MainControls/Slate, the Slates triggered by opening items of the Main Bar.
Also checkout the according slate scss variables.
- [Breadcrumbs](https://test6.ilias.de/goto_test6_stys_21_BreadcrumbsBreadcrumbsBreadcrumbs_default_delos.html?)
template directory: src/UI/templates/default/Breadcrumbs, Breadcrumbs working as locator on the top of the page.
Also checkout the breadcrumb scss variables.

See above section on information on how to customize those components.

#### ILIAS 5.3

The `icon-font-path` for glyphs has changed due to a move from the bootstrap
library to the new location for external libraries. The new location is:
`"../../../../libs/bower/bower_components/bootstrap/fonts/"`. If a 5.2 style is
imported, the variable `icon-font-path` must be adapted accordingly.

#### ILIAS 7

The `icon-font-path` for glyphs has been renamend to `il-icon-font-path`
and the location has changed due to a move from the bootstrap
library to the new location for external libraries. The new location is:
`"../../../../node_modules/bootstrap/fonts/"`. If a 5.2 style is
imported, the variable `icon-font-path` must be adapted accordingly.

In March 2022, we moved the general Test & Assessment CSS (ta.css and
ta_split.css) to `less/Modules/Test/delos.less` (like other CSS for modules)
to start refactoring this module's style code. As part of this change,
the override mechanism that fetches a custom style for just the T&A has been
removed. Please use the standard skin setup described in this document to style
the Test & Assessment like the rest of your custom skin.

## Coding Guidelines

If you want to change and contribute ILIAS style code, please refer to the most recent [SCSS Coding Guidelines](./Guidelines_SCSS-Coding.md)