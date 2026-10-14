# Language Identifier Enums

`generate_language_identifier_enum.php` generates the enum of the language identifiers of one module from
the module's `.pot` - an implementation of `ILIAS\Language\LanguageIdentifier`, to be passed to
`txt()` or `ntxt()` (see the README of the Language component) - and checks an existing enum
against the `.pot` (`--check`, for CI).

```php
$this->lng->txt(PollLanguageIdentifier::POLL_ANSWERS);          // no loadLanguageModule('poll') needed
$this->lng->ntxt(PollLanguageIdentifier::POLL_ANSWERS, $count); // plural form for $count
```

The enum is generated, not written by hand: it lists exactly the msgids of the `.pot`, so a key
that does not exist any more fails at the call site (an undefined enum case) instead of showing
`-key-` at runtime.

## Usage

Run from the ILIAS directory (in the PHP container of a development instance):

```
php components/ILIAS/Language/tools/language-identifier-enum/generate_language_identifier_enum.php \
    [--check | --stdout] [--module=<module>] --namespace=<Namespace> <pot file> <enum file>
```

- `<pot file>`: the `.pot` of the module (`components/<Vendor>/<Component>/lang/<module>.pot`).
- `<enum file>`: `<EnumName>.php`; the enum is named after the file, by convention
  `<Component>LanguageIdentifier`. Placed in the component's namespace root, e.g.
  `components/ILIAS/Poll/src/PollLanguageIdentifier.php` with `--namespace='ILIAS\Poll'`. Written
  atomically; its directory must exist - a component without `src/` yet (e.g. Poll,
  TermsOfService) creates it first, or uses `classes/`.
- `--module`: the module; default the `X-Domain` header of the `.pot` (written by
  `tools/po-migration/convert_module_to_po.php`), else the file name without `.pot`.
- `--stdout`: print the enum instead of writing it (e.g. for a proposal).
- `--check`: compare the enum with the `.pot` - missing or surplus cases, cases with another value,
  `module()` returning another module, not implementing `LanguageIdentifier`, not backed by string. Loads
  the enum file (`require`), so only use it on files of the repository. `module()` of an enum
  without cases cannot be called; there its body must be `return '<module>';` (as generated).

Both paths must lie inside the ILIAS directory; the path of the `.pot` (it is written into a doc
comment of the enum) may only contain `A-Z`, `a-z`, `0-9`, `_`, `.`, `/` and `-`. Exit codes: `0` success / no deviation, `1` the enum
deviates from the `.pot` (`--check`), `2` invalid arguments or input.

Generate the enum again after every change of the `.pot` (and the `.po` files) of the module; a CI
job can run `--check` for every enum.

Components are autoloaded through Composer's classmap: a new enum is only found after
`composer dump-autoload` (which also runs `php cli/setup.php build`).

## Case Names

- The identifier in upper case; every run of characters other than `A-Z`, `0-9`, `_` becomes one
  `_` (`poll_answers` -> `POLL_ANSWERS`, `a-b.c` -> `A_B_C`).
- A leading digit and the names PHP does not allow for a case - `CLASS` and `__HALT_COMPILER`
  (every keyword checked on PHP 8.4 and 8.5) - get the prefix `KEY_` (`1st` -> `KEY_1ST`).
- One case per msgid, also for plural messages (the msgid, not the `msgid_plural`).
- Identifiers that end up with the same name (e.g. `style` and `Style`, see
  `tools/po-migration/scan_shipped_case_duplicates.php`) are numbered deterministically and reported on
  stderr: the identifier in lower case keeps the name, the others (in byte order) get `_2`, `_3`, …,
  skipping names taken by other identifiers. Such a numbered name can change when identifiers are
  added or removed - prefer resolving the duplicate in the module.

## Files

The tool declares no named classes or functions (it lives in the classmap-scanned `components/`
tree, see `tools/po-migration/convert_module_to_po.php`). It reads the `.pot` through the
component's adapter `ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog` and writes
with `AtomicFileWriter`.
