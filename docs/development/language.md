ILIAS Language Handling
=======================
ILIAS offers multi-language support for the user interface of ILIAS. Language handling in ILIAS is realised by the [Language](../../components/ILIAS/Language/README.md) service.

# Guidelines
1.  All language entries are text strings. They are grouped in language modules, and each module is shipped by
    the component it belongs to as gettext files in the component's `lang/` directory: a template
    `<language_module_ID>.pot` and one `<language_module_ID>_<lang>.po` per language (e.g.
    `components/ILIAS/Forum/lang/forum.pot` and `forum_de.po`). Each language entry has the format:

        #. comment
        msgid "variable_ID"
        msgstr "text_content"

    `variable_ID` and `text_content` are REQUIRED, while the extracted comment (`#.`) is OPTIONAL and can be used
    for additional information about an entry. A component contributes each of its modules through
    `$contribute[LanguageFileDirectory::class]` in its `<Component>.php`. The files in the global directory
    `/lang` no longer hold any entries.

2. The `variable_ID` of a language entry MUST be unique across all language modules. This avoids conflicts in the
presentation of language entries because the `language_module_ID` is not taken into consideration when ILIAS inserts
language entries into the output (the module loaded last wins). The uniqueness of the spelling must be guaranteed
regardless of upper and lower case. Having the entries `login` and `Login` would violate the rule.

3. The following naming conventions must be respected when introducing new language variables.
3.1. New components MUST use the `object_ID` for the `language_module_ID` as defined in the related module.xml or 
service.xml.
3.2. The `language_module_ID` MUST also be used as a prefix for the variable names in this language module, 
e.g. in `forum.pot`:

        msgid "frm_new_posting"

3.3. Language variables used as alt texts MUST be easily identifiable by an added `_alt` at the end of the `variable_ID`.
e.g. for an alt text that belongs to an expand glyph: 

        msgid "expand_reload_alt"

3.4. Language variables used as bylines for a setting option MUST have `_info` at the end of the `variable_ID`.
e.g. for a byline to the setting `#default_auth_mode#`:

        msgid "default_auth_mode_info"

4. The language module `common` is shipped by the Language component (`components/ILIAS/Language/lang/common.pot`).
It MUST only be used for language entries that are used by various components and in combined contexts. Because
`common` is loaded for each request, the use of new `common` variables SHOULD be minimised.
 
5. To keep the language files maintainable and facilitate translation and creation of new language versions, 
the amount of language entries should be as low as possible. Therefore, language entries that are no longer 
used in ILIAS due to refactorings or changes in the code MUST be removed from the `.pot` and all `.po` files of
their module. Whenever possible, existing language entries SHOULD be reused and probably moved to the `common`
module to avoid multiple entries of the same meaning.

6. English is the master language. New variables MUST be added at least to the `.pot` and the English `.po` of their
module, since we synchronise the variables when preparing a new ILIAS release. If a variable exists in the `.po`
of another language but not in the English one, the entry will be removed from the file during synchronisation.

7. When, for a new or upcoming version of ILIAS, the underlying functionality of a feature or object changes to 
such an extent that the text_content of any corresponding language entries needs to be totally rewritten, create 
instead NEW language entries. This prevents corrections or improvements that arise during ongoing work on the 
current version of ILIAS (e.g. on the English language files or due to the gender mainstreaming process) from being 
merged into the next version and thereby incorrectly reverting affected text_content to a previous (albeit slightly 
improved or gender-conform) state.

# Additional Information
## Loading Language Entries
Changed language files are not available in the user interface automatically: `php cli/setup.php build` (also run by
`composer install` and `composer dump-autoload`) MUST be run after every change of a shipped `.po`, and
`php cli/setup.php update` writes the values to the database for the language administration.

## Gettext Files
The shipped `<module>_<lang>.po` is the only source of the translations of a module; they are never read from the
database. ILIAS serves them through native gettext, so the PHP extension `gettext` and the `C.utf8` locale MUST be
available to the PHP of the web server (see [the installation guide](../configuration/install.md)). When a module is
converted or synchronised, an entry a language does not translate gets the English text, marked `#, fuzzy`. A key only
added to the `.pot`/English `.po` shows `-identifier-` in the other languages until they are synchronised; `txt()`
with a `LanguageIdentifier` serves the default language's text then. `php cli/setup.php install`/`update` build the
language files themselves and therefore also need the PHP extension `gettext` and a writable `artifacts/language/`.
The developer mode action that merges local changes into the global language files writes the local changes into the
shipped `.po` files. Each installation keeps its local changes in the client data directory, which is why the ILIAS
setup MUST be run as the web server user. Plural forms are available through `txt($a_topic, $n)`, and a component can
address its entries through a generated `LanguageIdentifier` enum. Plugins still ship `.lang` files. See [the Language
component](../../components/ILIAS/Language/README.md#translations-maintained-in-po-files) for details.

## Supported HTML Tags in Language Files
Only a defined set of HTML tags and attributes is allowed within the `text_content` of a language
entry. The rules are defined by `TranslationMarkupPolicy` and listed in
[the Language component](../../components/ILIAS/Language/README.md#markup-policy). Values that
break them are rejected when entered in the language administration and removed when the `.po` files
are compiled.

## Using the Language Service
New component-revision code should depend on `ILIAS\Language\Language`. During the legacy bootstrap this interface is
provided through a lazy bridge and delegates to the active `ilLanguage` runtime instance after the legacy container has
been initialised.

Legacy components may continue to use `$DIC['lng']` or `$DIC->language()` during the transition. These access paths
are compatibility provisions for non-migrated code and are not the preferred API for new components.

Setup code uses the separate `ilSetupLanguage` implementation. It is intentionally independent of runtime user,
session, and container state and must not be selected for normal runtime initialisation.

        $language->loadLanguageModule("frm");
        $tpl->setVariable("TEXT", $language->txt("frm_new_posting"));

# Roles
The language handling process in ILIAS knows four distinct roles:

* The *First language maintainer* is managing the language handling process and fixing or delegating language bugs. The
first maintainer has to be notified about newly introduced languages and changes of the language version maintainers.
* The *Second language maintainer* is responsible for the language component.
* *Developers* add new language entries to their components, modify them or remove unused entries from the code.
* *Language version maintainers* are volunteers, take care of a specific language version and translate new entries 
to the related language.

# Maintaining Languages
The different languages supported by ILIAS are maintained by volunteers. If you want to help us to improve an existing language or introduce and maintain a new one, please contact the ILIAS product manager: info@ilias.de.

You find more information about language maintenance in the document 
[Language Instructions](https://docu.ilias.de/goto_docu_lm_37.html).
