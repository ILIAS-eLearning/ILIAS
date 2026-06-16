# Skins and Styles for ILIAS

The total representation of ILIAS graphical user interface is defined by
its skin and style. The skin contains all the necessary files (CSS/SCSS, images,
icons, fonts, templates) to determine the visual appearance of ILIAS. A skin contains
one or more Styles, which may differ in colors, fonts, spacing and other visual
aspects. The default skin of ILIAS is called Delos and contains one Style also
called Delos.


## ILIAS System Style DELOS

The template folder of ILIAS contains the CSS/SCSS files and templates, which are
necessary to build the default system style of ILIAS called Delos. Due to the 
new build process of ILIAS, the fonts, images and icons are no longer part of
this folder. They are now initially located in the components/ILIAS/UI/resources
folder and copied to the public/assets folder during the build process.
All template files are still located in the templates folder of each component. 
All files together determine the visual appearance of ILIAS. They differ from 
content styles, which allow the classes determining the appearance of 
user-generated content to be adjusted.

## Custom Skins and Styles

System styles may be customized by creating custom skin and style. Custom skins 
must be placed in the directory `./public/Customizing/skin`. A custom skin has
to contain at least one style and may include further sub-styles. The sub-styles 
may be active for different branches of the repository.

### Tools

The ILIAS default system style called Delos is written in SCSS, which has to be
compiled using the SASS pre-processor to a CSS file that the browser can read.

For any larger scale styling project, we recommend that you consider using SASS as
well. This way, you can build on and modify all the work that has been done by the
community to style the many views and components used in ILIAS.

You may want to use the same version of SASS that is referenced in the NPM
package.json and automatically installed to `node_modules/` when using `npm install`.
This specific SASS version can be executed like this from the ILIAS root:
`node_modules/.bin/sass`

Alternatively, you can install the latest version of SASS globally with
`npm install sass -g`.

You can find a starting point for a custom System Style based on the default Delos
system style here: [Delos Repository](https://github.com/ILIAS-eLearning/delos)

At the point of writing, it does require modification to be recognized as a custom
System Style as outlined later in this document.

### Access available skins and Styles through Frontend

1. Navigate to "Administration -> Layout and Styles" of you ILIAS Installation.
2. In a table you see all available System Styles. 
3. You may assign users to styles via Actions Dropdown
4. You may set Sub Styles for certain sections of the repository via Actions Dropdown

# Creating a custom skin and style

## Quick-Build from the delos repository

### Step 1: Create skin directory

If you want to create a new skin quickly, you can use the delos repository as a
starting point. To do so, first go to the directory `./public/Customizing/skin`.

```
cd ./public/Customizing/skin
```

### Step 2: Clone delos repository

Then clone the delos repository from GitHub into a new directory, e.g. `MyDelosSkin`.
Please note that the correct branch must be selected to match the ILIAS version being
used.

```
git clone https://github.com/ILIAS-eLearning/delos.git MyDelosSkin
```

### Step 3: Switch to correct branch

To switch to the correct branch you have to change into the skin directory and switch
to the branch you need, e.g. release_10 for ILIAS 10.xx:

```
cd MyDelosSkin
git switch release_10
```

### Step 4: Finish

Now the skin is ready to be used and you can activate it in the ILIAS administration.

> **Note:**  
> The Delos repository contains all CSS, templates, and resource files in their latest 
> versions. Template changes can lead to malfunctions, so it is recommended to update 
> custom skins immediately after an ILIAS update.

> **Note:**  
> It's not recommended to modify the delos.css directly. Instead, modify the SCSS files
> and compile with sass pre-processor.



## Create a custom skin from scratch

### Step 1: Create skin directory

If you want to create a new skin from scratch, you have to go to the skin directory
in the public folder. In the skin folder you have to create a new subdirectory for 
your skin, e.g. MySkin.

```
cd ./public/Customizing/skin
mkdir MySkin
```

### Step 2: Create template.xml File

One file that must exist in every skin is the file template.xml. E.g.
`./public/Customizing/skin/MySkin/template.xml`. This file contains all necessary
information about the skin and its styles. An example file could look like this:

```
<?xml version = "1.0" encoding = "UTF-8"?>
<template xmlns = "http://www.w3.org" version = "1.0" name = "MySkin">
        <style name="MyStyle" id="mystyle" image_directory="images"/>
</template>
```

In this example a custom skin called MySkin is defined, which contains one style 
called MyStyle. The style has the ID mystyle and uses the subdirectory 'images' to
store images, icons and logos.

> **Note:**
> 
> Additional template parameters can be found under `Additional Information` at
> the end of this documentation.

This skin/style combination will be listed as MySkin/MyStyle in the
ILIAS Style and Layout administration. The style's files are now expected
to be located in `./public/Customizing/skin/myskin/mystyle/`:

The ILIAS administration is the place where you can activate/deactivate styles,
and where you can assign users from one skin to another.

#### Step 3.1: Create main CSS File

The `id` attribute of the style tag defines the name of the corresponding style
sheet (CSS) file, in our example `./public/Customizing/skin/myskin/mystyle/mystyle.css`.

The easiest (but not recommended) ways to get a working skin quickly are to
* copy and rename the default CSS file located at `templates/default/delos.css`
  to `./public/Customizing/skin/myskin/mystyle/mystyle.css`
* or create `./public/Customizing/skin/myskin/mystyle/` and import delos in the
  top line of your css like so: `@import url("../../../../assets/css/delos.css");`

If your CSS file contains references to (background) images, these images must
be present at their defined locations. If you copied the default CSS file, the
image paths will not be correct anymore. You can either copy them to your skin
directory, change the CSS definitions or provide your own image files.

#### Step 3.2: Better Alternative

To have a working directory for your skin, you can also copy the complete folder
templates/default of your ilias installation to a new folder below
`./public/Customizing/skin/myskin` within that directory, edit the file
`template.xml` to have an unique Style Name and id. This is needed to identify the
new skin in ILIAS' administration. Compile `delos.scss` or copy/rename the
standard `delos.css` file to `./public/Customizing/skin/myskin/mystyle/mystyle.css`.
Take care: the main CSS-File must reflect the style id in its name (see above).

However, best use the stand alone skin [delos](https://github.com/ILIAS-eLearning/delos)
git repo, which is always an up-to-date copy of the delos skin from the main repo.
Clone it into your `./public/Customizing/skin/myskin` folder, make your changes and
merge important fixes and updates to delos into your skin.

With this approach, you should not modify the css file, but work entirely in the scss files.

#### Step 3.3 Sass

Do not forget to re-compile the scss-file after each change. Switch to the root of your style
and execute:

```
./node_modules/.bin/sass delos.scss mystyle.css
```

or

```
./node_modules/.bin/sass  --style=compressed delos.scss mystyle.css
```

for a minified CSS version.

#### Step 4: Mail styling
ILIAS 12 features a new, modern email design.
This can also be customized using a custom system style.
Both the styling and the layout can be changed as follows:

##### Step 4.1: Sass
The file `templates/default/mail.scss` provides the CSS for the mail template.
This can be overwritten in the custom system style.
To do this, the following file must exist in the custom system style:
`public/Customizing/skin/<myskin>/mail.scss` or `public/Customizing/skin/<myskin>/<mystyle>/mail.scss`.

Please note: this must be compiled separately into `mail.css`.
By copying and compiling the file from `templates/default/mail.scss` to the custom system style, variables such as the text color and link color are automatically used for the mail styling.

##### Step 4.2: Template
The file `components/ILIAS/UI/src/templates/default/Layout/tpl.mailpage.html` forms the HTML framework for the email and can be overwritten in the same way as all other templates via the path `public/Customizing/skin/<myskin>/<mystyle>/UI/Layout/tpl.mailpage.html`.

##### Step 4.3: Logo
When a custom system style is enabled, a logo is searched for along the following paths and in the given sequence:
1. `public/Customizing/skin/<myskin>/images/logo/Logo.svg`
2. `public/Customizing/skin/<myskin>/images/logo/HeaderIcon.svg`
3. `public/Customizing/skin/<myskin>/<mystyle>/images/logo/Logo.svg`
4. `public/Customizing/skin/<myskin>/<mystyle>/images/logo/HeaderIcon.svg`
5. The first compatible file in `public/Customizing/skin/<myskin>/images/logo/`
6. The first compatible file in `public/Customizing/skin/<myskin>/<mystyle>/images/logo/`
7. The ILIAS standard logo

The first match will be used as the logo for the email.
In addition to .svg, the following formats are also supported for the logo:
- jpg
- jpeg
- gif
- png


#### Step 5: Add Icons (Optional)

If you want to replace the default icons coming with ILIAS, you can add new
representations of them to your skin. They must be stored in a subdirectory
named like the `image_directory` attribute of the style tag in the
`template.xml` file.

E.g. if you want to replace the default icon for categories
`public/assets/images/standard/icon_cat.sfg`, and your template file defines
`image_directory = "images"` as in the example above, the new version must be
stored as `./public/Customizing/skin/myskin/mystyle/images/icon_cat.svg`.

Note: Since v9 ILIAS supports suffix-specific file icons. See: https://docu.ilias.de/go/wiki/wpage_7496_1357
These files cannot be changed via the style however. If you would like to change them you can access and change/override
all of them via the ILIAS administration:

Administration / Repository and Objects / Files / File Objects: Suffix-Specific Icons

#### Step 6: Change Layout (Optional)

The layout is specified in HTML template files. Some standard default template
files can be found in directory `templates/default`. Other template files are
stored within subdirectories of the Modules or Services directories. Most ILIAS
screens use more than one template file. Some template files are reused in many
ILIAS screens (e.g. the template file that defines the layout of the main menu).

To replace a template file for your skin, you have to create a new one in your
skin directory. Please note, that your skin should only contain template files
that are modified. You do not need to copy all default template files to your
new skin.

Since ILIAS 5.3 we moved most of the UI towards the UI Components, which are 
located in UI/src. To overwrite those you need to add the respective tpl files
to your skins folder.

**Examples:**  
`components` related template files must be stored in a similar subdirectory 
structure (omit the `templates` subdirectory). E.g. to replace the template file
`components/ILIAS/XYZ/templates/tpl.xyz.html` create a new version at 
`./public/Customizing/skin/myskin/components/ILIAS/XYZ/tpl.xyz.html`. A template
of a UI Component located in `UI/src/templates/default/XYZ/tpl.xyz.html` can be 
customized by creating a `./public/Customizing/skin/myskin/UI/XYZ/tpl.xyz.html` file.

<br>
  
The following list contains some standard template files, that are often changed in
skins:

- **Standard Layout:**  
  The file: `components/ILIAS/UI/src/templates/default/Layout/tpl.standardpage.html` contains the frame of the DOM for the complete ILIAS page.  
  > See also the according scss-variables in the folder [070-layout/UI-framework/Layout](https://github.com/ILIAS-eLearning/ILIAS/tree/release_10/templates/default/070-components/UI-framework/Layout).  
- **Meta Bar:**  
  The file `components/ILIAS/UI/src/templates/default/MainControls/tpl.metabar.html` contains the Bar on the top holding Notification, Search User Avatar, etc.
  > See also the according metabar scss-variables in the folder [070-layout/UI-framework/MainControls](https://github.com/ILIAS-eLearning/ILIAS/tree/release_10/templates/default/070-components/UI-framework/MainControls).
- **Main Bar:**  
  The file `components/ILIAS/UI/src/templates/default/MainControls/tpl.mainbar.html` contains the Bar on the left holding triggers for opening the slates for accessing Repository, Dasbhoard etc. Content.
  > See also the according mainbar scss variables in the folder [070-layout/UI-framework/MainControls](https://github.com/ILIAS-eLearning/ILIAS/tree/release_10/templates/default/070-components/UI-framework/MainControls).
- **Slate:**  
  The file `components/ILIAS/UI/src/templates/default/MainControls/Slate/tpl.slate.html` contains the Slates triggered by opening items of the Main Bar.
  > See also the according slate scss variables in the folder [070-layout/UI-framework/MainControls/Slate](https://github.com/ILIAS-eLearning/ILIAS/tree/release_10/templates/default/070-components/UI-framework/MainControls/Slate).
- **Breadcrumbs:**
  The file `components/ILIAS/UI/src/templates/default/Breadcrumbs/tpl.breadcrumbs.html` contains Breadcrumbs working as locator on the top of the page.
  > See also the breadcrumb scss variables in the folder [070-layout/UI-framework/Breadcrumbs](https://github.com/ILIAS-eLearning/ILIAS/tree/release_10/templates/default/070-components/UI-framework/Breadcrumbs).

* Startup Screens (Login, Registration, ...): `components/ILIAS/Init/templates/default/tpl .startup_screen.html`

<br>

> **Note:**  
>  
> We are currently working on harmonizing the template directories.  
> As part of this process, we move the `/UI/src/templates/default` to `/UI/templates/default`.

<br>

#### Step 7: Change the ILIAS Icon

The main ILIAS icon is stored in the images Directory as `logo/HeaderIcon.svg`. You
can replace this easyly by your own Icon in svg format. As long as your Icon is
close to a square, this may be all that is needed. Probably you want to change
the file favicon `.ico` in ILIAS' root directory too. For non-square Icons you
may refer to:

[Installation and Maintenance » Change the ILIAS
icon](http://www.ilias.de/docu/goto_docu_pg_68691_367.html)


### Migration

There might be changes you need to consider if updating to a new ILIAS version.

Note that this changelog was introduced for ILIAS 5.3. If migrating to a lower
version you might find helpful information by consulting:

[Installation and Maintenance » Prepare for a new
skin](https://www.ilias.de/docu/goto_docu_pg_68693_367.html)


# Additional Information

The following describes the individual tags and parameters of the template.xml and how to use them.

## Template Tags

The `template.xml` file can contain the following tags:

> &lt;template> to define a custom skin  
> &lt;style> for the skin's main style  
> &lt;substyle> to define substyles derived from the main style

### &lt;template>-Tag

The template tag defines the skin. It must contain at least one style and can optionally include
further sub-styles. The tag must first include the namespace parameter `xmlns="http://www.w3.org"`.
It must also include the parameters `version` and `name`.

### &lt;style>-Tag

The style tag defines the main style of the skin. This must include at least the parameters `name`,
`id`, and `image_directory`. The parameter `font_directory` and `sound_directory` can specified 
if needed.

### &lt;substyle>-Tag

The tag `<substyle>` defines a substyle that depends on the main style. Like the `<style>` tag, it
must contain at least the parameters `name`, `id`, and `image_directory`. The parameter
`font_directory` and `sound_directory` can also be specified optionally.

## Template Parameters

### name

The name of the skin, style, or substyle that is displayed in the ILIAS frontend.

### version

The version of the skin

### id

The ID parameter specifies the directory and simultaneously the name of the .css file
that belongs to the style or substyle.

### image_directory

The directory for images, icons, and logos.

> **Note:**  
>   
> The directory can also include the '../' designation and is always used relative 
> to the directory specified with the id.

### font_directory

The directory for fonts.

> **Note:**
>
> The directory can also include the '../' designation and is always used relative
> to the directory specified with the id.

## Useful hints

### Changing the image or font directory

If you want to change the image or font directory to a different location, you can
do so by specifying the `image_directory` or `font_directory` parameter with a
relative path including `../` in the style or substyle tag. If you have modified
these directories, you must ensure that the paths in the CSS files are correct.

Therefore please check the _settings_typography.scss on line 8:
```
$il-web-font-path: "../fonts/" !default;
```

and the _settings_media.scss on line 2 and line 4:

```
$il-background-images-path: "../images/" !default;
```

```
$il-icon-font-path: "../fonts/bootstrap/" !default;
```

## Examples for template.xml

### Example 1: Simple skin with one Style

**Directory structure**  

```
./public/Customizing/skin
  └── MySkin1/
      ├── MyStyle/
      │   ├── 010-settings/
      │   ├── [...]/
      │   ├── MyStyle.scss
      │   ├── MyStyle.css
      │   └── [...]
      ├── components/
      ├── images/
      │   └── [...]
      ├── fonts/
      └── template.xml
```

**template.xml**

```
<?xml version = "1.0" encoding = "UTF-8"?>
<template xmlns = "http://www.w3.org" version = "1.0" name = "MySkin1">
    <style id="MyStyle" name="Main-Style" image_directory="../images">
</template>
```

### Example 2: Skin with one Style and one Substyle and separate image directories

**Directory structure**

```
./public/Customizing/skin
  └── MySkin2/
      ├── MyStyle/
      │   ├── 010-settings/
      │   ├── [...]/
      │   ├── MyStyle.scss
      │   ├── MyStyle.css
      │   ├── images/
      │   │   └── [...]
      │   └── [...]
      ├── MySubStyle/
      │   ├── 010-settings/
      │   ├── [...]/
      │   ├── MySubStyle.scss
      │   ├── MySubStyle.css
      │   ├── images/
      │   │   └── [...]
      │   └── [...]
      ├── components/
      ├── fonts/
      └── template.xml
```

**template.xml**

```
<?xml version = "1.0" encoding = "UTF-8"?>
<template xmlns = "http://www.w3.org" version = "1.0" name = "MySkin2">
    <style id="MyStyle" name="Main-Style" image_directory="images">
    <substyle id="MySubStyle" name="Sub-Style" image_directory="images">
</template>
```

### Example 3: Skin with one Style and two Substyles and shared image and font directory

**Directory structure**

```
./public/Customizing/skin
  └── MySkin2/
      ├── MyStyle/
      │   ├── 010-settings/
      │   ├── [...]/
      │   ├── MyStyle.scss
      │   ├── MyStyle.css
      │   └── [...]
      ├── MySubStyle_A/
      │   ├── 010-settings/
      │   ├── [...]/
      │   ├── MySubStyle.scss
      │   ├── MySubStyle.css
      │   └── [...]
      ├── MySubStyle_B/
      │   ├── 010-settings/
      │   ├── [...]/
      │   ├── MySubStyle.scss
      │   ├── MySubStyle.css
      │   └── [...]
      ├── components/
      ├── images/
      │   └── [...]
      ├── fonts/
      │   └── [...]
      └── template.xml
```

**template.xml**

```
<?xml version = "1.0" encoding = "UTF-8"?>
<template xmlns = "http://www.w3.org" version = "1.0" name = "MySkin2">
    <style id="MyStyle" name="Main-Style" image_directory="../images" font_directory="../fonts">
    <substyle id="MySubStyle_A" name="Sub-Style A" image_directory="../images" font_directory="../fonts">
    <substyle id="MySubStyle_B" name="Sub-Style B" image_directory="../images" font_directory="../fonts">
</template>
```


## History

### ILIAS 10
- **Important**:
  > The location of the skin has been moved to `./public/Customizing/skin`
- System style management through GUI has been abandoned, see: https://docu.ilias.de/go/wiki/wpage_1_1357
- Sass is now shipped with NPM in the ILIAS dev-dependencies.

### ILIAS 9
A proposal on how to better structure the system styles was made and accepted by the JF in 2021.

With ILIAS 9 the SCSS was restructered according to the ITCSS structure suggested by this proposal,
and the depencency to less from Bootstrap has mostly been removed. However, the change from less to SCSS
and the abandonment from Bootstrap means, that system styles from 8 and lower are NOT compatible with ILIAS 9.
They can not be imported, be used, or compiled.

However, note, that most of the css should still work. Also less and scss are not that far appart. Best read
through our [SCSS Coding Guidelines](./Guidelines_SCSS-Coding.md) to get started.

### ILIAS 7

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

### ILIAS 6

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

### ILIAS 5.3

The `icon-font-path` for glyphs has changed due to a move from the bootstrap
library to the new location for external libraries. The new location is:
`"../../../../libs/bower/bower_components/bootstrap/fonts/"`. If a 5.2 style is
imported, the variable `icon-font-path` must be adapted accordingly.

## Coding Guidelines

If you want to change and contribute ILIAS style code, please refer to the most recent [SCSS Coding Guidelines](./Guidelines_SCSS-Coding.md)
