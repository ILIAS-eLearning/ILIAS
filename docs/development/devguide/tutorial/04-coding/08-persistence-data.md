# Database Usage In ILIAS

## What is This About?

In this tutorial you will learn how to create and use a database table in ILIAS.

As an example, we will use a to-do list that can be displayed and edited on the ILIAS dashboard, for example. Your database table should contain the following columns:

* **todo_id**: unique identifier of the list entry (integer)
* **user_id**: unique identifier of the ILIAS user account to which the entry belongs (integer)
* **title**: title of the entry (text, max. 250 characters)
* **description**, description of the task (text, any length)
* **deadline**, deadline by which the task must be completed (date)

## Why do I need This in ILIAS?

Database tables are used to store small pieces of information that are entered directly or generated automatically when using ILIAS and that should be retained even after the ILIAS session has ended. Tables are suitable for quick access, good searchability and frequent changes.

## How to Proceed?

ILIAS is designed for the use of MysQL or MariaDB as database systems, but also supported Oracle and PostgreSQL in former versions. To keep this flexibility, database calls must use the interface [ilDBInterface](../../../../../components/ILIAS/Database/interfaces/interface.ilDBInterface.php), whose functions largely abstract the peculiarities of certain SQL dialects.

The interface provides functions for creating and changing database tables as well as for the typical CRUD operations (Create, Read, Update, Delete). Changes to the data schema must be made in the ILIAS setup or when updating a plugin. Do not use such functions in the normal application!

### Create a database table for an ILIAS component

Each ILIAS component that needs database changes via the setup must provide two classes in its subfolder `classes/Setup` that implement the interfaces `ILIAS\Setup\Agent` and `ilDatabaseUpdateSteps`. A minimal implementation for our example can be found in the following files:

* [ilTodoSetupAgent](TodoExample/classes/Setup/class.ilTodoSetupAgent.php)
* [ilTodoDBUpdateSteps](TodoExample/classes/Setup/class.ilTodoDBUpdateSteps.php)

The setup agent integrates the class of the update steps into the setup. The individual update steps are numbered `step_x` functions:

````php
    public function step_1()
    {
        if (! $this->db->tableExists('todo_items')) {
            $this->db->createTable('todo_items', [
                'todo_id' => ['type' => 'integer', 'length' => '4', 'notnull' => true],
                'user_id' => ['type' => 'integer', 'length' => '4', 'notnull' => true],
                'title' => ['type' => 'text', 'length' => '250', 'notnull' => true],
                'description' => ['type' => 'clob', 'notnull' => false],
                'deadline' => ['type' => 'date', 'notnull' => false],
            ]);

            $this->db->createSequence('todo_items');
            $this->db->addPrimaryKey('todo_items', ['todo_id']);
            $this->db->addIndex('todo_items', ['user_id'], 'i1');
        }
    }
````

Each update step that creates a table should first use `tableExists()` to check whether the table already exists and only create it if not. This requires several steps:

1. the table is created with `createTable()`. The first parameter is the table name. All table names of a component should use the same prefix (here `todo`). This is followed by an associative array with the column names as keys and as values arrays to define their properties:
    * `type` specifies the data type. Allowed types are: `integer` (integer number), `float` (floating point number), `text` (character string with limited length), `clob` (character string with unlimited length), `date` (date value), `time` (time value) and `timestamp` (date and time values combined).
    * `length` specifies the maximum length for text and the number of bytes for integer.
    * `notnull` determines whether the column prohibits NULL values.
2. a unique counter for the todo_id is generated with `createSequence()`. This effectively creates an auxiliary table whose name corresponds to the table with `_seq` appended.
3. the primary key of the table is set to the todo_id with `addPrimaryKey()`. The primary key is specified as an array of column names and can therefore be composed.
4. an additional index is created on the user_id with `addIndex`, which accelerates the query of the todo lists of individual user accounts.


### Create a Database Table for a Plugin (up to ILIAS 9)

In a plugin for ILIAS, you create a new database table via the file `sql\dbupdate.php`, which is executed when your plugin is updated. The individual update steps are numbered there with identifiers of the form `<#1>`, which are followed by the PHP code to carry out the change.

The database update step then looks like this:

````php
<#1>
<?php
        if (!$ilDB->tableExists('todo_items')) {
            // see above example
         }

?>
````

`$ilDB` is an instance of the class that provides the functions of the `ilDBInterface`. It is available in the dbupdate script as a global variable. 

### Database Access in the Application ###

To access the ILIAS database, the classes of your component also require an instance of the global database class. If it cannot be passed as a parameter of the constructor, it should be taken from the dependency injection container of ILIAS, which is available as a global variable `$DIC`.

````php
      public function __construct() 
      {
         global $DIC;
         $this->db = $DIC->database();
      }
````

The following examples follow the [repository pattern](../../../repository-pattern.md), which is recommended for data processing in ILIAS. According to this pattern, all functions for reading and saving data from an application domain should be implemented in a central class, the repository. Composite data is passed between the repository and the application classes as data objects.

You can find the example files for the data class and the repository here:
* [ilTodoItem](TodoExample/classes/class.ilTodoItem.php)
* [ilTodoRepository](TodoExample/classes/class.ilTodoRepository.php)

### Reading Data

If you want to display the entries of a to-do list on the dashboard, you must read them from the database table. The following example generates a database query, executes it and creates an array of data objects that can then be processed further.

````php
    public function getItemsOfUser(int $user_id) : array
    {
        $items = [];
        
        $query = "SELECT * FROM todo_items WHERE user_id = " 
            . $this->db->quote($user_id, 'integer');
        
        $result = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($result)) {
            $items[] = new TodoItem(
                $row['item_id'],
                $row['user_id'],
                $row['title'],
                $row['description'],
                $row['deadline']]
            );
        }
        return $items;
    }
````

1. the database query is defined as `$query`. It is important to insert all parameters of the query via the `quote()` function. In addition to the value to be inserted, it also receives its data type and inserts an appropriately formatted character string.
2. the data is queried with `query()` and a result object is created.
3. with `fetchAssoc()` the individual data records are read from the result object one after the other as associative arrays.

As an alternative to `query()`, you can use the `queryF()` function, in which the query is passed with placeholders `%s` and the parameters are passed as arrays of data types and values. This can be clearer with many parameters, but makes it more difficult to output the specific query before it is executed when debugging. The example in [ilTodoRepository](TodoExample/classes/class.ilTodoRepository.php) is written this way.

### Saving Data

New entries on the to-do list must be saved in the table. The following example takes an entry as a data object and saves it as a new data record:

````php
    public function createItem(TodoItem $item) : TodoItem
    {
        $todo_id = $this->db->nextId('todo_items');
        
        $this->db->insert('todo_items', [
            'todo_id' => ['integer', $todo_id],
            'user_id' => ['integer', $item->getUserId()],
            'title' => ['text', $item->getTitle()],
            'description' => ['clob', $item->getDescription()],
            'deadline' => ['date', $item->getDeadline()]
        ]);
        
        return $item->widthTodoId($todo_id);
    }
````

1. a new sequence value for the primary key field todo_id is generated with `nextId()`.
2. the `insert()` function receives the table name and an associative array with the column names as keys and arrays with data types and contents as values.

### Updating Data

If an entry in the to-do list has been edited, its data record in the table must be updated:

````php
    public function updateItem(TodoItem $item) : void
    {
        $this->db->update('todo_items', [
            'user_id' => ['integer', $item->getUserId()],
            'title' => ['text', $item->getTitle()],
            'description' => ['clob', $item->getDescription()],
            'deadline' => ['date', $item->getDeadline()]
        ], [
            'todo_id' => ['integer', $item->getTodoId()]
        ]);
        
    }
````
Like the *insert()* function, the `update()` function also receives the table name as the first parameter, but then two associative arrays. The first contains all the columns that are to be updated and the second all the columns of the primary key used to find the data record to be updated.

### Deleting Data

Finally, when deleting an entry from the to-do list, its data record must be deleted. There is no special function for this, i.e. the DELETE statement must be written and executed directly:

````php
    public function deleteItem(ToDoItem $item) : void
    {
        $query = "DELETE FROM todo_items WHERE todo_id = " 
            . $this->db->quote($item->getTodoId(), 'integer');
        
        $this->db->manipulate($query);
    }
````

1. when writing the query, again make sure to quote all parameters with `quote()`.
2. instead of `query()` the function `manipulate()` must be used for data manipulations.

As an alternative to `manipulate()`, you can use the `manipulateF()` function, in which the query is passed with placeholders `%s` and the parameters are passed as arrays of data types and values. This can be clearer with many parameters, but makes it more difficult to output the specific statement before it is executed when debugging. The example in [ilTodoRepository](TodoExample/classes/class.ilTodoRepository.php) is written this way.

The `manipulate()` function can also be used to execute queries for inserting or updating data records. In most cases, however, the use of `insert()` and `update()` is preferable. When using `clob` columns, these functions must be used.

## What do I Need to Watch Out For? (Dos & Dont's)

### Security

**SQL injection** refers to attacks in which SQL queries are manipulated by user input, imports or changes to request parameters. Attackers can use this to execute additional queries, e.g. to delete or modify data or possibly also to execute system commands on the server.

**To avoid such attacks, all parameters of a query must be quoted with the `quote()` function.** It avoids the introduction of special characters such as apostrophes, inverted commas, backslashes or double hyphens which change the intended query.

### Database Abstraction

Even though support for Oracle and PostgreSQL has been dropped, it is possible that another database system will become important in the future. You should therefore ensure that your code supports the ILIAS database abstraction.

* Use the functions `insert()` and `update()` from the database interface.
* Use general SQL code wherever possible and avoid special syntax or SQL functions.
* Use the functions of the database interface in your SQL code wherever possible, e.g. `in()` and `like()` for formulating field conditions instead of the corresponding language constructs of SQL.


### Performance

A frequent cause of slow ILIAS response times is too many or too slow database queries.
Therefore, when designing your database schema and queries, pay attention to the performance of the system.

* Create **indices** for columns that are used as conditions or in joins.
  For queries, only read the columns that you need for further processing.
* Avoid querying individual data records in loops. Try to query and process the required data records of a table together.
* If multiple queries cannot be avoided because their functions are called by other components, data caching can increase performance. However, make sure that large amounts of data do not lead to memory overflow and that you do not continue working with outdated data after updates in a request.
* Consider how your database functions will behave if an installation has large tables with many data records and many simultaneous users. 







