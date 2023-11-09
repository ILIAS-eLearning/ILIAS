## Permission Handling

### Reference IDs and Object IDs

All objects within the ILIAS repository are protected by the role-based access control system (RBAC). An object within the repository is identified by the so-called Reference-ID (ref_id). The Reference-ID determines the object and the position of the object within the repository tree. Objects are identified by their Object-ID. Every object has only one Object-ID but may be associated with multiple Reference-IDs if it is referenced in multiple locations within the repository tree.

#### Related Classes:
- `ilObject` (classes/class.ilObject.php): Handles objects and their IDs
- `ilTree` (classes/class.ilTree.php): Handles the repository tree (and other trees).

#### Related Tables:
- `object_data`: Stores basic object data
- `object_reference`: Stores Reference-IDs of objects
- `tree`: Stores the repository tree

### How to check the access permission of a user

The access checking is provided by the class `ilAccessHandler`. An instance of this class is globally available through the DI-Container. The most important method of this class is:

```php
checkAccess($a_permission, $a_cmd, $a_ref_id, $a_type = "", $a_obj_id = "")
```



```php
global $DIC;

$access = $DIC->access();
if ($access->checkAccess("write", "", $this->object->getRefId())
{
	...
}
```


This method checks whether the current user may perform the action `$a_cmd` associated with the permission `$a_permission` on the repository object identified by `$a_ref_id`. The method checks the following things:

1. **RBAC Check**: Check whether the current user has the permission `$a_permission` for the object identified by `$a_ref_id`. `$a_permission` may be, for example, "read" or "write".

2. Repository **Path Check**: Checks whether the current user has read access to all parent nodes of the object identified by `$a_ref_id`. For example, if a learning module is located within a course A in category B, ILIAS checks read access of course A and category B.

3. **Condition Check**: Checks whether the user fulfills all preconditions for the object. Preconditions could be defined by authors, administrators, or tutors of repository objects. They consist of a trigger, a target, and a condition expression. For example, Learning module A (target) can be accessed only if the user has passed (condition) Test B (trigger).

4. **Object Status Check**: Checks whether the status of the object allows a command to be performed. For example, if a learning module is set to "offline," no read access-related command may be performed, even if the read permission is granted by RBAC.

The check of step 4 makes use of type-specific access classes. Every object type (learning modules, glossaries, chats, etc.) must provide an access class derived from `ilObjectAccess`, named `ilObj_Type_Access`, e.g., `ilObjGlossaryAccess`, `ilObjLearningModuleAccess`, `ilObjChatAccess`, etc. Those classes must contain a static method:

```php
_checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
```

This method should check the object status-related conditions and return true if everything is OK or false if not.

#### Related classes:
- `ilAccessHandler` (Services/AccessControl/classes/class.ilAccessHandler.php)
- `ilObjectAccess` (classes/class.ilObjectAccess)

