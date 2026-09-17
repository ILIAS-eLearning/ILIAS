Language Service
================

*document in progress*

# General Information

## Guidelines
There are a couple of guidelines defined in the [language.md](../../../docs/development/language.md) that have to be respected when adding language variables to the lang files of ILIAS or editing them.

## Using the Language Service
Component-revision code should depend on `ILIAS\Language\Language`. During the legacy bootstrap this interface is
provided lazily, so early component construction does not access the language service before the legacy container has
been initialised. After initialisation it delegates to the active `ilLanguage` runtime instance.

For components which have not yet been migrated, the active runtime language remains available through `$DIC['lng']`
or `$DIC->language()`. These access paths are a temporary compatibility layer and must not be used for new code.

Setup code has a separate language implementation, `ilSetupLanguage`, which is constructed without runtime user,
session, or container state. It must not be used as the runtime language service.

        $language->loadLanguageModule("frm");
        $tpl->setVariable("TEXT", $language->txt("frm_new_posting"));

## Installing and Managing Languages
Which languages are installed/available (`ILIAS\Language\Setup\InstalledLanguageRepository`) and installing, flushing
or registering a language (`ILIAS\Language\Setup\LanguageInstallationManager`) are separate, narrower services -
following [the repository pattern](../../../docs/development/repository-pattern.md) - rather than part of
`ilSetupLanguage` itself. New code that only needs to install or inspect languages should depend on these two
instead of on `ilSetupLanguage`, which still exists (delegating to both) as the concrete `ILIAS\Language\Language`
implementation used during Setup and as a stable entry point for callers not yet wired through `Language.php`.


## Language File Directories
Where language files are looked up - the global `lang` directories, a component's own language
files, and the Customizing overrides - is resolved through `ILIAS\Language\ComponentTranslation\*`
(`src/ComponentTranslation/`): `LanguageFileDirectory` is the interface each source implements
(`MainLanguageFileDirectory`, `ComponentLanguageFileDirectory`, `CustomizingLanguageFileDirectory`),
and `LanguageFileDirectoryManager` aggregates all of them, contributed by other components via
`$contribute[LanguageFileDirectory::class]` and gathered in `Language.php` via
`$seek[LanguageFileDirectory::class]`. `InstalledLanguageDatabaseRepository` and
`LanguageInstallationManager` both depend on this manager rather than hardcoding paths.

## User Settings Contribution
This component contributes a personal "language" setting to the user settings framework
(`ILIAS\Language\UserSettings\Settings`, wired in `Language.php` via
`$contribute[User\Settings\UserSettings::class]`).

## Activities
This component provides seven [Activities](../Component/src/Activities/README.md)
(`ILIAS\Language\Activities\*`), each wired into `Language.php` (`$internal`/`$provide`/
`$contribute`) and used by this component's own GUI classes via `maybePerformAs()`. All seven
are `Command`s (they change installation/configuration state; none of them queries data without
side effects). See each class's own `getDescription()` for its authoritative, up-to-date
description rather than a copy here, which would drift out of sync:

* **InstallLanguage** (`src/Activities/InstallLanguage.php`) - installs/re-applies one or more
  languages, depending on the chosen mode.
* **UpdateLanguage** (`src/Activities/UpdateLanguage.php`) - refreshes one or more already
  installed languages from the current language files.
* **UninstallLanguage** (`src/Activities/UninstallLanguage.php`) - uninstalls one or more already
  installed languages.
* **RemoveLocalLanguageChanges** (`src/Activities/RemoveLocalLanguageChanges.php`) - removes all
  local changes of one or more already installed languages and reinstalls them from the
  global/component language files.
* **AddLanguageEntry** (`src/Activities/AddLanguageEntry.php`) - adds one new "adjust language
  variables" entry to every currently installed language for which a value was given.
* **SetLanguageDetectionEnabled** (`src/Activities/SetLanguageDetectionEnabled.php`) - enables or
  disables the system-wide automatic language detection from the browser's Accept-Language
  header.
* **SetLanguageTranslationEnabled** (`src/Activities/SetLanguageTranslationEnabled.php`) - enables
  or disables the "page translation" feature for one specific language.

### Known, accepted deviations from the Activity contract

* **AddLanguageEntry::perform()** additionally accepts an optional `usr_id` (int) key in its
  `$parameters`, even though `getInputDescription()`'s `FormInput` never produces one - it is
  supplied out-of-band by `maybePerformAs()` (never spoofable via form data) and recorded as the
  author of the local change made to every written entry. A caller that omits it (e.g. a generic
  caller following the plain `getInputDescription()` -> `withInput()` -> `getContent()` ->
  `perform()` contract) is not rejected; the entries are simply written without an attributed
  author.
* **AddLanguageEntry::getInputDescription()** builds per-language field labels via
  `$this->lng->txt('meta_l_' . $lang_key)`, which requires the caller to have already called
  `loadLanguageModule('meta')` beforehand. This component's own GUI does so already (see
  `classes/class.ilObjLanguageExtGUI.php`'s constructor). A caller that skips this gets the raw,
  untranslated placeholder (e.g. `-meta_l_de-`) as the label instead of a crash. `getInputDescription()`
  deliberately does not load the module itself, since `loadLanguageModule()` merges the module's
  keys, unnamespaced, into the shared `$this->lng` state - doing so implicitly here could clobber
  keys for an unrelated module if a future generic caller iterates over several Activities sharing
  one `Language`/`ilLanguage` instance.
* **AddLanguageEntry::getInputDescription()** resolves the current set of installed languages from
  `InstalledLanguageRepository::getInstalledLanguages()` on every call, rather than being a pure,
  static description of the input shape. Within one `maybePerformAs()` call, `getInputDescription()`
  and `perform()` can therefore observe different snapshots if a language is installed/uninstalled
  concurrently between the two calls: a newly installed optional language simply ends up in
  `skipped_empty_language_keys`; a newly installed `de`/`en` makes `perform()` reject the whole
  request (fail-closed, since no value could have been submitted for it); a language uninstalled
  in the meantime has its submitted value silently dropped rather than written or reported. No
  case crashes or writes partial data.

## Supported HTML Tags in Language Files
Only a defined set of HTML tags are allowed to be used within the `text_content` of a language entry.
The allow-list is passed explicitly to `ilUtil::stripSlashes()` at the only place this component
applies it - the language file import in `ilObjLanguageExtGUI::importLangfile()`
(`classes/class.ilObjLanguageExtGUI.php`) - and currently is:

* `a`, `b`, `bdo`, `br`, `code`, `div`, `em`, `gap`, `i`, `img`, `li`, `ol`, `p`, `pre`, `span`,
  `strike`, `strong`, `sup` and `ul`

This is almost, but not exactly, the tag set returned by `ilUtil::getSecureTags()` plus `span` and
`br`: `sub` is part of `getSecureTags()` but is missing from the allow-list actually passed here.
Whether that omission is intentional is unclear; treat `sub` as unsupported in language files until
this is resolved one way or the other.

All other HTML tags are unsupported and will be removed by `ilUtil::stripSlashes`.

## Further Reading
* [use-language-object.md](use-language-object.md) and [use-language-logging.md](use-language-logging.md)
  document the older, still widely used `$lng`/`ilLanguage` access pattern. They predate the
  `ILIAS\Language\Language` interface and the Activities described above and have not been updated
  to reference them; prefer the "Using the Language Service" section above for new,
  component-revision code.
* [`tests/LanguageComponentGraphTest.php`](tests/LanguageComponentGraphTest.php) is a contract test
  for this component's `$define`/`$implement`/`$use`/`$contribute`/`$seek`/`$provide`/`$pull`/`$internal`
  wiring in `Language.php` and is a good place to check the current, authoritative wiring behaviour
  (e.g. singleton guarantees, contribute ordering) rather than relying on prose descriptions here.
