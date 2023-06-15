# $lng language object

ILIAS offers multi-language support. All text strings are stored in **language files** in the subdirectory `lang`. The lines of these files have the format:
 
`module#:#variable_name#:#text content`
 
It is best practice that as module a short ID for your component is used, e.g. frm for forums. This should also be used as a prefix for the variable names in this language module, e.g.:
 
`frm#:#frm_new_posting#:#New Posting`
 
The file contains one module block called the **common module**. Entries of this block start with "common#:#". This block is always read from the database into memory for each request. Other modules need to be loaded on demand. **Try to minimize the use of new common variables**.
 
The **english language** file is the **master language file**. This means you must always add new variables to this file, since we synchronize the variables of all files from time to time. If a variable exists in a file of another language then English, but not in the English one, it will be removed from the file.
 
Adding new entries into these files will not make them available automatically in the user interface. You need to refresh the languages by executing Administration > Languages > **Refresh Languages** in your ILIAS installation.
 
The global language object `$lng`, an instance of class ilLanguage, provides methods to access these strings in the language of the user currently logged in. This is done by using the functions `loadLanguageModule()` and `txt()`.

```php
$lng->loadLanguageModule("frm");
$tpl->setVariable("TEXT", $lng->txt("frm_new_posting"));
```

## Maintaining Languages

The different languages supported by ILIAS are maintained by volunteers. We are offering language maintenance installations for every version. They are clients of the regular testing installation.

- [http://lang54.ilias.de](http://lang54.ilias.de) is the language maintenance installation for 5.4
- [http://lang6.ilias.de](http://lang6.ilias.de) is the language maintenance installation for 6 a.s.o.

You find more information about language maintenance in the document ['Language Instructions'](https://docu.ilias.de/goto_docu_pg_130_37.html).