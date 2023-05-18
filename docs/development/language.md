ILIAS Language Handling
=======================
ILIAS offers multi-language support for the user interface of ILIAS.

# Guidelines
1.  All language entries are text strings and stored in language files in the 
    subdirectory `/lang`. Each language entry has the format:

        language_module_ID#:#variable_ID#:#text_content###comment

    The elements `language_module_ID`, `variable_ID` and `text_content`  are REQUIRED, 
    while `comment` is OPTIONAL and can be used for additional information about an 
    entry, e.g. „`07 02 2020 new variable`“.
    
2. The `variable_ID` of a language entry MUST be unique within the whole language file. This avoids conflicts in the 
presentation of language entries because the `language_module_ID` is not taken into consideration when ILIAS inserts 
language entries into the output. The uniqueness of the spelling must be guaranteed regardless of upper and lower case.
Having a language entry `common#:#login#:#…` and  `common#:#Login#:#…` would violate the rule.

3. New components MUST use the `object_ID` for the `language_module_ID` as defined in the related module.xml or 
service.xml. The `language_module_ID` MUST also be used as a prefix for the variable names in this language module, 
e.g.:

        frm#:#frm_new_posting#:#New Posting

4. Each language file contains one block with language_module_ID `common`. Entries of this block start with 
`common#:#`. The language_module_ID `common` MUST only be used for language entries that are used by various 
components and in combined contexts. Because this block is always read from the database into memory for each 
request, the use of new `common` variables SHOULD be minimised.
 
5. To keep the language files maintainable and facilitate translation and creation of new language versions, 
the amount of language entries should be as low as possible. Therefore, language entries that are no longer 
used in ILIAS due to refactorings or changes in the code MUST be removed from the English language file. 
Whenever possible, existing language entries SHOULD be reused and probably moved to the `common` module to 
avoid multiple entries of the same meaning.

6. The English language file is the master language file. New variables MUST be added at least to this file, 
since we synchronise the variables when preparing a new ILIAS release. If a variable exists in a file of another 
language but not in the English one, the entry will be removed from the file during synchronisation.

# Additional Information
Adding new entries into language files will not make them available in the user interface automatically. You need to 
refresh the languages by executing the `Refresh Languages` action in the global ILIAS language administration
(`Administration » Languages`).
 
The global language object can be retrieved from the dependency injection container by using/calling `$DIC['lng']` or
`$DIC->language()`. This is an instance of class `ilLanguage` and provides methods to access these strings in the 
language of the user within the current authentication process. This is done by using the functions
 `loadLanguageModule()` and `txt()`.

        $lng->loadLanguageModule("frm");
        $tpl->setVariable("TEXT", $lng->txt("frm_new_posting"));    

# Roles
The language handling process in ILIAS knows four distinct roles:

* The *First language maintainer* is managing the language handling process and fixing or delegating language bugs. The
first maintainer has to be notified about newly introduced languages and changes of the language version maintainers.
* The *Second language maintainer* is responsible for the language component.
* *Developers* add new language entries to their components, modify them or remove unused entries from the code.
* *Language version maintainers* are volunteers, take care of a specific language version and translate new entries 
to the related language.

# Maintaining Languages
The different languages supported by ILIAS are maintained by volunteers. The ILIAS society is offering language 
maintenance installations for every version. These are clients of the regular testing installations.

* http://lang54.ilias.de is the language maintenance installation for 5.4
* http://lang6.ilias.de is the language maintenance installation for 6, a.s.o.

You find more information about language maintenance in the document 
[Language Instructions](https://docu.ilias.de/goto_docu_lm_37.html).
