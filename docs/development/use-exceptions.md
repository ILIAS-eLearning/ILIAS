# Using Exceptions

If code identifies any problems that prevent the normal processing from being executed, e.g. a directory is not writable even if it should be, exceptions should be thrown. This usually happens within the application layer. Upper contexts that have called the application classes (e.g. GUI or SOAP) can act appropriate. E.g. the user interface layour can present the error using `ilUtil::sendFailure`.

## Defining new Exceptions

- New exceptions should be implemented as classes derived from `ilException` (found in component Services/Exceptions).
- These class file should be put into a subdirectory `exceptions` within the component directory, e.g. `Services/AdvancedEditing/exceptions`.
- If a component uses exceptions a top exception named after the component should be used. Other exceptions classes should be derived from this class, e.g. `Services/AdvancedEditing/exceptions/class.ilAdvancedEditingException.php`.


```php
<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */
 
require_once 'Services/Exceptions/classes/class.ilException.php'; 
 
/** 
 * Class for advanced editing exception handling in ILIAS. 
 * 
 * @author Michael Jansen <mjansen@databay.de>
 * @version $Id$ 
 * 
 */
class ilAdvancedEditingException extends ilException
{
    /** 
     * Constructor
     * 
     * A message is not optional as in build in class Exception
     * 
     * @param   string $a_message message
     */
    public function __construct($a_message)
    {
        parent::__construct($a_message);
    }
}
?>
```


## Throwing and Catching Exceptions
Throwing an exception:

```php
function update()
{
    if (!$this->getId())
    {
        throw new ilObjectException('No id given');
    }
}
```

Catching an exception (in this case in the GUI layer):

```php
try
{
    $this->object->setTitle($title);
    $this->object->update();
    ilUtil::sendSuccess($this->lng->txt('saved_settings'));
    return true;
}
catch (ilObjectException $e)
{
    ilUtil::sendFailure($e->getMessage());
    return false;
}
```


## Exception Wrapping
Advantages of exception wrapping are:

- Exception wrapping avoids the breaking of layer abstractions
- Exception wrapping gives layers the possibility to add context informations to the exception

```php
Exception Wrapping I (application class):
...
    try 
    {
        $parser = new ilSaxParser($this->xml);
        $parser->parse();
        ...
    {
    catch (ilSaxParserException $e)
    {
        throw new ilObjectException(?Cannot parse object XML: ?.e->getMessage());
    }
...
 
Exception Wrapping II (here: GUI class):
 
...
    try 
    {
        $this->object->import();
        lUtil::sendSuccess($this->$lng('obj_created'));
    }
    catch(ilObjectException $e)
```