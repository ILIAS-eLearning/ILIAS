# Language Roadmap

Language is central to the usage of ILIAS. Everybody who writes concepts and feature requests, everybody who contributes test cases and – of course – everybody who implements the code gets in touch with it. Once the implementation is done and a new ILIAS release is out in the wild, language is of paramount importance for our end users. 

The variety of actors leads to different flavours of language in ILIAS. The creation of a general style book has not yet gained enough traction.

Still, we would like to improve language in ILIAS. This is why we want to… 
* outline what we’re up to and 
* provide pull requests based on our ideas presented in this document.

If you oppose (or support!) entries in this document, please comment and/or get on board.

## Unifying language variables

Language variables are one of the many cross-cutting issues in ILIAS. Over time, the language increasingly diffuses apart. Continuous maintenance of the language variables should ensure and *improve the uniformity of the system*.

Inconsistent identifiers will be collected and renamed. In order to reduce the number of variables in the long term, the redundant variables should then be removed and replaced by common variables. 

The unification of language variables is discussed and implemented in several groups:
* Gendermainstreaming and Settings-Tab: https://docu.ilias.de/goto_docu_fold_10678.html
* ILIAS Language Front: https://docu.ilias.de/goto_docu_grp_8782.html 

## Gender Mainstreaming

German language has the peculiarity that it is gendered. In many contexts in ILIAS, generalized masculine identifiers are used in the language variables. This can lead to users feeling excluded. In order to welcome users of all genders and to ensure gender mainstreaming, neutral identifiers should be used wherever possible. To achieve this, we collected all gendered identifiers in ILIAS and improve them by groups of terms. Looking at each identifier within its ILIAS context ensures that we make informed decisions, both linguistically and in terms of usability.

## Settings Tabs

The Settings tab in ILIAS objects is of crucial importance for users who want to explore our features. Unfortunately, it is often quite complex. Working there should be made as simple as possible, by intelligently grouping the settings, naming them in a consistent way, and adding helpful bylines where required. In close cooperation with the ILIAS maintainers, we strive to subsequently improve the Settings tabs of all objects in ILIAS.

## Reducing language variables

Language variables are used inflationary in ILIAS. Many variables are redudant and should be replaced by a common variable.
The redundant variables will be identified and a proposed solution for the particular case will be offered. Subsequently, the issues are processed in Mantis and collectively assigned to the maintainers.

## Classification for language modules

The language module identifiers in the language file are not coherent. (see Florian’s list - www.) Some modules are abbreviated others are not. For this reason, it is not always clear which language module contains the variables of a component. The naming scheme of the language modules is to be revised. In the long term, abbreviations should no longer be used.

Based on the names, the language modules should be clearly connected to their respective services and objects.
