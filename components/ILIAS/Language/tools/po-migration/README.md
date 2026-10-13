# PO Migration Tools

Tools to move a language module from the global `lang/ilias_<lang>.lang`
files to gettext files shipped by its component, and to check the global
language files before doing so. How modules maintained in PO files are built,
served and edited is described in the
[Language component's README](../../README.md#translations-maintained-in-po-files).

1. [convert_module_to_po.php](#convert_module_to_pophp)
2. [plurals.json](#pluralsjson)
3. [Checks of the Global Language Files](#checks-of-the-global-language-files)
4. [Tests](#tests)

All tools are run from the ILIAS root directory with the PHP CLI. They declare
no named classes or functions, so they never end up in Composer's classmap.

## convert_module_to_po.php

Generates the initial shipped `.pot` and `.po` files of one module from the
lines of that module in `lang/ilias_*.lang` (never write them by hand). After
the migration the `.po`/`.pot` files are maintained directly. A new run
regenerates them from the `.lang` lines and overwrites values merged into the
`.po` by the language administration ("merge"), keys added there and their
`#.` lines; after `--remove-from-lang` it fails, since the module has no
`.lang` lines any more.

```
php components/ILIAS/Language/tools/po-migration/convert_module_to_po.php \
    [--pattern=<pattern>] [--skip-unmigratable-keys] [--remove-from-lang] \
    <module> [referenceLanguage] [outputDir]
```

Arguments:

* `<module>`: the language module (`lng_data.module`), `[A-Za-z0-9_]` only.
* `referenceLanguage`: default `en`. It is the template of every language: it
  gives the `msgid`s, their order and the `.pot`.
* `outputDir`: default `tools/po-migration/output/<module>/` (ignored by git).
  To migrate a module, pass the `lang/` directory of the owning component.

Options:

* `--pattern=<pattern>`: file name of a language without `.po`, `%s` is the
  language key (default `<module>_%s`, e.g. `ilias_%s`). It must match the
  pattern of the module's `ComponentLanguageFileDirectory`. The `.pot` is named
  after the pattern without `%s`.
* `--skip-unmigratable-keys`: instead of failing, leave out (with a `WARNING`
  on stderr) every key the reference language does not have, the empty key and
  every key containing ` [` (the notation of a plural form), and use one
  occurrence of a key that occurs several times in a language with the same
  value and comment. Duplicates with different values still fail.
* `--remove-from-lang`: after a completely successful run, remove every line of
  the module (the left out ones included) from every `lang/ilias_<lang>.lang`
  that was read, atomically per file and keeping every other byte. Checked
  before anything is read: `outputDir` must be an existing
  `components/<Vendor>/<Component>/lang` whose `<Component>.php` already
  contributes the module's `ComponentLanguageFileDirectory` (literal arguments,
  same pattern), and no `lang/ilias_<lang>.lang` may be a symbolic link. If one
  file cannot be processed, no `.lang` file is changed. Afterwards the `.po`
  files are the only source of the module; a second run for it fails.

Unknown options fail, so a typo is never taken as module name or directory.

Examples (how the pilot modules were migrated):

```
php components/ILIAS/Language/tools/po-migration/convert_module_to_po.php --remove-from-lang tos en components/ILIAS/TermsOfService/lang
php components/ILIAS/Language/tools/po-migration/convert_module_to_po.php --remove-from-lang poll en components/ILIAS/Poll/lang
```

### Input

* Every `lang/ilias_<lang>.lang` of the ILIAS root, lines trimmed like the
  installer does. Component and customizing `.lang` files are not read.
* `plurals.json` next to the tool (see below). Without it no `Plural-Forms`
  header and no plural message is written (a note is printed).
* For the `Language-Team` header: `meta_l_<lang>` of `lang/ilias_en.lang`, or of
  a shipped `components/*/*/lang/meta_en.po` once `meta` is migrated (default
  file name pattern only).
* Headers of an already existing target `.po` are kept.

### Output

* `<module>.pot` (or after `--pattern`): every `msgid` with empty `msgstr`.
* One `.po` per language: `msgid` is the language key, there is no `msgctxt`
  (the file belongs to exactly one module). Headers `Content-Type` (UTF-8),
  `Language` (the language key), `Language-Team` (the English language name),
  `Plural-Forms` (from `plurals.json`), `X-Domain`.
* A `###` comment that is a "new variable" placeholder becomes `#, fuzzy`, any
  other comment an extracted comment `#.`.
* A key the language lacks, or has with an empty value, gets the value of the
  reference language and `#, fuzzy`. Fuzzy entries are compiled and served like
  all others, so a language without translation shows the English text instead
  of `-identifier-`.
* No `.mo` file and nothing in the database: run `php cli/setup.php build`
  afterwards.

### Checks

Nothing is written if a language has a key the reference language lacks, an
empty key, a key with ` [` or a duplicate key (unless
`--skip-unmigratable-keys`), if a plural definition of `plurals.json` does not
fit the module, or if the module has no line in any `.lang` file. After writing,
every `.po` is read again with the strict loader and compared with its source:
every value, every plural form and the default form of a plural message (exit
code 1 on any difference). The tool reads and writes through the same adapter
as the language component (`TranslationCatalog`), so it writes exactly what
Setup and the administration read.

stdout lists per language the module's own lines (`entries`), those with a
"new variable" marker (`fuzzy`), the entries filled from the reference language
(`filled`) and the result of the round trip (`po_ok`). Exit code 0 means every
file was written and verified.

Before committing generated files, compare the placeholders (`%s`, `[x]`) of
each language with the reference language.

## plurals.json

Input of `convert_module_to_po.php`:

* `plural_forms`: the `Plural-Forms` header of each of the 31 languages of
  `lang/`, written into every generated `.po` (not into the `.pot`). Source:
  CLDR 48.2, integer categories. When adding a language or following a new CLDR
  release, change the header here, regenerate the `.po` files of the migrated
  modules and extend `PluralFormsShippedRulesTest` (it fails for a language
  without its independent reference rule). CLDR 49 will change `vi` to two
  forms.
* `modules.<module>`: the plural messages of a module,
  `msgid => {"singular": <key>, "plural_id": <msgid_plural>}` (both optional,
  `msgid_plural` defaults to `<msgid>_plural`). The tool invents no text: the
  value of the singular key goes into the form the language's rule uses for
  n = 1 only (if the language translated it), every other form gets the
  message's own value. A message copied into several forms is fuzzy, except in
  the reference language, `de` and `en`.

**Changing a rule:** local form values in existing overlays follow the new rule
by meaning (`MigratedLanguageFileSync::withPluralRuleOf()`, `PluralForms::previousFormsOf()`):
a new form takes the local value of the old form serving the same sampled
quantities; a form covering several old forms takes their value if it is the
same, the new default form the old default form; any other form gets the shipped
value, and a local value that ends up in no form is logged when the overlay is
rewritten (`setup update`). Until that rewrite the runtime serves the overlay's
local plural messages with the old rule (its `.mo` carries the old header);
`setup build` alone is not enough for them. Form keys in import files refer to the current rule.

## Checks of the Global Language Files

Read-only scripts that report problems of the shipped `lang/ilias_*.lang` files
as Markdown tables on stdout (German headers). Run them on the host or in a
container with `git` (the caller columns use `git grep`), e.g.:

```
php components/ILIAS/Language/tools/po-migration/scan_shipped_markup.php > markup.md
```

* `scan_shipped_markup.php`: every value that `TranslationMarkupPolicy` does
  not allow, grouped by key with languages and violations. Such values are
  compiled cleaned once their module is migrated; they should be fixed in the
  `.lang` files first.
* `scan_shipped_case_duplicates.php`: identifiers of one module that occur in
  one file in spellings differing only in case (or accents). The primary key of
  `lng_data` is case-insensitive, so such a pair shares one row.
* `scan_shipped_cross_module_duplicates.php`: identifiers that occur in more
  than one module, with the kind of difference and the number of literal
  callers. `txt()` serves the value of the module loaded last; for two migrated
  modules `setup build` lists such identifiers as well.

Their results are reported as issues to the maintainers of the language files,
not kept in the repository.

## Tests

* `components/ILIAS/Language/tests/ConvertModuleToPoToolTest.php`: covers
  options, failures, fuzzy handling, plural messages and `--remove-from-lang`.
* `components/ILIAS/Language/tests/ComponentTranslation/PluralFormsShippedRulesTest.php`:
  every header of `plurals.json` against an independent CLDR reference.

```
php vendor/composer/vendor/phpunit/phpunit/phpunit -c scripts/PHPUnit/phpunit.xml \
    components/ILIAS/Language/tests/ConvertModuleToPoToolTest.php
```
