# Language Handling in ILIAS

ILIAS offers multi-language support. Guidelines and additional information about the syntax and how to create language entries and the language handling in ILIAS are described in the file [language.md](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/language.md)

## $lng Language Object

The global language object `$lng`, an instance of class ilLanguage, provides methods to access these strings in the language of the user currently logged in. This is done by using the functions `loadLanguageModule()` and `txt()`.

```php
$lng->loadLanguageModule("frm");
$tpl->setVariable("TEXT", $lng->txt("frm_new_posting"));
```
