Language Service
================

*document in progress*

# General Information

## Guidelines
There are a couple of guidelines defined in the [language.md](../../docs/development/language.md) that have to be respected when adding language variables to the lang files of ILIAS or editing them.

## Using the Global Language Object
The global language object can be retrieved from the dependency injection container by using/calling `$DIC['lng']` or
`$DIC->language()`. This is an instance of class `ilLanguage` and provides methods to access these strings in the 
language of the user within the current authentication process. This is done by using the functions
 `loadLanguageModule()` and `txt()`.

        $lng->loadLanguageModule("frm");
        $tpl->setVariable("TEXT", $lng->txt("frm_new_posting"));    


## Supported HTML Tags in Language Files
Only a defined set of HTML tags are allowed to be used within the `text_content` of a language entry:

* All tags allowed by `getSecureTags` from `ilUtil`: `a`, `b`, `bdo`, `code`, `div`, `em`, `gap`, `i`, `img`, `li`, `ol`, `p`, `pre`, `strike`, `strong`, `sub`, `sup`, `u` and `ul`
* In addition: `span` and `br`

All other HTML tags are unsupported and will be removed by `ilUtil::stripSlashes`.
