# Listening To Events

> *This documentation is only relevant for ILIAS 4.3.x and above. This is a work in progress.*

Several modules and services in ILIAS raise events. This is mainly done to enable decoupling of components. As a component does not need to know about every dependent service or module, it just notifies the event handler about a new event and the handler then alerts the registered listeners.
 
**The typical scenarios of events are:**

- Decoupling of dependent components
- Propagation of events in hierarchical structures
- Peparation of generic and specific data types


To register a component as a listener for an event, add the following code to your `service.xml` or `module.xml`:

```php
<?php xml version = "1.0" encoding = "UTF-8"?>
<module ...>
   ...
   <events>     
      <event type="listen" id="Services/Tracking" />
   </events>
   ...
</module>
```

The example above will register a module for **all** events issued by `Services/Tracking`.
 
*There is currently no way to register for certain events of a component only, this has to be done in your `EventListener` class.*


If necessary, you can set the id of the component to register manually:

```php
<?php xml version = "1.0" encoding = "UTF-8"?>
<module ...>
   ...
   <events>     
      <event type="listen" id="Services/Tracking" component="Module/Course" />
   </events>
   ...
</module>
```


To process events add the following class to your module or service in a file called `class.il<Module>AppEventListener.php`:

```php
<?php
 
class il<Module>AppEventListener
{
    static function handleEvent($a_component, $a_event, $a_parameter)
    {
             ...
    }
}
 
?>
```

Implement the method `handleEvent` to your liking. All necessary information of the event should be available in `$a_parameter`. Please do not forget to check if `$a_component` and `$a_event` have the correct values for your purpose.

All supported events can be found in table `il_event_handling`.


> *As event handling is part of the application layer, please do not issue redirects or error messages. As return values are ignored, you should use log entries if needed.*



# Raising Events

To raise an event use the following code (in your application layer):
  
```php
global $ilAppEventHandler;
$ilAppEventHandler->raise("Services/Tracking", "updateStatus", array(...));
```

This way all registered listeners of the component `Services/Tracking` will be notified for the event `updateStatus`. You should add all relevant data for the event to the 3rd parameter.
 
*There is no information available to the calling component which or if any listeners are notified.*

Please add all events your component is raising to the respective `module.xml` or `service.xml`:

```php
<?php xml version = "1.0" encoding = "UTF-8"?>
<module ...>
   ...
   <events>    
      <event type="raise" id="updateStatus" />
   </events>
   ...
</module>
```

If necessary, you can set the id of the (raising) component manually:

```php
<?php xml version = "1.0" encoding = "UTF-8"?>
<module ...>
   ...
   <events>    
      <event type="raise" id="updateStatus" component="Services/Tracking" />
   </events>
   ...
</module>
```