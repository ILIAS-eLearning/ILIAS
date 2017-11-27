# How to use Active Record in ILIAS
## Active Records
### General
The ActiveRecord-Implementation in ILIAS should help developers to get rid of multiple developments of CRUD functionality in their model classes. A lot of redundant code is to be found in ILIAS due to the implementations of read- and write processes to the persistent layer.
ILIAS-ActiveRecord provides a lot of useful helpers such as a QueryBuilder, dynamic CRUD, ObjectCaching und data source maintenance.

### Differences against other implementations
ActiveRecord are well known in other frameworks and languages such as Ruby, .NET, CakePHP …
Most ActiveRecords directly represent the persistent layer, mostly database-tables. Changes in the database are automatically represented by the model. Modifications on the class-members are only possible by modifying the database-field.
This is the the only big difference between ILIAS-ActiveRecord and other ActiveRecord-Implementations. ILIAS-ActiveRecord describes the whole class-member in PHP-Code with the information for the persistent layer (such as data-type, length, …).
Advantages of this implementation:

- The class-member is represented in your PHP-class and not just dynamically loaded, you ‘see’ your members and let IDEs like PHPStorm automatically implement your setters and getters.
- You ‘see’ directly the field-attributes of your member in the persistent layer.
Information about your members can be accessed by field-classes.

## Implement your ActiveRecord-Class
### Structure of your model
An ActiveRecord-Class normally extends from the abstract ActiveRecord. Let us use the following example:
“We need a Message-Model. A Message has a title, a body, a sender and a receiver. Additionally the Message can be of the priority ‘low’, ‘normal’ or ‘high’ and can have a status like ‘new’ and ‘read’.”
Our Model could look like this:

```php
<?php
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/class.ActiveRecord.php');
require_once(dirname(__FILE__) . '/../../Connector/class.arConnectorSession.php');
 
/**
 * Class arMessage
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class arMessage extends ActiveRecord {
 
        const TYPE_NEW = 1;
        const TYPE_READ = 2;
        const PRIO_LOW = 1;
        const PRIO_NORMAL = 5;
        const PRIO_HIGH = 9;
 
        const TABLE_NAME = 'ar_message';
 
        /**
         * @return string
         */
        static function returnDbTableName() {
                return self::TABLE_NAME;
        }
 
        /**
         * @var int
         *
         * @con_is_primary true
         * @con_sequence true
         * @con_has_field  true
         * @con_fieldtype  integer
         * @con_length     8
         */
        protected $id;
        /**
         * @var string
         *
         * @con_has_field true
         * @con_fieldtype text
         * @con_length    256
         */
        protected $title = '';
        /**
         * @var string
         *
         * @con_has_field true
         * @con_fieldtype clob
         * @con_length    4000
         */
        protected $body = '';
        /**
         * @var int
         *
         * @con_has_field  true
         * @con_fieldtype  integer
         * @con_length     1
         */
        protected $sender_id = 0;
        /**
         * @var int
         *
         * @con_has_field  true
         * @con_fieldtype  integer
         * @con_is_notnull true
         * @con_length     1
         */
        protected $receiver_id = 0;
        /**
         * @var int
         *
         * @con_has_field  true
         * @con_fieldtype  integer
         * @con_length     1
         * @con_is_notnull true
         */
        protected $priority = self::PRIO_NORMAL;
        /**
         * @var int
         *
         * @con_has_field  true
         * @con_fieldtype  integer
         * @con_length     1
         * @con_is_notnull true
         */
        protected $type = self::TYPE_NEW;
 
 
        /**
         * @param mixed $body
         */
        public function setBody($body) {
                $this->body = $body;
        }
 
 
        /**
         * @return mixed
         */
        public function getBody() {
                return $this->body;
        }
 
 
        /**
         * @param int $priority
         */
        public function setPriority($priority) {
                $this->priority = $priority;
        }
 
 
        /**
         * @return int
         */
        public function getPriority() {
                return $this->priority;
        }
 
 
        /**
         * @param int $receiver_id
         */
        public function setReceiverId($receiver_id) {
                $this->receiver_id = $receiver_id;
        }
 
 
        /**
         * @return int
         */
        public function getReceiverId() {
                return $this->receiver_id;
        }
 
 
        /**
         * @param int $sender_id
         */
        public function setSenderId($sender_id) {
                $this->sender_id = $sender_id;
        }
 
 
        /**
         * @return int
         */
        public function getSenderId() {
                return $this->sender_id;
        }
 
 
        /**
         * @param string $title
         */
        public function setTitle($title) {
                $this->title = $title;
        }
 
 
        /**
         * @return string
         */
        public function getTitle() {
                return $this->title;
        }
 
 
        /**
         * @param int $type
         */
        public function setType($type) {
                $this->type = $type;
        }
 
 
        /**
         * @return int
         */
        public function getType() {
                return $this->type;
        }
}
 
?>
```

The class implements the public static Method ‘returnDbTableName’, which returns the identifier of the container in the persistent layer. The rest of the Class are Members, Setters and Getters. This Class is fully functional, no other methods have to be implemented to have full CRUD, Caching, Collections, Factory, …
All Class-Members, which should be represented in the persistent layer, are additionally documented with PHPDoc.

### Using CRUD
After implementing your modelclass you can use the ActiveRecord CRUD commands to build and modify objects of the class:

```php
$arMessage = new arMessage();
$arMessage->setTitle('Hello World');
$arMessage->setBody('Development using ActiveRecord saves a lot of time');
$arMessage->create();
// OR
$arMessage = new arMessage(3);
echo $arMessage->getBody();
// OR
$arMessage = new arMessage(6);
$arMessage->setType(arMessage::TYPE_READ);
$arMessage->update();
// OR
$arMessage = arMessage::find(58); // find() Uses the ObjectCache
$arMessage->delete();
```

### Fields and FieldList
An ActiveRecord-Class-Member is described with the following attributes in PHPDoc. This information is used to provide a proper persistent layer access.

| Attribute-Name | Description                                                                                               | Possible Values                                                                                  |
|----------------|-----------------------------------------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------|
| con_hasfield   | Defines whether the field is represented in the persistent layer or not (false doesn’t has to be written) | true/false                                                                                       |
| con_is_primary | Member is primary key. Only one primary for one class possible.                                           | true/false                                                                                       |
| con_sequence   | The (primary-)field has an auto-increment. This is needed in most of the cases                            | true/false                                                                                       |
| con_is_notnull | Is member not_null (as in MySQL)                                                                          | true/false                                                                                       |
| con_fieldtype  | All ilDB-Field-Types are currently supported                                                              | text, integer, float, date, time, timestamp, clob                                                |
| con_length     | Length of the field in the persistent layer                                                               | determines from the fieldtype. See 'Databse Access and Database Schema' for further information. |

All this information is parsed from the PHPDoc once per ActiveRecord-Class and request and are cached for all other instances of this type. So there should not be a remarkable performance-drop.
This is an Example for a primary key $id:

```php
/**
 * @var int
 *
 * @con_is_primary true
 * @con_has_field  true
 * @con_sequence  true
 * @con_fieldtype  integer
 * @con_length     8
 */
protected $id;
```

All the meta information can be access in the ActiveRecord:

```php
public function dummy() {
        echo $this->arFieldList->getPrimaryField();
        echo $this->arFieldList->getFieldByName('title')->getFieldType();
        echo $this->getPrimaryFieldValue();
}
```

## ActiveRecordList
### Basics
The ActiveRecordList-Class represents the Collection, Repository, ... The List is accessible through the ActiveRecord or in an own instance:

```php
/**
 * @return arMessage[]
 * @description a way to get all objects is to call get() directly on your class
 */
public static function getAllObjects() {
        $array_of_arMessages = arMessage::get();
 
        // OR
 
        $arMessageList = new arMessageList();
        $array_of_arMessages = $arMessageList->get();
        return $array_of_arMessages;
}
```

Both examples return an Array of arMessage-Objects. But The List provide more functionality, such as a QueryBuilder:


```php
public function getSome() {
        $array_of_arMessages = arMessage::where(array('type' => arMessage::TYPE_READ))->orderBy('title')->get();
 
        // OR
 
        $arMessageList = new arMessageList();
        $arMessageList->where(array('type'=> arMessage::TYPE_READ));
        $arMessageList->orderBy('title');
        $array_of_arMessages = $arMessageList->get();
}
```

### Build a query
**Where**

| Method-Call                                                                                                                     | Query                                                                                       | 
|---------------------------------------------------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------| 
| ```arMessage::where(array('type' => arMessage::TYPE_READ));```                                                                  | ```SELECT * FROM ar_message WHERE ar_message.type = 1```                                    | 
| ```arMessage::where(array('type'=>arMessage::TYPE_NEW), '!=');```                                                               | ```SELECT * FROM ar_message WHERE ar_message.type != 1```                                   | 
| ```arMessage::where(array( 'type' => arMessage::TYPE_NEW, 'title' => '%test%' ), '=');```                                       | ```SELECT * FROM ar_message WHERE ar_message.type = 1 AND ar_message.title = '%test%'```    | 
| ```arMessage::where(array( 'type' => arMessage::TYPE_NEW, 'title' => '%test%' ), array( 'type' => '=', 'title' => 'LIKE' ));``` | ```SELECT * FROM ar_message WHERE ar_message.type = 1 AND ar_message.title LIKE '%test%'``` | 
| ```arMessage::where(array( 'type' => arMessage::TYPE_NEW ))->where(array( 'title' => '%test%' ), 'LIKE')```                     | ```SELECT * FROM ar_message WHERE ar_message.type = 1 AND ar_message.title LIKE '%test%'``` | 


**Oder By**

| Method-Call                                                 | Query                                                         | 
|-------------------------------------------------------------|---------------------------------------------------------------| 
| ```arMessage::orderBy('title');```                          | ```SELECT * FROM ar_message ORDER BY title ASC```             | 
| ```arMessage::orderBy('title', 'DESC');```                  | ```SELECT * FROM ar_message ORDER BY title DESC```            | 
| ```arMessage::orderBy('title', 'DESC')->orderBy('type');``` | ```SELECT * FROM ar_message ORDER BY title DESC, type ASC'``` | 


**Limit**

| Method-Call                     | Query                                       | 
|---------------------------------|---------------------------------------------| 
| ```arMessage::limit(0, 100);``` | ```SELECT * FROM ar_message LIMIT 0, 100``` | 


**Join**

| Method-Call                                                                     | Query                                                                                                                    | 
|---------------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------| 
| ```arMessage::innerjoin('usr_data', 'receiver_id', 'usr_id');```                | ```SELECT ar_message.*, usr_data.* FROM ar_message INNER JOIN usr_data ON ar_message.receiver_id = usr_data.usr_id```    | 
| ```arMessage::leftjoin('usr_data', 'receiver_id', 'usr_id', array('email'));``` | ```SELECT ar_message.*, usr_data.email FROM ar_message LEFT JOIN usr_data ON ar_message.receiver_id = usr_data.usr_id``` | 

**Combining statements**

| Method-Call                                                                     | Query                                                                                                                    | 
|---------------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------| 
| ```arMessage::innerjoin('usr_data', 'receiver_id', 'usr_id');```                | ```SELECT ar_message.*, usr_data.* FROM ar_message INNER JOIN usr_data ON ar_message.receiver_id = usr_data.usr_id```    | 
| ```arMessage::leftjoin('usr_data', 'receiver_id', 'usr_id', array('email'));``` | ```SELECT ar_message.*, usr_data.email FROM ar_message LEFT JOIN usr_data ON ar_message.receiver_id = usr_data.usr_id``` | 

### Get information
**get();**
arMessage::orderBy('title')->get(); will return an array of arMessage-Object.
 
**getArray();**
arMessage::orderBy('title')->getArray(); will return a 2D record-value array. getArray() can be filtered or the index oft he array can be set:
arMessage::getArray(NULL, array('title')); will return an array with only the titles of the records.
 
**getCollection();**
If you build a query using the statements explaines above, you can store this Collection for further use. arMessage::getCollection(); return the ActiveRecordList-Object with all statements saves.
 
**first();**
Returns the first object from your query.
 
**last();**
Returns the last object from your query.

### Use ActiveRecord for Sorting and Filters in Tables
When using an Activerecord for presentation in ilTableGUI, this is an Example to use external sorting and external segmentation, which will increase performance on larger tables:

```php
protected function parseData() {
        $this->determineOffsetAndOrder();
        $this->determineLimit();
        $arMessageList = arMessage::orderBy($this->getOrderField(), $this->getOrderDirection());
        foreach ($this->filter as $field => $value) {
                if ($value) {
                        $arMessageList->where(array( $field => $value ));
                }
        }
        $this->setMaxCount($arMessageList->count());
        $arMessageList->limit($this->getOffset(), $this->getLimit());
        $arMessageList->orderBy('title'); // Secord order field
        $arMessageList->dateFormat('d.m.Y - H:i:s'); // All date-fields come in three ways: formatted, unix, unformatted (as in db)
        $this->setData($arMessageList->getArray());
}
```

## Connector

ILIAS ActiveRecord uses the ilDB connection as default persistent layer. A connector is responsible for all connections to the persistent layer. It’s possible to write your own Connector, an example is delivered with the ActiveRecord. It uses the User-Session to store the objects. This connector is not fully functional (there is no Querybuilder). Use the abstract arConnector Class to implement your own connector.
 
## Maintenance of data source
ActiveRecord allows to maintain your persistent layer like the ILIAS Database for your class. There is no need to install the database on your own, ActiveRecord can install und update the table:
**Please do not use installDB; and updateDB; for core-development. Using them will be reported as a bug.**

### Generate DB-Update-Step to install your Class
Use the already known DB-Update-Steps to generate your AR-Databases. There is a Helper-Script to auto-generate a Installation-Updatestep. Implement these two lines with your ActiveRecord somewhere in ILIAS-Code and run the site. It generates and Downloads a tet-file with the installation-Step:

```php
$arBuilder = new arBuilder(new arMessage());
$arBuilder->generateDBUpdateForInstallation();
```

You can use these methods to delete or truncate your table even in dbupdate-Scripts:

```php
arMessage::resetDB(); // Truncates the Database
$ilDB->dropTable(arMessage::TABLE_NAME, false); // Deletes the Database
```

It's not yet possible to generate e database-modification step with this feature. Please write those as usual and don't forget to represent your changes in your AR-based Class.

### Generate Class-File from existing MySQL-Table (Beta)
It’s possible to generate a PHP-Classfile for an existing MySQL-Datatable, e.g. with the Table usr_data:

```php
$arConverter = new arConverter('usr_data', 'arUser');
$arConverter->downloadClassFile();
```
## Object-Cache
Every ActiveRecord is being cached, developers don’t have to mind this task. The cache is updated on every object modification and is deleted after deleting the object.
The object cache storage can be accessed if necessary:

```php
// e.g.
 
$arMessageFour = new arMessage(4);
arObjectCache::purge($arMessageFour);
if (! arObjectCache::isCached('arMessage', 4)) {
        arObjectCache::store(new arMessage(4));
 
        return arObjectCache::get('arMessage', 4);
}
```

