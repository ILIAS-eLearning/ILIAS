# Language Logging

___
*This feature is available for ILIAS 5.2.x+.*
___

After years and years of ILIAS development by a big amount of developers and language maintainers, it's necessary to tidy up the language files. To get an overview of which language variable is needed or isn't needed anymore, there is a language logging in ILIAS.

## Requirements

- MySQL Database
- Developer Mode turned on or client.ini entry `LANGUAGE_LOG = "1"` in section `[system]`

## Evaluation

If both requirements are fulfilled, every used language variable will be stored in the database as "module" and "Identifier" in table `lng_log`. After **extensive** usage of ILIAS, these data could be used to decide if a language variable could be deleted or kept according to its appearance in this dataset.

There is no function provided by ILIAS itself to gain this data. You have to access the database of ILIAS with a script and obtain it by a SQL-statement.

```php
<?php
//Init ILIAS for a database connection
require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();
global $ilDB;
header("Content-Type:text/plain");
 
//Collects all language variables which were not used since logging
$lang_log_query = $ilDB->query(
	"SELECT ldat.module module, ldat.identifier identifier " .
	"FROM lng_data ldat LEFT JOIN lng_log llog " .
	"ON (ldat.module = llog.module AND ldat.identifier = llog.identifier) " .
	"WHERE ldat.lang_key = 'en' AND llog.module IS NULL AND llog.identifier IS NULL"
);
 
while($row = $lang_log_query->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
{
	echo($row->module . "#:#" . $row->identifier . "\n");
}
```

After execution it's recommended to delete all data in lng_log to build a new 'clean' dataset. This should be done via SQL by executing:

```
DELETE FROM lng_log;
```