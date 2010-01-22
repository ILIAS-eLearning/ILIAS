This directory holds all customized files for this ilias installation.

On the top level two directories may be created: /Customizing/global for global 
changes and /Customizing/clients for changes that should be applied to clients.
The clients directory holds a subdirectory for each client:
/Customizing/clients/<client_id>
At the time being, only user agreements can be offered for clients! Customized skins and languages are only supported globally.


1. User Agreements

User agreement texts are stored within html files. They can be defined globally
for all clients or on a client level.

Global user agreements:
/global/agreement/agreement_<lang_code>.html
E.g. /global/agreement/agreement_fr.html

Client specific user agreements:
/clients/<client_id>/agreement/agreement_<lang_code>.html
E.g. /clients/default/agreement/agreement_fr.html


2. System Language Changes

You may change terms used in the user interface of ILIAS. To do this, use the
same format as is used in the language files in directory /lang. Store the
values to be overwritten in files ending with ".lang.local" and put them into
the /global/lang directory. Client specific changes are not supported yet.

/global/lang/ilias_<lang_code>.lang.local
E.g. /global/lang/ilias_en.lang.local


3. Skins and Styles

Skins are applied through changes of HTML template files that are stored in the 
/skin directory. Currently they can be defined only globally.

/global/skin/<skin_name>/tpl.xxx.html

The skin directory must include a template.xml description file, example:

<?xml version = "1.0" encoding = "UTF-8"?>
<template xmlns = "http://www.w3.org" name = "MySkin">
	<style 	name = "MyStyle"
			id = "mystyle"
			image_directory = "images"/>
</template>

The directory must include a .css file that corresponds to the style id, e.g.
for style id "mystyle" a file "mystyle.css" with the style sheet information must be
included.

If you want to customize a HTML template file, you have to put it into the
following directory:

a) The original is located in templates/default/:
/global/skin/<skin_name>/Services/<ServiceName>/tpl.xxx.html

b) The original is locatod in Modules/<ModuleName>/templates/default/:
/global/skin/<skin_name>/Modules/<ModuleName>/tpl.xxx.html

c) The original is locatod in Services/<ServiceName>/templates/default/:
/global/skin/<skin_name>/Services/<ServiceName>/tpl.xxx.html

All images that are referred by the .css file must be present at their 
defined location. If you copy the default css from 
templates/default/delos.css. you have either to change the url in the css 
for each image or you copy all necessary images to the directory 
Customizing/global/skin/<skin_name>/images.

You find additional information about how to create your own skin in the 
Installatin and Maintenance documentation at
http://www.ilias.de/docu/goto.php?target=pg_15917_367&client_id=docu