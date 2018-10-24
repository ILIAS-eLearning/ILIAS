# Customizing

<!-- MarkdownTOC depth=0 autolink="true" bracket="round" autoanchor="true" style="ordered" indent="   " -->

1. [Introduction](#introduction)
1. [System Language Changes](#system-language-changes)
1. [Skins and Styles](#skins-and-styles)
1. [Plugins](#plugins)

<!-- /MarkdownTOC -->

<a name="introduction"></a>
## Introduction

This directory holds all customized files for this ILIAS installation.

On the top level two directories may be created: `/Customizing/global` for
global changes and `/Customizing/clients` for changes that should be applied to
clients.

The clients directory holds a subdirectory for each client:

```
/Customizing/clients/<client_id>
```

At the time being, only user agreements can be offered for clients! Customized
skins and languages are only supported globally.

<a name="system-language-changes"></a>
## System Language Changes

You may change terms used in the user interface of ILIAS. To do this, use the
same format as is used in the language files in directory `/lang`. Store the
values to be overwritten in files ending with `.lang.local` and put them into
the `/global/lang` directory. Client specific changes are not supported yet.

```
/global/lang/ilias_<lang_code>.lang.local
```

Example:

```
/global/lang/ilias_en.lang.local
```

<a name="skins-and-styles"></a>
## Skins and Styles

You find all information about how to create your own skin in the [Custom
Styles](/templates/Readme.md#custom-styles) documentation.

<a name="plugins"></a>
## Plugins

Plugins are installed under `/global/plugins`. Each plugin should come with its
own documentation stating the exact target directory.
