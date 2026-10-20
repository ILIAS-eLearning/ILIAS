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

### Language Identifier Enums
`txt()` (`ILIAS\Language\Language`) accepts, besides a string, a `LanguageIdentifier`:

        $language->txt(PollLanguageIdentifier::POLL_ANSWERS);         // text of the current language
        $language->txt(PollLanguageIdentifier::POLL_ANSWERS, $count); // plural form for $count
        $language->txt('poll_answers');                               // unchanged

- `ILIAS\Language\LanguageIdentifier` is implemented by a string backed enum per module (`<Component>LanguageIdentifier`):
  the case value is the identifier, `module()` the module. The enum is generated from the module's `.pot` with
  [`tools/language-identifier-enum/`](tools/language-identifier-enum/README.md) (`--check` in CI), so a removed
  identifier fails at the call site instead of showing `-identifier-`.
- With a `LanguageIdentifier` the identifier is read from exactly its module - no `loadLanguageModule()` beforehand, no
  dependency on which module was loaded last (no collision of identifiers that exist in several modules). A migrated
  module is looked up directly in its build and overlay; of a module that is not migrated the values for the current
  language are read (cache of `lng_modules`, else `lng_modules`) and kept for the request, for the default language its
  entry is read from `lng_data`. No side effect on string calls: the module is not loaded, so the order "the module
  loaded last wins" stays as it is. Without a text in the current language the one of the default language is served,
  otherwise `-identifier-`.
- With a string and without `$n`, `txt()` behaves as before, without a fallback module (see below).
- An empty identifier (as for a string, also `"0"`) gives `""`.
- `$n` only selects a plural form of a plural message of a migrated module, see [Plural Forms](#plural-forms).
- `txtlng()` and `exists()` take a string only.
- Breaking change (announced for the Jour Fixe): implementations of `ILIAS\Language\Language` and subclasses of
  `ilLanguage` that override `txt()` must accept `string|LanguageIdentifier $a_topic, ?int $n = null`. `ntxt()` is
  gone, use `txt($a_topic, $n)`. The second parameter `$a_default_lang_fallback_mod` is gone: load the module with
  `loadLanguageModule()` or pass a `LanguageIdentifier`. A string no longer falls back to the default language
  (#13467); only a `LanguageIdentifier` does. Behaviour change: where callers passed a fallback module, a key missing
  in the user's language now shows `-key-` instead of the text of the default language (`ilPluginLanguage` and the fuzzy marker of the
  language editor keep the fallback).
  `ilSetupLanguage` reads only the identifier of a `LanguageIdentifier` (`$n` without effect) - Setup needs English at most.

## Installing and Managing Languages
Which languages are installed/available (`ILIAS\Language\Setup\InstalledLanguageRepository`) and installing, flushing
or registering a language (`ILIAS\Language\Setup\LanguageInstallationManager`) are separate, narrower services -
following [the repository pattern](../../../docs/development/repository-pattern.md) - rather than part of
`ilSetupLanguage` itself. Both are internal to this component; `ilSetupLanguage` delegates to them and remains the
concrete `ILIAS\Language\Language` implementation used during Setup and the entry point for callers outside the
component.


## Language File Directories
Where language files are looked up - the global `lang` directories, a component's own language
files, and the Customizing overrides - is resolved through `ILIAS\Language\ComponentTranslation\*`
(`src/ComponentTranslation/`): `LanguageFileDirectory` is the interface each source implements
(`MainLanguageFileDirectory`, `ComponentLanguageFileDirectory`, `CustomizingLanguageFileDirectory`),
and `LanguageFileDirectoryManager` aggregates all of them, contributed by other components via
`$contribute[LanguageFileDirectory::class]` and gathered in `Language.php` via
`$seek[LanguageFileDirectory::class]`. `InstalledLanguageDatabaseRepository` and
`LanguageInstallationManager` both depend on this manager rather than hardcoding paths.

## Translations Maintained in PO Files
A module can be shipped by its component as gettext files instead of lines in
`lang/ilias_<lang>.lang`. Currently this applies to the modules `tos`
(`TermsOfService`) and `poll` (`Poll`). For such a module the shipped `.po` is
the only source of its shipped values: its lines are removed from
`lang/ilias_<lang>.lang` when it is migrated (`--remove-from-lang`, see
[Migrating a Module](#migrating-a-module)); a rollback restores them from git
(see [Rollback](#rollback)). Lines left there would be ignored when installing
or updating a language.

1. [Shipped Files](#shipped-files)
2. [Build](#build)
3. [Runtime](#runtime)
4. [Plural Forms](#plural-forms)
5. [Local Changes and Database](#local-changes-and-database)
6. [Markup Policy](#markup-policy)
7. [Language Administration](#language-administration)
8. [Setup](#setup)
9. [Plugins](#plugins)
10. [Migrating a Module](#migrating-a-module)
11. [Rollback](#rollback)

### Shipped Files
* The component contributes a `ComponentLanguageFileDirectory` with the module
  as prefix (`$contribute[LanguageFileDirectory::class]` in `<Component>.php`)
  and ships `<module>.pot` and one `<module>_<lang>.po` per language in its
  `lang/` directory. A 4th constructor argument may name the files differently
  (e.g. `'ilias_%s'` for `ilias_<lang>.po`; exactly one `%s`, no `/` or `..`).
  Build artifacts and overlays are always named after the module.
* Several modules of one component share its `lang/` directory and are told
  apart by their file names only: `LanguageFileDirectoryManager` refuses two
  directories that ship the same files. An `ilias_<lang>.lang` must not be put
  there: it is read once per contributed directory, so its lines would end up
  in every module of that folder.
* `msgid` is the language key (`txt()` is unchanged). There is no `msgctxt`: an
  entry without context belongs to the module of its directory, an entry with
  the module as context is read as well, every other context (also
  `msgctxt ""`) is ignored. Only UTF-8 files are accepted.
* A `###` comment of a `.lang` file is a note: the reference language's note
  is an extracted comment (`#.`, also in the `.pot`), a language's own note a
  translator comment (`# `, kept by `msgmerge`), which wins. `#, fuzzy` marks
  an entry that is not translated yet (it holds the English text); fuzzy
  entries are served like all others, empty translations are not shipped
  (`-identifier-`, as before).
* The initial files are generated with
  [`tools/po-migration/convert_module_to_po.php`](tools/po-migration/README.md),
  later changes are made in the `.po`/`.pot` files directly. The module has no
  `.lang` lines any more, so a new run of the tool fails ("Nothing was
  written") and leaves the files unchanged.
* A module counts as migrated for a language while its shipped `.po` exists
  (Setup, administration) and the build contains it (runtime).

### Build
`php cli/setup.php build` (also run by `composer install` and
`composer dump-autoload`, see `ShippedTranslationsBuild`) compiles every
shipped `.po` into `artifacts/language/<build>/`:
`messages/LC_MESSAGES/<module>.<lang>.mo`, `keys/<module>.php`, a copy of the C
library's `C.utf8` locale and a manifest (also the hashes of the compiled
`.po` files, warnings and collisions). `artifacts/language/current.json` names
the build that is served and its languages per module, nothing else, as every
request reads it; it is switched only after the new build is complete and
passed a real lookup through native gettext, and the previous build is kept
for running requests.

* **The build aborts** if the PHP extension `gettext` (a precondition of
  `setup build`) or `C.utf8` is missing.
* Not aborting, but reported on every build: values with markup that is not
  allowed (compiled cleaned, see [Markup Policy](#markup-policy)), a `.po` that
  cannot be compiled (e.g. merge conflict markers) and identifiers that two
  migrated modules ship with different values. For a `.po` that cannot be
  compiled the language keeps the catalog of the served (or previous) build,
  if that build was made by the same compiler and its catalog is intact and
  passes the markup policy; otherwise the module is not served for that
  language (`-identifier-`, like a `.lang` line that cannot be read) until the
  file is fixed; the runtime logs it when the module is used.
* **Deployment requirement:** run `php cli/setup.php build` after every change
  of a shipped `.po`. The runtime never compiles; a file that is not in the
  build is not served.
* Only Setup builds. The web server never writes into `artifacts/`.

### Runtime
`ilLanguage` serves a migrated module only through native gettext
(`MigratedTranslations`, `NativeGettext`): the build with the overlay of the
local changes on top, never from the database, and only for installed
languages (a language that is not installed falls back to `lng_modules` like a
module that is not migrated).

* Requirements: the PHP extension `gettext` for CLI and web server, and the
  same C library for both (glibc with `C.utf8`; musl ignores `LOCPATH`). The
  build checks this in the CLI process only. If native gettext is not available
  at runtime, the texts of migrated modules are shown as `-identifier-`, the
  problem is logged and shown in the language administration. With a
  thread-safe PHP (ZTS) the locale applies to every thread of the process.
* Behaviour change: an identifier that the migrated module loaded last does not
  translate gives `-identifier-`, not the value of a module loaded before.
* `txt()` with a `LanguageIdentifier` reads from exactly its module, see
  [above](#language-identifier-enums).
* Apart from these lookups, `.po`/`.mo` files are read and written with the
  library `gettext/gettext`, only through the adapter in
  `src/ComponentTranslation/Catalog/` (`TranslationCatalog`,
  `TranslationEntry`). No other class imports from `Gettext\`, and the
  library's `Scanner` is not used.

### Plural Forms
`txt($a_topic, $n)` returns the form of a plural message (`msgid_plural`,
`msgstr[n]`) that the `Plural-Forms` rule of the language selects for `$n`.
The quantity is not inserted; callers do that, e.g. with `sprintf()`. In every
other case (module not migrated, plugin, no plural message, empty form) it
returns exactly what `txt()` without `$n` returns in that language, never the
form of another language.

* `txt()` without `$n` returns the default form of a plural message: the last form
  (CLDR "other"), or the last non-empty one before it. The database holds this
  form only; all forms exist only in the `.po` files, the build and the
  overlay.
* The rules are taken from CLDR (`tools/po-migration/plurals.json`). They are
  checked when compiling (`PluralForms`, no `eval`); a missing or invalid
  header falls back to `nplurals=2; plural=(n != 1);` with a warning.
* The administration shows each form as a row of its own, `<key> [<n>]`
  (e.g. `poll_population [0]`). Keys containing ` [` cannot be migrated.
* If the rule of a language changes (e.g. CLDR 49: `vi` 1 → 2), local form
  values are taken over by meaning, not by index: a new form gets the local
  value of the old form that served the same quantities (n = 0…200 and some
  large values). If it covers several old forms, it gets their value if they
  agree, and the new default form gets the old default form; otherwise it
  shows the shipped value (logged when the overlay is written). The overlay
  keeps its old rule until the next write (e.g. `setup update`): until then
  the runtime serves the local plural messages with the old rule (consistent
  in itself, by meaning), `setup build` alone does not change that; the
  administration and every write already map it to the new rule. Form keys in imported files (`<key> [<n>]`) always mean the
  forms of the current rule.

### Local Changes and Database
Shipped files are never written at runtime, except by "merge" (see
[Language Administration](#language-administration)). Each installation keeps
only its local changes in the "overlay" below the client data directory,
`<client data dir>/lang/<module>/<lang>/`: the `.po` with the bookkeeping
(`# original`, `# local_change`, `# remark`), `lock`, `current` and the
compiled revisions `r-<hash>/` that `current` names.

* The overlay holds the delta only: entries whose value differs from the
  shipped one, keys that are not shipped (customizing file, "add new
  variable") and remarks. Setting an entry back to the shipped value, or to an
  empty value, removes it; an empty overlay is deleted. Installing a language
  without local changes writes no file.
* `lng_data`/`lng_modules` are still written on every path (with the cleaned
  shipped values and the default form of plural messages) as fallback for a
  rollback. They must not be dropped as long as code outside of this component
  reads `lng_data` directly (`ilNotificationDatabaseHandler`,
  `ilDclStandardField`).
* Writes of a module's overlay and database rows are serialized per module and
  language by an exclusive lock on `lock` in the overlay directory. Setup and
  "remove local changes" lock every module with an overlay for the whole run.
  Known limitation: `lng_data` is written in one batch, so a concurrent edit of
  a module without overlay can lose its database row (the overlay, which is
  served, keeps the edit).
* Symbolic links below `<client data dir>/lang` are not followed. The checks
  work on path names, so a link swapped in between check and file operation is
  still followed (requires write access as web server user).
* **Setup MUST be run as the web server user** (the owner of the client data
  directory), since Setup and the web server both write the overlay. Setup does
  not change ownership: it warns, naming the directories and the `chown`
  command. The administration shows a warning whenever an overlay could not be
  written or removed.
* Uninstalling a language removes its overlays.

### Markup Policy
Which HTML a language value may contain is decided in one place,
`ILIAS\Language\ComponentTranslation\TranslationMarkupPolicy`, for every
module, migrated or not. It uses PHP's HTML5 parser (no regular expressions);
an allowed value stays byte-identical.

* Tags: `a`, `b`, `bdo`, `br`, `code`, `div`, `em`, `gap`, `h3`, `i`, `img`,
  `li`, `ol`, `p`, `pre`, `s`, `small`, `span`, `strike`, `strong`, `sub`,
  `sup`, `u`, `ul`.
* Attributes (names case-insensitive): `a[href|target|rel|title]`,
  `img[src|alt]`, `span[class|style]`, `p[align]`, `div[align]`; no others.
  `href`/`src` must be relative or use `http`/`https` (`href` also `mailto`),
  checked on the decoded value. An attribute value must not contain `<`.
* `style` (on `span` only): the properties `background-color`, `color`,
  `font-style`, `font-weight`, `text-align` and `text-decoration`; no functions
  except `rgb`, `rgba`, `hsl`, `hsla`, no CSS escapes or comments.
* Not allowed either: comments, a value ending inside an unfinished tag, and
  unbalanced markup - an element left open (unless HTML5 allows to omit its end
  tag, e.g. `li`, `p`), an end tag closing an element around the value, and
  the end tag of any tag that is not allowed (`</form>`). Known gap: a stray
  `</p>` or `</a>` is not detected.
* A value containing `<` that is longer than 16 KiB is not parsed and never
  allowed.
* Text that is no tag to an HTML parser (`a <= b`, `<<`) is allowed.
* Values without `<` are not checked at all: output in attributes or
  JavaScript must be escaped by the caller.

Where the rules apply:
* Build (shipped `.po`) and plugin `.po`: offending tags, attributes and
  comments are removed (their text stays) and reported; nothing aborts.
* Local write paths reject such values instead, checking only values that
  differ from the current and the shipped one: the edit table and the import
  of an uploaded file (nothing is saved, every affected key is named with the
  offending markup), "add new variable" (the whole request is rejected), the
  customizing file `lang/customizing/ilias_<lang>.lang.local` (the affected
  entries are left out and named; the language is installed regardless).
* `setup update` reports local changes that break the rules, without changing
  them.
* Plugin `.lang` files and the shipped `lang/ilias_<lang>.lang` files are not
  changed; violations of plugin `.lang` files are logged.

### Language Administration
* The edit table compares with the shipped `.po`. A rejected save shows the
  table again with the input. The remark field of a module maintained in PO
  files shows the note of the shipped entry as long as there is no local
  remark; saving it unchanged writes nothing. Fuzzy entries are marked
  "Not translated" (`lng_not_translated`) in the comment of the default value.
* Remarks of migrated modules are stored in the overlay (and `lng_data`), one
  per key, also for plural messages.
* Import of a language file: for a migrated module, imported values that
  differ from the shipped ones become local changes in the overlay, in every
  mode. A line without `###` comment keeps the stored remark. "delete" first
  deletes the language's database rows and then writes every module anew; a
  remark that is not in the file is removed from the overlay as well. Markup is
  checked before anything is deleted.
* "Remove local changes" and the maintenance action "clear" are the same
  activity (`RemoveLocalLanguageChanges`): values, locally added keys and
  remarks are removed from database and overlay, the shipped state is
  re-installed and the plugin languages are re-imported.
* "save_dist" also copies the shipped `.po` of each migrated module into
  `<client data dir>/lang_data/`; the filter "conflicts" compares with these
  copies.
* "merge" (translation mode only, checked on the server) writes the global
  `.lang` file for modules that are not migrated, and the local changes of
  migrated modules into their shipped `.po` (new keys also into the `.pot`;
  remarks become an additional `# ` line). The previous files are copied to
  `lang/customizing/` (ignored by git), the written files belong to the web
  server user. Values with markup that is not allowed are not taken over. The
  overlay keeps serving the merged values until `php cli/setup.php build` and
  then `php cli/setup.php update` ran. Afterwards remove the lines of the
  merged keys from `lang/customizing/ilias_<lang>.lang.local`; otherwise the
  next update takes them as local changes again (and a remark appears twice).

### Setup
`setup update` (and "update" in the administration) reconciles each entry of a
migrated module three-way: L = the local value (`lng_data`, overlay or
customizing file), O = the shipped value recorded in the overlay (`original`),
S = the new shipped value.

| Case | Result |
|---|---|
| no L | S |
| L == S | S, no longer a local change |
| L == O (only the shipped value changed) | S |
| otherwise | L stays, `original` becomes S |

Values from the customizing file always stay local. A key removed from the
shipped `.po` is removed unless it carries a real local change.

* A language with an invalid language file (e.g. a shipped `.po` that cannot be
  parsed) is skipped and reported; the run goes on.
* **`setup update` aborts with `LanguageDataNotSavedException`** if a module
  array written to `lng_modules` cannot be read back (almost always a wrong
  collation of `lng_data`/`lng_modules`).
* Warnings are printed for skipped customizing entries, local changes with
  markup that is not allowed, overlay directories that are not writable, a
  Setup not running as owner of the client data directory, and a missing
  client data directory (the overlay is then written by the next update; until
  then migrated modules show their shipped values without local changes).

### Plugins
Plugins cannot contribute a `LanguageFileDirectory` yet. A plugin may ship
`lang/ilias_<lang>.po` instead of `lang/ilias_<lang>.lang` (the `.po` wins if
both exist): `msgid` is the key without the plugin prefix, entries with a
`msgctxt` are ignored and logged. The values go into the database as before
(no build, no overlay), comments are not stored, plural messages are
stored with their default form (`$n` has no effect for plugins). Values
of a plugin `.po` with markup that is not allowed are cleaned and logged;
values of a plugin `.lang` are only logged and taken over unchanged.

### Migrating a Module
1. Contribute the module's `ComponentLanguageFileDirectory` in the
   component's `<Component>.php` (`$contribute[LanguageFileDirectory::class]`);
   `--remove-from-lang` refuses to run without it.
2. Generate the `.pot`/`.po` files into the component's `lang/` directory and
   remove the module's lines from `lang/ilias_<lang>.lang` with
   [`convert_module_to_po.php --remove-from-lang`](tools/po-migration/README.md),
   then compare the placeholders with the reference language.
3. Run `composer dump-autoload` (or `php cli/setup.php build`) and
   `php cli/setup.php update`.

### Rollback
The database tables are written on every path, so a rollback removes code or
files, it never writes data back.

* Per module: revert the migration with git (restores the module's `.lang`
  lines, removes the contribution and the `.po`/`.pot`, e.g. `git revert` of
  the migration commit), run `composer dump-autoload` (rebuilds the component
  graph and the build) and `php cli/setup.php update`. Local changes of plural
  forms other than the default form are lost, values merged into the `.po` must
  be added to the `.lang` files.
* Per language: remove its shipped `.po` and run `php cli/setup.php build`.
  The overlay is kept unchanged and reconciled again once the `.po` is back.
* `artifacts/language/` can be deleted and rebuilt with
  `php cli/setup.php build`; until then migrated modules show `-identifier-`.

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
  languages, depending on the chosen mode. Mode `install` (`MODE_INSTALL`) fully installs a
  language that is not installed yet and is a no-op for an installed one. Mode `install_local`
  (`MODE_INSTALL_LOCAL`) only applies to already installed languages: it re-applies just the
  customizing/local file (`ilias_<lang>.lang.local`) on top of the stored data; not installed
  languages are skipped (reported as `not_installed_language_keys`). This is intended.
* **UpdateLanguage** (`src/Activities/UpdateLanguage.php`) - refreshes one or more already
  installed languages from the current language files. The entries of a module maintained in PO
  files are reconciled three-way with its shipped `.po`, see [Setup](#setup) above.
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

## Further Reading
* [use-language-object.md](use-language-object.md) and [use-language-logging.md](use-language-logging.md)
  document the older, still widely used `$lng`/`ilLanguage` access pattern. They predate the
  `ILIAS\Language\Language` interface and the Activities described above and have not been updated
  to reference them; prefer the "Using the Language Service" section above for new,
  component-revision code.
* [`tests/LanguageComponentGraphTest.php`](tests/LanguageComponentGraphTest.php) is a contract test
  for this component's `$define`/`$implement`/`$use`/`$contribute`/`$seek`/`$provide`/`$pull`/`$internal`
  wiring in `Language.php` and is a good place to check the current, authoritative wiring behaviour
  (e.g. singleton guarantees) rather than relying on prose descriptions here.
