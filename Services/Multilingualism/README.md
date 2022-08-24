# Multilinguasm

There is a high dependency of this component to Services/Object, especially the ObjectTranslation classes.

## Variants of support
- Objects may not use any translations at all
- use translations for title/description only or
- use translation for (the page editing) content, too.

## Naming

The naming of variables/concepts has become inconsistent over time.

### Master Language

- If no translation mode for the content is active no master lang will be
 set and no record in table obj_content_master_lng will be saved. For the title/descriptions the master language will be marked by field lang_default in table object_translation. (To do: Rename this to "master" internally)
- If translation for content is activated a master language must be set (since concent may already exist the language of this content is defined through setting the master language (in obj_content_master_lng). Modules that use this mode will not get informed about this, so they can not internally assign existing content to the master language.

### Fallback Language

- If content translation is enabled, a fallback language can be set. This is the language content should be retrieved from, if content in the user language is not available. Also the title/description should be returned in the fallback language, if not available in the user language.

### Default Language

- The `ilObjectTranslation` class contains methods to get the default language, description and title, as well to set the default title and description.
- Default language here means: Fallback language, if given, master language otherwise.

## Storing title/description

- The `object_translation` table should always contain all translations, marking the master language as default_lang (which should be renamed to master, see above)
- The `object_data` table must always contain title/description in the default language (fallback, if given, master otherwise)
