# Roadmap of Language Service

## Already Implemented

* Accelerated language update (no more need to introduce background tasks)
* Removed language installation in config (only English is installed by Setup)
* Substituted LegacyUI Button by KS Button

## Short Term

* Fixing PHP 8.2 issues
* Analysing use of language variables on test9
* GitHook for preventing duplicate use of same variable_ID in language files

## Mid Term

* Move LegacyUI Table2GUI to KS Data Table
* Remove unused language variables from language files
* Improving export and import of customised language files
* Improving online translation tool

## Long Term

* Introducing RFC 5646 language coding scheme for language and region to allow multiple versions per language, see [https://datatracker.ietf.org/doc/html/rfc5646](https://datatracker.ietf.org/doc/html/rfc5646)
* Separating language service from language files
