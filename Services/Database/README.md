# Data Retrieval and Manipulation
With release 4.0 ILIAS uses ```PEAR::MDB2``` to provide a database abstraction layer that gives full portability to run ILIAS on MySQL or Oracle. In the future we would like to support at least PostreSQL as a third DBMS.
## Portability and Future Conventions
MDB2 offers a set of portability options, that are needed to run the same code with different database types. Since ILIAS 4.0 ILIAS has activated the full portability mode of MDB2.
 
See the [pear documentation](http://pear.php.net/manual/en/package.database.mdb2.intro-portability.php) for more info.

ILIAS requires that all **table** and **field** names use lower case characters a-z, underscore "\_" and numbers 0-9. Multiple words should be separated by "\_", e.g. "user\_id".

**Table names should start with the Service or Module ID, to make clear, which Service or Module is responsible for managing the table data e.g. "frm_posting".**

## ILIAS Database Object

ILIAS provides a global database object in variable ```$ilDB``` to access the database. All database functionality must be accessed through methods of this object. The object wraps a lot of MDB2 methods. Some ```$ilDB``` methods return MDB2 objects. Instead of calling their methods directly, these MDB2 objects should be passed to ```$ilDB``` again. Example:

```php
// WRONG
$result = $ilDB->query(...);
$n = $result->numRows();
 
// CORRECT
$result = $ilDB->query(...);
$n = $ilDB->numRows($result);
```

## Supported Column Types and Attributes
MDB2 supports a number of abstract column types and attributes. Not all of them should be used in ILIAS. The following table lists the supported column types, their attributes and the mapping to the different DBMS column types.

| MDB2 Type Supported by ILIAS | Supported Attributes                                | MySQL Mapping                             | Oracle Mapping                                          | 
|------------------------------|-----------------------------------------------------|-------------------------------------------|---------------------------------------------------------| 
| text                         | notnull, length (must be >0 <=4000), default, fixed | varchar, char                             | char, varchar2                                          | 
| integer                      | notnull, length (must be 1, 2, 3, 4 or 8),          | tinyint, smallint, mediumint, int, bigint | number(3), number(5), number(8), number(10), number(20) | 
|                              | unsigned must not be true, default                  |                                           |                                                         | 
| float                        | notnull, default                                    | double                                    | number                                                  | 
| date                         | notnull, default                                    | date                                      | date                                                    | 
| time                         | notnull, default                                    | time                                      | date                                                    | 
| timestamp                    | notnull, default                                    | datetime                                  | date                                                    | 
| clob                         | notnull, default                                    | longtext                                  | clob                                                    | 


## Queries
### SELECT Queries
Queries are done with the ```$ilDB-query(...)``` method. All parameters must be quoted by using the ```$ilDB->quote(...)``` method. With ILIAS 4.0 you must pass a second parameter with the quote-Method, the (abstract MDB2) type of the variable.

```php
$result = $ilDB->query("SELECT * FROM usr_data");
$result = $ilDB->query("SELECT * FROM usr_data WHERE id = ".$ilDB->quote($id, "integer"));
```

## Formatted Query
Using the quote method within the statements strings may look confusing if a larger number of parameters are included into the statement. An alternative is the ```$iDB->queryF(...)``` method, that uses ```%s``` placeholders for the parameters to be inserted. After the statement you must pass two arrays containing the types and the values for each parameter to the method.

```php
$result = $ilDB->queryF("SELECT * FROM desktop_item WHERE ".
        "item_id = %s AND type = %s AND user_id = %s",
        array("integer", "text", "integer"),
        array($a_item_id, $a_type, $a_usr_id));
```

### Executing multiple similar SELECTS
Prepared statements can be used for multiple similar operations. They **should be used only in rare cases**, where a lot of data is process with similar queries. In most of the cases prepared statements will reduce performance (e.g. in MySQL 5.0 query cache cannot be used).
Prepare queries are done using the `$ilDB->prepare(...)` and `$ilDB->execute(...)` methods. If the prepared query is not used anymore, use `$ilDB->free(...)` to deallocate the resources.

```php
$statement = $ilDB->prepare("SELECT firstname FROM usr_data WHERE usr_id > ? AND usr_id < ?",
        array("integer", "integer"));
$result1 = $ilDB->execute($statement, array(1000, 2000));
$result2 = $ilDB->execute($statement, array(2000, 3000));
$ilDB->free($statement);
```

### Fetching Result Row
To fetch a row from a result set you may either use `$ilDB->fetchAssoc(...)` or `$ilDB->fetchObject(...)`, which will return a result row as PHP associative array or as PHP object.

```php
while ($record = $ilDB->fetchAssoc($result))
{
        ...
}
```
or
```php
while ($record = $ilDB->fetchObject($result))
{
        ...
}
```

### Using Limits
Limits must be done with the `setLimit()` method of `$ilDB`. Limit is only allowed for SELECT queries, since it is not supported for data manipulation in MDB2.        

```php
$ilDB->setLimit(10, 0); // limit result to 10 datasets beginning at offset 0
$result = $ilDB->queryF("SELECT firstname FROM usr_data WHERE usr_id > %s AND usr_id < %s",
        array("integer", "integer"), array(1000, 2000)
);
while (($row = $ilDB->fetchArray($result))) 
{
    echo $row["firstname"] . "\n";
}
```

## Data Manipulation (INSERT/UPDATE/DELETE)

### Data Manipulation
For any data manipulation like INSERT, UPDATE and DELETE, you should usually use the `$iDB->manipulate(...)` operation.

```php
$affected_rows = $ilDB->manipulate("DELETE FROM my_table");
$affected_rows = $ilDB->manipulate("DELETE FROM my_table WHERE id = ".$ilDB->quote($id, "integer"));
```
### Formatted Data Manipulation
Using the quote method within the statements strings may look confusing if a larger number of parameters are included into the statement. An alternative is the `$iDB->manipulateF(...)` method, that uses %s placeholders for the parameters to be inserted. After the statement you must pass two arrays containing the types and the values for each parameter to the method.

```php
$ilDB->manipulateF("INSERT INTO desktop_item (item_id, type, user_id, parameters) VALUES ".
        " (%s,%s,%s,%s)",
        array("integer", "text", "integer", "text"),
        array($a_item_id,$a_type,$a_usr_id,$a_par));
```

### Multiple similar data manipulation
Similar to queries we use `$ilDB->prepareManip(...)` and `$ilDB->execute(...)` for prepared statements. This can be useful, if multiple similar data manipulations should be executed. Please use this methods only in rare cases, if you are sure, that performance decline will be limited (or performance will be improved).

```php
$statement = $ilDB->prepareManip("INSERT INTO usr_data (firstname, lastname) VALUES (?, ?)", 
        array("text", "text"));
$data = array("ILIAS", "Administrator");
$affectedRows = $ilDB->execute($statement, $data);
$ilDB->free($statement);
```

You can put data for multiple operations into an array and invoke `$ilDB->executeMultiple(...)` after `$ilDB->prepareManip(...)` for prepared statements.

```php
$statement = $ilDB->prepareManip("INSERT INTO usr_data (id, firstname) VALUES (?,?)",
        array("integer", "text"));
$data = array(
        array(1, "Mike"),
        array(2, "Phil"));
$ilDB->executeMultiple($statement, $data);
```

### The insert and update commands - Data manipulation when CLOB fields are involved
Insert and updates can also be done with special methods for these SQL data manipulation statements. If a CLOB field is involved in a data manipulation these commands must be used.

```php
$ilDB->insert("table_name", array(
        "field1" =>        array("text", $a_val1),
        "field2" =>        array("text", $a_val2),
        "field3" =>        array("clob", $a_val3)
));
```

```php
$ilDB->update("table_name", array(
        "field1" =>        array("text", $a_val1),
        "field2" =>        array("text", $a_val2),
        "field3" =>        array("clob", $a_val3)),
        array(
        "where1" =>        array("int", $a_where1),
        "where2" =>        array("text", $a_where2)
));
```

## Database Update Script
If you need to add, modify or rename tables or columns to the ILIAS database you need to add steps to the so called **database update script**. You find it in the directory **setup/sql**. It is called db\_update<nr>.php, the current one is always the one with the highest sequence number. The steps of this script are executed in the ILIAS setup in the database section of a client.
 
A typical DB update step looks like this:

```php
<#2950>
<?php
        $ilDB->modifyTableColumn('table_properties', 'value',
                array("type" => "text", "length" => 4000, "notnull" => true));
?>
```

The step starts with a sequential number <#Nr> and continues with a code block. It is important to understand that this **sequence of database update steps** is part of the **main ILIAS development branch**. You should never try to add steps in a "patched" version of ILIAS to this scripts, since these numbers identify a state of the database that must be given for all ILIAS installations (with the exception of plugins that add their own tables).
 
Core developers that add steps to this script must be subscribed to the ILIAS developer mailing list. We announced the creation of bug fix development branches and their relationship to the main development in this list, with a special focus on the database update script.

### Hotfix Scripts (Bugfix Branches)
When **stable bugfix branches** and **trunk** development in ILIAS go in parallel, only one branch can define new database steps in the database update script. This is usually the trunk development. However it may be necessary that tables or columns need to be added or modified for a bug fix within a stable branch.
 
For this purpose we have so called **hotfix scripts**. They work similar as the database update script, but have their **own numbering**. Their filename starts with the main release number, e.g. ```setup/sql/4_1_hotfixes.php``` for ILIAS **4.1.x**.

```php
<#3>
<?php        
        $ilDB->addTableColumn(
                'export_options',
                'pos',
                array(
                        'type'         => 'integer', 
                        'length'         => 4,
                        'notnull'        => true,
                        'default'        => 0
                )
        );
?>
```

Adding a step to the hotfix script is never enough. You must **ensure** that the **trunk database script is synchronized accordingly**. Add a new step that creates the same state, regardless of whether the hotfix has been applied to an installation or not (which we do not now):

```php
<#3205>
<?php
        if(!$ilDB->tableColumnExists('export_options','pos'))
        {
                $ilDB->addTableColumn(
                        'export_options',
                        'pos',
                        array(
                                'type'                 => 'integer', 
                                'length'         => 4,
                                'notnull'        => true,
                                'default'        => 0
                        )
                );
        }
?>
```

Note the `if(!$ilDB->tableColumnExists(...))` statement above, which is very important for these cases. There is a `if(!$ilDB->tableExists('...'))` as well.

## Creating, Modifying and Deleting Tables
Since ILIAS 4.0 tables will are defined in an abstracted way in ILIAS. Only the following `$ilDB` methods may be used, to create, modify and delete tables in the database update script.
To create a new table, use `$ilDB->createTable(...)`.

```php
<?php
$fields = array(
    'text_32_fixed' => array(
        'type'     => 'text',
        'length'   => 32,
        'fixed' => true
    ),
    'integer_small' => array(
        'type'     => 'integer',
        'length'   => 2
    ),
    'date_' => array(
        'type' => 'date'
    ),
    'timestamp_' => array(
        'type' => 'timestamp'
    ),
    'clob_' => array(
        'type' => 'clob'
    ),
    'blob_' => array(
        'type' => 'blob'
    )
);
 
$ilDB->createTable("my_table", $fields);
 
?>
```
Renaming a table is done with `$ilDB->renameTable(...)`.

```php
$ilDB->renameTable("old_table_name", "new_table_name");
```

To delete a table, use `$ilDB->dropTable(...)`.

```php
$ilDB->dropTable("my_table");
```

Adding new table columns is done by the `$ilDB->addTableColumn(...)` method.

```php
$ilDB->addTableColumn("my_table", "my_column", array("type" => "text", "length" => 20));
```

To modify a table use `$ilDB->modifyTableColumn(...)`.

```php
$ilDB->modifyTableColumn("my_table", "my_column", array("type" => "text", "length" => 30);
```

Renaming a column is done by `$ilDB->renameTableColumn(...)`.

```php
$ilDB->renameTableColumn("my_table", "old_column_name", "new_column_name");
```

To remove a table column, use `$ilDB->dropTableColumn(...)`.

```php
$ilDB->dropTableColumn("my_table", "column_name");
```

## Primary Keys and Indices
To add a primary key, use the `addPrimaryKey($a_table_name, $a_fields, $a_name = "pk")` method of `$ilDB`.

```php
$ilDB->addPrimaryKey("my_table", array("id"));
```

To drop a primary key, use function `dropPrimaryKey($a_table, $a_name = "pk")`.

```php
$ilDB->dropPrimaryKey("my_table");
```

Using indices is very similar. To create an index us `addIndex($a_table, $a_fields, $a_name = "indx")`.

```php
$ilDB->addIndex("my_table", array("id", "flag"), "id_flag");
```

To drop an index use `dropIndex($a_table, $a_name = "indx")`.

```php
$ilDB->dropIndex("my_table", "id_flag");
```

## Sequences / Auto Increments
To get rid of the MySQL specific auto_increment for unique ID's, MDB2 offeres sequences.
A sequence is special database table which is created automatically by MDB2 which increments a sequence field in the database and uses it to create unique values. In ILIAS the name of a sequence should be the same as the corresponding database table.
To create and drop sequences use `createSequence($a_table_name, $a_start = 1)` and `dropSequence($a_table_name)`.

```php
$ilDB->createSequence("my_table");
```

```php
$ilDB->dropSequence("my_table");
```

To obtain the next ID of a sequence use `nextId($a_table_name)`.

```php
$id = $ilDB->nextID('usr_data');
$statement = $ilDB->prepare("INSERT INTO usr_data (usr_id, firstname, lastname) VALUES (?, ?, ?)",
        array("integer", "text", "text")
);
$data = array($id, "ILIAS", "Administrator");
$statement->execute($data);
```

## Transactions
To use transaction, tables have to be converted to type InnoDB for MySQL first. The use of InnoDB and transactions is currently in a testing phase. We will introduce transactions for a smaller number of tables first to check the general influence on productive systems. **Please contact the core team if you want to use transactions or the InnoDB table engine.**

### Transaction Handling

```php
$ilDB->beginTransaction();
 
... // Data Manipulation Statements
 
if ($everything_ok)
{
      $ilDB->commit();
}
else
{
      $ilDB->rollback();
}
```

