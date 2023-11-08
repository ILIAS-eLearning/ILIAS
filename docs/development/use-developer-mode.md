# Developer Mode

Sometimes features that are not implemented completely need to be committed to CVS because several programmers work on the same source code or new features or objects should be tested on dependencies to other parts of ILIAS. For these cases we have created the possibility to set parts of the system or whole object types into **developer mode**.

Object types or functions in developer mode are not shown in ILIAS by default. So users are not affected with features that are not working properly yet.

## Activating Objects

To display functions in developer mode please add the following line in your `client.ini` in the `[system]` section:

```php
DEVMODE = "1"
```

Now you will see everything as usual.

If you delete the line or set the value to 0 you will disable developer mode again.

## How to Use DEVMODE for Programming

To hide entire object types by setting them to DEVMODE just add a new attribute to the desired object in `objects.xml`, e.g.:

```xml
<object name="grp" class_name="Group" checkbox="1" inherit="1" translate="0" devmode="1">
```

In this case group objects will now not longer appear in the system.

To hide particular code parts or functions use the new constant DEVMODE:

```php
if (DEVMODE) 
    {
        // do something only if devmode is activated
    }
```