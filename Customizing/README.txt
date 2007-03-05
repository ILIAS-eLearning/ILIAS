This directory holds all customized files for this ilias installation.

On the top level two directories may be created: /Customizing/global for global 
changes and /Customizing/clients for changes that should be applied to clients.
The clients directory holds a subdirectory for each client:
/Customizing/clients/<client_id>


1. User Agreements

User agreement texts are stored within html files. They can be defined globally
or all clients or on a client level.

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
<template xmlns = "http://www.w3.org" name = "ISN">
	<style 	name = "PfP"
			id = "pfp"
			image_directory = "images"/>
</template>

The directory must include a .css file that corresponds to the style id, e.g.
for style id "pfp" a file "pfp.css" with the style sheet information must be
included.
