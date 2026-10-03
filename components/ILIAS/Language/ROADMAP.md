# Roadmap of Language Service

## Already Implemented

* Accelerated language update (no more need to introduce background tasks)
* Removed language installation in config (only English is installed by Setup)
* Substituted LegacyUI Button by KS Button in the folder GUI's toolbar (`ilObjLanguageFolderGUI`);
  the form action buttons in `ilObjLanguageExtGUI` (`addCommandButton()`) are still legacy `ilPropertyFormGUI` buttons
* Migrated the component to the Component Revision's dependency graph
  (`$define`/`$implement`/`$use`/`$contribute`/`$seek`/`$provide`/`$pull`/`$internal` in `Language.php`)
  and introduced the Activities pattern: seven `ILIAS\Language\Activities\*` classes now hold this
  component's install/update/uninstall/detection/translation-toggle/entry-editing logic, used by the
  GUI classes via `maybePerformAs()` - see the README's "Activities" section
* Extracted language-installation state access into `InstalledLanguageRepository` and
  `LanguageInstallationManager` (repository pattern), and language-file-directory resolution into
  `ILIAS\Language\ComponentTranslation\*` - see the README

## Short Term

* Fixing PHP 8.2 issues
* Analysing use of language variables on test9
* GitHook for preventing duplicate use of same variable_ID in language files
* Migrate the remaining `addCommandButton()` form buttons in `ilObjLanguageExtGUI` to KS Button

## Mid Term

* Move LegacyUI Table2GUI to KS Data Table - `ilLanguageFolderTable` and `ilLanguageStatisticsTable`
  already implement the KS `DataRetrieval` table; only `ilLanguageExtTableGUI` (still `extends ilTable2GUI`,
  used by `ilObjLanguageExtGUI::getViewTable()`) remains to be migrated
* Remove unused language variables from language files
* Improving export and import of customised language files
* Improving online translation tool

## Long Term

* Introducing RFC 5646 language coding scheme for language and region to allow multiple versions per language, see [https://datatracker.ietf.org/doc/html/rfc5646](https://datatracker.ietf.org/doc/html/rfc5646)
* Separating language service from language files
